<?php

namespace YesWiki\Test\Qrcards\Migrations;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use YesWiki\Content\Entity\PageBody;
use YesWiki\Content\Entity\PageType;
use YesWiki\Content\Service\EntryManager;
use YesWiki\Content\Service\FormManager;
use YesWiki\Content\Service\MenuManager;
use YesWiki\Content\Service\PageManager;
use YesWiki\Core\YesWikiMigration;
use YesWiki\Files\Service\Storage;
use YesWiki\Identity\Service\AclService;
use YesWiki\Kernel\Service\ConfigurationService;
use YesWiki\Kernel\Service\DbService;
use YesWiki\Kernel\Service\ExtensionRegistry;
use YesWiki\Kernel\Service\PageContext;
use YesWiki\Kernel\Service\UrlFormatter;
use YesWiki\Qrcards\Service\QrcardsSetup;
use YesWiki\Render\Service\LayoutService;
use YesWiki\Test\Core\ForcedParameterBag;
use YesWiki\Test\Core\YesWikiTestCase;

require_once 'tests/YesWikiTestCase.php';
require_once 'tests/ForcedParameterBag.php';

/** The four qrcards migrations, run the way `yeswicli migrate` runs them, on a quick menu of the test's own. */
class QrcardsMigrationsTest extends YesWikiTestCase
{
    private const QUICK_MENU = 'QrcardsTestQuickMenu';
    private const MIGRATIONS = [
        '20240527103813_QrcardsInitialDatabaseAndPagesSetup',
        '20241101074820_QrcardsAddCardSetForm',
        '20250430153031_QrcardsAddLinkToHomePageAndBiggerIconText',
        '20250603105836_QrcardsAddBookmarkletAndUpdateMenus',
    ];
    private const LISTS = ['ListeComplexite', 'ListeDegreDeMaturite', 'ListeTypeCarte'];
    private const PAGES = ['AjouterQrCards', 'DocumentationQrCartes', 'FavorisQrCards', 'VoirQrCards'];
    private const ENTRIES = ['bienvenue', 'demarrer'];

    private static bool $replaced = false;

    /** @var list<string> */
    private array $existedBefore = [];

    /** @var list<string> */
    private array $formsBefore = [];

    protected function setUp(): void
    {
        parent::setUp();
        $services = $this->getWiki()->services;
        if (!isset($services->get(ExtensionRegistry::class)->all()['qrcards'])) {
            $this->markTestSkipped('qrcards is not switched on in this wiki');
        }
        $pages = $services->get(PageManager::class);
        foreach ([...self::LISTS, ...self::PAGES, ...self::ENTRIES, QrcardsSetup::MENU] as $tag) {
            if ($pages->tagExists($tag)) {
                $this->existedBefore[] = $tag;
            }
        }
        foreach ([QrcardsSetup::CARD_FORM, QrcardsSetup::CARD_SET_FORM] as $id) {
            if ($services->get(FormManager::class)->getOne($id) !== null) {
                $this->formsBefore[] = $id;
            }
        }
        $pages->save(self::QUICK_MENU, ['title' => 'Test', 'nodes' => [
            ['id' => 't-search', 'label' => 'Rechercher', 'link' => 'search', 'icon' => ['source' => 'sprite', 'value' => 'search']],
            ['id' => 't-dashboard', 'label' => 'Tableau de bord', 'link' => 'dashboard'],
        ]], '', true, null, PageType::MENU);
        $pages->cacheType(self::QUICK_MENU, PageType::MENU);
        $this->useTheTestQuickMenu();
    }

    protected function tearDown(): void
    {
        $services = $this->getWiki()->services;
        $pages = $services->get(PageManager::class);
        $forms = $services->get(FormManager::class);
        foreach ([QrcardsSetup::CARD_SET_FORM, QrcardsSetup::CARD_FORM] as $id) {
            if (!in_array($id, $this->formsBefore, true) && $forms->getOne($id) !== null) {
                $forms->delete($id);
            }
        }
        foreach ([...self::ENTRIES, ...self::PAGES, ...self::LISTS, QrcardsSetup::MENU, self::QUICK_MENU] as $tag) {
            if (!in_array($tag, $this->existedBefore, true) && $pages->tagExists($tag)) {
                $pages->deleteOrphaned($tag);
            }
        }
        parent::tearDown();
    }

    public function testAWikiWithoutQrcardsGetsTheCardsTheirFormsAndTheirPages(): void
    {
        if ($this->existedBefore !== [] || $this->formsBefore !== []) {
            $this->markTestSkipped('qrcards is already installed on this wiki: ' . implode(', ', [...$this->existedBefore, ...$this->formsBefore]));
        }
        $services = $this->getWiki()->services;
        $this->runMigrations();

        $pages = $services->get(PageManager::class);
        foreach (self::LISTS as $list) {
            $this->assertTrue($pages->isType($list, PageType::LIST), "{$list} is a value list");
        }
        $this->assertCount(10, $this->body('ListeTypeCarte')['nodes'] ?? []);

        $cards = $services->get(FormManager::class)->getOne(QrcardsSetup::CARD_FORM);
        $this->assertNotNull($cards);
        $this->assertSame('{{bf_titre}}', $cards['entry_title_template']);
        $this->assertSame('bf_ressources', $cards['entry_bookmarklet']['url_field'] ?? null);
        $names = array_column($cards['template'], 'name');
        foreach (['bf_titre', 'listeListeComplexite', 'listeListeTypeCarte', 'bf_picto_boite1', 'bf_chapeau', 'bf_geolocation'] as $name) {
            $this->assertContains($name, $names);
        }
        foreach ($cards['template'] as $field) {
            if (in_array($field['name'] ?? '', QrcardsSetup::ICON_TEXT_FIELDS, true)) {
                $this->assertSame('30', $field['max_chars']);
            }
        }
        $this->assertNotNull($services->get(FormManager::class)->getOne(QrcardsSetup::CARD_SET_FORM));

        $welcome = $services->get(EntryManager::class)->getOne('bienvenue') ?? [];
        $this->assertSame(QrcardsSetup::CARD_FORM, (string)($welcome['form_id'] ?? ''));
        $this->assertSame('Bienvenue !', $welcome['title'] ?? null);
        $this->assertArrayNotHasKey('id_fiche', $welcome);
        $this->assertTrue($services->get(EntryManager::class)->isEntry('demarrer'));

        foreach (self::PAGES as $tag) {
            $content = $this->content($tag);
            $this->assertStringContainsString('{{nav menu="MenuQrCards"', $content);
            $this->assertStringNotContainsString('tools/qrcards', $content);
            $this->assertTrue($services->get(AclService::class)->hasAccess('read', $tag, '*'), "{$tag} is readable by anyone");
        }
        $this->assertStringContainsString('{{entrylist id="1400" template="qrcards"', $this->content('VoirQrCards'));
        $this->assertStringContainsString('AjouterQrCards/iframe', $this->content('DocumentationQrCartes'));
        $this->assertStringNotContainsString('{bookmarkletUrl}', $this->content('DocumentationQrCartes'));

        $this->assertCount(4, $services->get(MenuManager::class)->getOne(QrcardsSetup::MENU)['nodes'] ?? []);
        $quick = $services->get(MenuManager::class)->getUntranslated(self::QUICK_MENU)['nodes'] ?? [];
        $this->assertSame(['t-search', QrcardsSetup::QUICK_MENU_NODE, 't-dashboard'], array_column($quick, 'id'), 'the dropdown sits after the search entry');
        $this->assertCount(5, $quick[1]['children']);
    }

    public function testASecondRunChangesNothing(): void
    {
        $services = $this->getWiki()->services;
        $this->runMigrations();
        $pages = $services->get(PageManager::class);
        $tags = [...self::LISTS, ...self::PAGES, ...self::ENTRIES, QrcardsSetup::MENU, self::QUICK_MENU, 'qrcards', 'sets-de-cartes'];
        $revisions = array_map(fn (string $tag): int => count($pages->getRevisions($tag)), $tags);

        $this->runMigrations();

        $this->assertSame($revisions, array_map(fn (string $tag): int => count($pages->getRevisions($tag)), $tags));
        $this->assertSame(1, substr_count(json_encode($services->get(MenuManager::class)->getUntranslated(self::QUICK_MENU)) ?: '', '"id":"qrcards"'));
    }

    /** A wiki whose quick menu still holds Doryphore's dropdown gets it replaced rather than a second one. */
    public function testAnOlderDropdownIsReplaced(): void
    {
        $pages = $this->getWiki()->services->get(PageManager::class);
        $body = $this->body(self::QUICK_MENU);
        $body['nodes'][] = ['id' => 'old', 'label' => QrcardsSetup::QUICK_MENU_LEGACY_LABEL, 'link' => '', 'children' => [['id' => 'old-1', 'label' => 'Voir', 'link' => 'VoirQrCards']]];
        $pages->save(self::QUICK_MENU, $body, '', true, null, PageType::MENU);

        $this->assertTrue($this->qrcardsSetup()->placeInQuickMenu());

        $nodes = $this->body(self::QUICK_MENU)['nodes'] ?? [];
        $this->assertSame(['t-search', 't-dashboard', QrcardsSetup::QUICK_MENU_NODE], array_column($nodes, 'id'));
    }

    /** @return array<string, mixed> */
    private function body(string $tag): array
    {
        $page = $this->getWiki()->services->get(PageManager::class)->getOne($tag, null, false, true);

        return is_array($page['body'] ?? null) ? $page['body'] : [];
    }

    private function content(string $tag): string
    {
        $content = $this->body($tag)[PageBody::CONTENT] ?? null;
        $this->assertIsString($content, "{$tag} holds Markdown");

        return $content;
    }

    private function runMigrations(): void
    {
        $services = $this->getWiki()->services;
        foreach (self::MIGRATIONS as $file) {
            require_once dirname(__DIR__, 2) . "/migrations/{$file}.php";
            $class = (string)preg_replace('/^[\d_]*/', '', $file);
            /** @var YesWikiMigration $migration */
            $migration = new $class();
            $migration->setServices($services);
            $migration->setParams($services->get(ParameterBagInterface::class));
            $migration->setDbService($services->get(DbService::class));
            $migration->run();
        }
    }

    private function qrcardsSetup(): QrcardsSetup
    {
        return $this->getWiki()->services->get(QrcardsSetup::class);
    }

    /** The container hands the migrations a QrcardsSetup whose quick menu is this test's. */
    private function useTheTestQuickMenu(): void
    {
        $services = $this->getWiki()->services;
        if (self::$replaced) {
            return;
        }
        self::$replaced = true;
        $layout = new LayoutService(
            new ForcedParameterBag($services->get(ParameterBagInterface::class), [LayoutService::QUICK_MENU => self::QUICK_MENU]),
            $services->get(ConfigurationService::class),
            $services->get(PageManager::class),
            $services->get(MenuManager::class),
            $services->get(PageContext::class),
            $services->get(Storage::class),
        );
        $services->set(QrcardsSetup::class, new QrcardsSetup(
            $services->get(PageManager::class),
            $services->get(FormManager::class),
            $services->get(EntryManager::class),
            $services->get(AclService::class),
            $layout,
            $services->get(UrlFormatter::class),
        ));
    }
}
