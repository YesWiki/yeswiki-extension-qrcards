<?php

namespace YesWiki\Qrcards\Service;

use YesWiki\Content\Entity\MenuNode;
use YesWiki\Content\Entity\PageBody;
use YesWiki\Content\Entity\PageType;
use YesWiki\Content\Service\EntryManager;
use YesWiki\Content\Service\FormManager;
use YesWiki\Content\Service\PageManager;
use YesWiki\Identity\Service\AclService;
use YesWiki\Kernel\Service\UrlFormatter;
use YesWiki\Render\Service\LayoutService;

/** What the qrcards migrations install: value lists, forms 1400 and 1401, sample cards, pages and menus, read from setup/. */
class QrcardsSetup
{
    public const CARD_FORM = '1400';
    public const CARD_SET_FORM = '1401';
    public const MENU = 'MenuQrCards';
    public const QUICK_MENU_NODE = 'qrcards';
    public const QUICK_MENU_LEGACY_LABEL = 'Qr Cartes';
    public const ICON_TEXT_FIELDS = ['bf_texte_boite1', 'bf_texte_boite2', 'bf_texte_boite3'];
    public const ICON_TEXT_LENGTH = '30';

    public function __construct(
        private readonly PageManager $pageManager,
        private readonly FormManager $formManager,
        private readonly EntryManager $entryManager,
        private readonly AclService $aclService,
        private readonly LayoutService $layout,
        private readonly UrlFormatter $urlFormatter,
    ) {
    }

    public static function setupDir(): string
    {
        return \dirname(__DIR__) . '/setup';
    }

    /** @return list<string> the tags of the lists created */
    public function installLists(): array
    {
        $created = [];
        foreach ($this->jsonFiles('lists') as $tag => $list) {
            if ($this->saveIfFree($tag, ['title' => (string)($list['title'] ?? $tag), 'nodes' => $list['nodes'] ?? []], PageType::LIST)) {
                $created[] = $tag;
            }
        }

        return $created;
    }

    /** The setup copy of a list replaces the stored one when their values differ, keeping its translations. */
    public function refreshList(string $tag): bool
    {
        $list = $this->jsonFiles('lists')[$tag] ?? null;
        if ($list === null) {
            return false;
        }
        if (!$this->pageManager->tagExists($tag)) {
            return $this->saveIfFree($tag, ['title' => (string)($list['title'] ?? $tag), 'nodes' => $list['nodes'] ?? []], PageType::LIST);
        }
        $body = $this->pageManager->getOne($tag, null, false, true)['body'] ?? [];
        if (($body['nodes'] ?? null) == $list['nodes'] && ($body['title'] ?? null) === $list['title']) {
            return false;
        }
        $body['title'] = $list['title'];
        $body['nodes'] = $list['nodes'];
        $this->pageManager->save($tag, $body, '', true, null, PageType::LIST);

        return true;
    }

    public function installForm(string $id): bool
    {
        if ($this->formManager->getOne($id) !== null) {
            return false;
        }
        $form = $this->readJson(self::setupDir() . "/forms/{$id}.json");
        if ($form === []) {
            return false;
        }

        return $this->formManager->create(['id' => $id] + $form) === 0;
    }

    /** @return list<string> the tags of the sample cards created */
    public function installEntries(): array
    {
        $created = [];
        if ($this->formManager->getOne(self::CARD_FORM) === null) {
            return $created;
        }
        foreach ($this->jsonFiles('entries') as $tag => $entry) {
            if ($this->pageManager->tagExists($tag)) {
                continue;
            }
            $this->entryManager->create(self::CARD_FORM, ['tag' => $tag, 'antispam' => 1] + $entry);
            $created[] = $tag;
        }

        return $created;
    }

    /** @return list<string> the tags of the pages created */
    public function installPages(): array
    {
        $created = [];
        foreach ($this->pageContents() as $tag => $content) {
            if ($this->installPage($tag, $content)) {
                $created[] = $tag;
            }
        }

        return $created;
    }

    /** The setup copy of a page replaces its content when they differ. */
    public function refreshPage(string $tag): bool
    {
        $content = $this->pageContents()[$tag] ?? null;
        if ($content === null) {
            return false;
        }
        $page = $this->pageManager->getOne($tag, null, false, true);
        if ($page === null) {
            return $this->installPage($tag, $content);
        }
        $body = $page['body'];
        if (($body[PageBody::CONTENT] ?? null) === $content) {
            return false;
        }
        $body[PageBody::CONTENT] = $content;
        $this->pageManager->save($tag, $body, '', true, null, PageType::PAGE);

        return true;
    }

    public function installMenu(): bool
    {
        $menu = $this->jsonFiles('menus')[self::MENU] ?? null;
        if ($menu === null) {
            return false;
        }

        return $this->saveIfFree(self::MENU, ['title' => (string)($menu['title'] ?? self::MENU), 'nodes' => self::nodes($menu['nodes'] ?? [])], PageType::MENU);
    }

    /** The qrcards dropdown in the menu the quick access bar draws, replacing an older one, after the search entry. */
    public function placeInQuickMenu(): bool
    {
        $tag = $this->layout->quickMenu();
        $page = $tag === '' ? null : $this->pageManager->getOne($tag, null, false, true);
        $wanted = MenuNode::fromArray($this->readJson(self::setupDir() . '/quick-menu.json'))?->toArray();
        if ($page === null || $wanted === null) {
            return false;
        }

        $body = $page['body'];
        $nodes = array_values(array_filter(is_array($body['nodes'] ?? null) ? $body['nodes'] : [], 'is_array'));
        $position = null;
        foreach ($nodes as $index => $node) {
            if (($node['id'] ?? null) === self::QUICK_MENU_NODE || ($node['label'] ?? null) === self::QUICK_MENU_LEGACY_LABEL) {
                if ($node == $wanted) {
                    return false;
                }
                $position = $index;
                break;
            }
        }

        if ($position !== null) {
            $nodes[$position] = $wanted;
        } else {
            $search = array_search('search', array_map(static fn (array $node) => $node['link'] ?? null, $nodes), true);
            array_splice($nodes, $search === false ? count($nodes) : (int)$search + 1, 0, [$wanted]);
        }
        $body['nodes'] = $nodes;
        $this->pageManager->save($tag, $body, '', true, null, PageType::MENU);

        return true;
    }

    /** Lets the card form's three icon captions hold 30 characters instead of 15. */
    public function widenIconTexts(): bool
    {
        $form = $this->formManager->getUntranslated(self::CARD_FORM);
        if ($form === null) {
            return false;
        }
        $changed = false;
        foreach ($form['template'] as $index => $field) {
            if (!is_array($field) || !in_array($field['name'] ?? null, self::ICON_TEXT_FIELDS, true)) {
                continue;
            }
            foreach (['size', 'max_chars'] as $attribute) {
                if (($field[$attribute] ?? '') !== self::ICON_TEXT_LENGTH && (int)($field[$attribute] ?? 0) < (int)self::ICON_TEXT_LENGTH) {
                    $form['template'][$index][$attribute] = self::ICON_TEXT_LENGTH;
                    $changed = true;
                }
            }
        }
        if ($changed) {
            $this->formManager->update($form);
        }

        return $changed;
    }

    /** Switches on the card form's bookmarklet, filling the sources with the page's address and the back of the card with the selection. */
    public function enableBookmarklet(): bool
    {
        $form = $this->formManager->getUntranslated(self::CARD_FORM);
        if ($form === null || !empty($form['entry_bookmarklet'])) {
            return false;
        }
        $form['entry_bookmarklet'] = $this->readJson(self::setupDir() . '/forms/' . self::CARD_FORM . '.json')['entry_bookmarklet'] ?? null;
        if (empty($form['entry_bookmarklet'])) {
            return false;
        }
        $this->formManager->update($form);

        return true;
    }

    /** @return array<string, string> tag => Markdown, placeholders filled in */
    public function pageContents(): array
    {
        $bookmarklet = str_replace('bf_titre=QRCARDS', 'bf_titre=', $this->urlFormatter->href('iframe', 'AjouterQrCards', ['bf_titre' => 'QRCARDS'], false));
        $pages = [];
        foreach (glob(self::setupDir() . '/pages/*.md') ?: [] as $file) {
            $pages[basename($file, '.md')] = str_replace('{bookmarkletUrl}', $bookmarklet, (string)file_get_contents($file));
        }
        ksort($pages);

        return $pages;
    }

    private function installPage(string $tag, string $content): bool
    {
        if (!$this->saveIfFree($tag, [PageBody::CONTENT => $content], PageType::PAGE)) {
            return false;
        }
        $this->aclService->save($tag, 'read', '*');
        $this->aclService->save($tag, 'write', '@admins');

        return true;
    }

    /** @param array<string, mixed> $body */
    private function saveIfFree(string $tag, array $body, string $type): bool
    {
        if ($this->pageManager->tagExists($tag)) {
            return false;
        }
        $this->pageManager->save($tag, $body, '', true, null, $type);
        $this->pageManager->cacheType($tag, $type);

        return true;
    }

    /**
     * @param array<mixed> $nodes
     *
     * @return list<array<string, mixed>>
     */
    private static function nodes(array $nodes): array
    {
        return array_values(array_filter(array_map(
            static fn ($node): ?array => is_array($node) ? MenuNode::fromArray($node)?->toArray() : null,
            $nodes
        )));
    }

    /** @return array<string, array<string, mixed>> file name => decoded JSON */
    private function jsonFiles(string $directory): array
    {
        $files = [];
        foreach (glob(self::setupDir() . "/{$directory}/*.json") ?: [] as $file) {
            $files[basename($file, '.json')] = $this->readJson($file);
        }
        ksort($files);

        return $files;
    }

    /** @return array<string, mixed> */
    private function readJson(string $file): array
    {
        $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : null;

        return is_array($decoded) ? $decoded : [];
    }
}
