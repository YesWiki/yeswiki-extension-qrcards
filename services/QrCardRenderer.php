<?php

namespace YesWiki\Qrcards\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use YesWiki\Content\Entity\PageBody;
use YesWiki\Content\Service\FavoritesManager;
use YesWiki\Content\Service\ListManager;
use YesWiki\Content\Service\PageManager;
use YesWiki\Content\Service\QrCodeService;
use YesWiki\Files\Service\AttachedFilePaths;
use YesWiki\Files\Service\ImageResizer;
use YesWiki\Files\Service\Storage;
use YesWiki\Identity\Service\AclService;
use YesWiki\Identity\Service\AuthenticationService;
use YesWiki\Kernel\Service\AssetRegistry;
use YesWiki\Kernel\Service\HibernationService;
use YesWiki\Kernel\Service\PageContext;
use YesWiki\Kernel\Service\UrlFormatter;
use YesWiki\Render\Service\MarkdownFormatterService;
use YesWiki\Render\Service\TemplateEngine;

/** One card of a card entry, front and back, in the card layout the wiki chose. */
class QrCardRenderer
{
    public const VIEW_WEB = 'web';
    public const VIEW_PRINT = 'print';
    public const FORMAT_STANDARD = 'standard';
    public const FORMAT_A5 = 'a5';
    public const FORMAT_A4 = 'a4';
    public const FORMATS = [self::FORMAT_STANDARD, self::FORMAT_A5, self::FORMAT_A4];
    public const CONFIG_PAGE = 'ConfigMetacarte';
    public const DEFAULT_LAYOUT = 'qrcard';
    public const ASSETS = 'extensions/qrcards';
    public const THUMBNAIL_SIZE = '300';
    public const LONG_TEXT_TAGS = '<p><span><b><strong><u><i><em><ul><ol><li><br>';
    public const DIFFICULTY = ['facile' => 1, 'moyen' => 2, 'complexe' => 3];

    public function __construct(
        private readonly TemplateEngine $templateEngine,
        private readonly PageManager $pageManager,
        private readonly ListManager $listManager,
        private readonly AclService $aclService,
        private readonly AuthenticationService $authenticationService,
        private readonly FavoritesManager $favoritesManager,
        private readonly HibernationService $hibernationService,
        private readonly UrlFormatter $urlFormatter,
        private readonly PageContext $pageContext,
        private readonly Storage $storage,
        private readonly AttachedFilePaths $paths,
        private readonly ImageResizer $imageResizer,
        private readonly QrCodeService $qrCodeService,
        private readonly MarkdownFormatterService $markdown,
        private readonly AssetRegistry $assets,
        private readonly ParameterBagInterface $params,
    ) {
    }

    /** @param array<string, mixed> $entry */
    public function render(array $entry, string $view = self::VIEW_WEB, string $format = self::FORMAT_STANDARD): string
    {
        $config = $this->config();

        return $this->templateEngine->render('@qrcards/card-layouts/' . $this->layout($config) . '.twig', $this->variables($entry, $view, $config, $format));
    }

    /** The print format a `print` value asks for: `a5`, `a4`, or `standard` for anything else, `1` included. */
    public static function printFormat(mixed $print): string
    {
        return is_string($print) && in_array($print, self::FORMATS, true) ? $print : self::FORMAT_STANDARD;
    }

    /**
     * @param array<string, mixed> $entry
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    public function variables(array $entry, string $view, array $config = [], string $format = self::FORMAT_STANDARD): array
    {
        $format = self::printFormat($format);
        $tag = (string)($entry['tag'] ?? '');
        $link = trim((string)($entry['bf_url'] ?? '')) ?: $this->urlFormatter->href('', $tag, null, false);
        $logo = $this->logo();
        $user = $this->authenticationService->getLoggedUser();
        $userName = is_array($user) ? (string)($user['name'] ?? '') : '';
        $writable = !$this->hibernationService->isWikiHibernated();

        return [
            'entry' => $entry,
            'view' => $view === self::VIEW_PRINT ? self::VIEW_PRINT : self::VIEW_WEB,
            'format' => $format,
            'currentPage' => $this->pageContext->getTag(),
            'title' => (string)($entry['bf_titre'] ?? $entry['title'] ?? $tag),
            'difficulty' => trim(str_repeat('⚫️ ', self::DIFFICULTY[$entry['listeListeComplexite'] ?? ''] ?? 0)),
            'type' => $this->typeLabel((string)($entry['listeListeTypeCarte'] ?? '')),
            'logo' => $logo,
            'setLogo' => is_string($config['card_set_image'] ?? null) && $config['card_set_image'] !== '' ? $config['card_set_image'] : $logo,
            'image' => $this->imageUrl($entry, 'imagebf_image', $format === self::FORMAT_STANDARD),
            'color' => $this->color($entry, $config),
            'pictos' => array_map(fn (int $i): array => [
                'image' => $this->imageUrl($entry, "imagebf_picto_boite{$i}"),
                'text' => (string)($entry["bf_texte_boite{$i}"] ?? ''),
            ], [1, 2, 3]),
            'longText' => $this->longText((string)($entry['bf_chapeau'] ?? $entry['bf_essentiel'] ?? '')),
            'qrcode' => $this->qrcode($link),
            'link' => $link,
            'shortLink' => str_replace(['https://', 'http://'], '', $link),
            'license' => $this->assets->urlFor(self::ASSETS . '/images/cc-by-sa.svg') ?? '',
            'viewUrl' => $this->urlFormatter->href('', $tag, null, false),
            'printUrl' => $this->urlFormatter->href('', $tag, ['print' => self::FORMAT_STANDARD], false),
            'duplicateUrl' => $this->urlFormatter->href('duplicate', $tag, null, false),
            'duplicateRemoteUrl' => $this->urlFormatter->href('duplicate', $tag, ['toUrl' => '1'], false),
            'editUrl' => $writable && $this->aclService->hasAccess('write', $tag)
                ? $this->urlFormatter->href('edit', $tag, ['incomingurl' => $this->urlFormatter->href('', '', null, false)], false)
                : null,
            'deleteUrl' => $writable && ($this->aclService->isOwner($tag) || $this->aclService->isAdmin())
                ? $this->urlFormatter->href('deletepage', $tag, ['incomingurl' => $this->urlFormatter->href('', '', null, false)], false)
                : null,
            'favoriteUser' => $userName !== '' && $this->favoritesManager->areFavoritesActivated() ? $userName : null,
            'isFavorite' => $userName !== '' && $this->favoritesManager->areFavoritesActivated() && $this->favoritesManager->isUserFavorite($userName, $tag),
        ];
    }

    /**
     * What the `ConfigMetacarte` page says in YAML: `template`, `card_set_image`, `card_colors`.
     *
     * @return array<string, mixed>
     */
    public function config(): array
    {
        $page = $this->pageManager->getOne(self::CONFIG_PAGE);
        $yaml = is_array($page['body'] ?? null) ? (string)($page['body'][PageBody::CONTENT] ?? '') : '';
        if (trim($yaml) === '') {
            return [];
        }
        try {
            $config = Yaml::parse($yaml);
        } catch (ParseException) {
            return [];
        }

        return is_array($config) ? $config : [];
    }

    /** @param array<string, mixed> $config */
    private function layout(array $config): string
    {
        $layout = is_string($config['template'] ?? null) ? basename($config['template']) : '';

        return $layout !== '' && $this->templateEngine->hasTemplate("@qrcards/card-layouts/{$layout}.twig") ? $layout : self::DEFAULT_LAYOUT;
    }

    private function logo(): string
    {
        $logo = $this->params->has('card_logo') ? $this->params->get('card_logo') : null;

        return is_string($logo) && $logo !== '' ? $logo : ($this->assets->urlFor(self::ASSETS . '/images/logo_qr_cartes.svg') ?? '');
    }

    private function typeLabel(string $id): string
    {
        if ($id === '') {
            return '';
        }
        $nodes = $this->listManager->getOne('ListeTypeCarte')['nodes'] ?? [];
        foreach ($nodes as $node) {
            foreach ([$node, ...($node['children'] ?? [])] as $candidate) {
                if (($candidate['id'] ?? null) === $id) {
                    return (string)($candidate['label'] ?? '');
                }
            }
        }

        return '';
    }

    /**
     * @param array<string, mixed> $entry
     * @param array<string, mixed> $config
     */
    private function color(array $entry, array $config): string
    {
        foreach (is_array($config['card_colors'] ?? null) ? $config['card_colors'] : [] as $rule) {
            if (is_array($rule) && isset($rule['field'], $rule['key'], $rule['color']) && ($entry[$rule['field']] ?? null) == $rule['key']) {
                return (string)$rule['color'];
            }
        }

        return (string)($entry['bf_card_color'] ?? '');
    }

    /** @param array<string, mixed> $entry */
    private function imageUrl(array $entry, string $key, bool $thumbnail = false): ?string
    {
        $value = $entry[$key] ?? null;
        if (!is_string($value) || $value === '') {
            return null;
        }
        if (preg_match('#^(https?:)?//#i', $value) === 1) {
            return $value;
        }
        $uploads = rtrim($this->paths->uploadPath(), '/');
        foreach (["{$uploads}/{$value}", "{$uploads}/" . ($entry['tag'] ?? '') . "/{$value}"] as $path) {
            if (!$this->storage->exists($path)) {
                continue;
            }
            if ($thumbnail) {
                $cached = $this->imageResizer->resizedFilename($path, self::THUMBNAIL_SIZE, self::THUMBNAIL_SIZE);
                if ($this->storage->exists($cached)) {
                    return $this->storage->url($cached);
                }
            }

            return $this->storage->url($path);
        }

        return null;
    }

    private function longText(string $text): string
    {
        return $text === '' ? '' : strip_tags($this->markdown->format($text), self::LONG_TEXT_TAGS);
    }

    private function qrcode(string $link): string
    {
        $path = 'cache/qrcards-' . md5($link) . '.svg';
        if (!$this->storage->exists($path)) {
            $this->qrCodeService->generateToFile($link, $path);
        }

        return $this->storage->url($path);
    }
}
