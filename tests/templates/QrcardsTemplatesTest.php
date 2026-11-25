<?php

namespace YesWiki\Test\Qrcards\Templates;

use YesWiki\Content\Service\EntryDisplay;
use YesWiki\Content\Service\EntryManager;
use YesWiki\Content\Service\FormBuilderExtensions;
use YesWiki\Content\Service\FormManager;
use YesWiki\Content\Service\PageManager;
use YesWiki\Files\Service\Storage;
use YesWiki\Kernel\Service\CurrentRequest;
use YesWiki\Kernel\Service\ExtensionRegistry;
use YesWiki\Kernel\Service\UrlFormatter;
use YesWiki\Qrcards\Service\QrCardRenderer;
use YesWiki\Qrcards\Service\QrcardsSetup;
use YesWiki\Render\Service\ActionRunner;
use YesWiki\Render\Service\TemplateEngine;
use YesWiki\Test\Core\YesWikiTestCase;

require_once 'tests/YesWikiTestCase.php';

/** The card list template, the card and card set entry templates, drawn from entries with Ectoplasme's keys. */
class QrcardsTemplatesTest extends YesWikiTestCase
{
    private const CARD = 'qrcards-test-card';
    private const SET = 'qrcards-test-set';
    private const OTHER_FORM = '1499';
    private const OTHER = 'qrcards-test-other';

    /** @var list<string> */
    private array $created = [];

    /** @var list<string> */
    private array $formsCreated = [];

    protected function setUp(): void
    {
        parent::setUp();
        $services = $this->getWiki()->services;
        if (!isset($services->get(ExtensionRegistry::class)->all()['qrcards'])) {
            $this->markTestSkipped('qrcards is not switched on in this wiki');
        }
        $setup = $services->get(QrcardsSetup::class);
        $this->created = $setup->installLists();
        foreach ([QrcardsSetup::CARD_FORM, QrcardsSetup::CARD_SET_FORM] as $id) {
            if ($setup->installForm($id)) {
                $this->formsCreated[] = $id;
            }
        }
        $entries = $services->get(EntryManager::class);
        $entries->create(QrcardsSetup::CARD_FORM, [
            'tag' => self::CARD,
            'antispam' => 1,
            'bf_titre' => 'Carte de test qrcards',
            'bf_accroche' => 'Une accroche de test',
            'bf_chapeau' => "Le **verso** de la carte\n\n- un\n- deux",
            'bf_description' => 'Contenu de la page associée',
            'bf_card_color' => '#ffcc00',
            'listeListeComplexite' => 'moyen',
            'listeListeTypeCarte' => '①',
            'bf_texte_boite1' => 'PICTO UN',
            'bf_licence' => 'CC-BY-SA',
        ]);
        $this->created[] = self::CARD;
        $entries->create(QrcardsSetup::CARD_SET_FORM, [
            'tag' => self::SET,
            'antispam' => 1,
            'bf_titre' => 'Set de test qrcards',
            'bf_description' => 'Un set pour le test',
            'checkboxfiche1400bf_cardlist' => self::CARD,
        ]);
        $this->created[] = self::SET;
    }

    protected function tearDown(): void
    {
        $services = $this->getWiki()->services;
        $pages = $services->get(PageManager::class);
        $storage = $services->get(Storage::class);
        foreach (array_reverse($this->created) as $tag) {
            if ($pages->tagExists($tag)) {
                $pages->deleteOrphaned($tag);
            }
            $qrcode = 'cache/qrcards-' . md5($services->get(UrlFormatter::class)->href('', $tag, null, false)) . '.svg';
            if ($storage->exists($qrcode)) {
                $storage->delete($qrcode);
            }
        }
        foreach (array_reverse($this->formsCreated) as $id) {
            $services->get(FormManager::class)->delete($id);
        }
        unset($_GET['print']);
        $services->get(CurrentRequest::class)->get()->query->remove('print');
        parent::tearDown();
    }

    public function testTheListDrawsEachEntryAsAFlippableCard(): void
    {
        $html = $this->getWiki()->services->get(ActionRunner::class)->action('entrylist', [
            'id' => QrcardsSetup::CARD_FORM,
            'template' => 'qrcards',
            'dynamic' => 'false',
            'query' => 'bf_titre=Carte de test qrcards',
        ]);

        $this->assertStringContainsString('page-qrcard web', $html);
        $this->assertStringContainsString('Carte de test qrcards', $html);
        $this->assertStringContainsString('Une accroche de test', $html);
        $this->assertStringContainsString('① Personne / sujet', $html, 'the type shows its label, not its id');
        $this->assertStringContainsString('⚫️ ⚫️', $html);
        $this->assertStringContainsString('background-color: #ffcc00', $html);
        $this->assertStringContainsString('<strong>verso</strong>', $html);
        $this->assertStringContainsString('PICTO UN', $html);
        $this->assertMatchesRegularExpression('#cache/qrcards-[0-9a-f]{32}\.svg#', $html);
        $this->assertStringContainsString('extensions/qrcards/images/logo_qr_cartes.svg', $html);
        $this->assertStringContainsString('flip-container', $html);
        $this->assertStringNotContainsString('tools/qrcards', $html);
        $this->assertStringNotContainsString('fa-', $html);
        $this->assertStringNotContainsString('&amp;amp;', $html);
    }

    public function testThePrintViewDrawsBothSidesSideBySide(): void
    {
        $html = $this->getWiki()->services->get(ActionRunner::class)->action('qrcard', ['tag' => self::CARD, 'view' => 'print']);

        $this->assertStringContainsString('qrcard-to-print', $html);
        $this->assertStringContainsString('qrcard-remove-from-print', $html);
        $this->assertStringNotContainsString('flip-container', $html);
    }

    public function testThePrintFormatComesFromTheArgumentOrTheAddress(): void
    {
        $actions = $this->getWiki()->services->get(ActionRunner::class);
        $query = $this->getWiki()->services->get(CurrentRequest::class)->get()->query;

        $this->assertStringContainsString('qrcard-format-a4', $actions->action('qrcard', ['tag' => self::CARD, 'view' => 'print', 'format' => 'a4']));
        $query->set('print', 'a5');
        $this->assertStringContainsString('qrcard-format-a5', $actions->action('qrcard', ['tag' => self::CARD]));
        $query->set('print', '1');
        $this->assertStringContainsString('qrcard-format-standard', $actions->action('qrcard', ['tag' => self::CARD]));
    }

    public function testUnknownPrintValuesFallBackToStandard(): void
    {
        $this->assertSame(QrCardRenderer::FORMAT_A5, QrCardRenderer::printFormat('a5'));
        $this->assertSame(QrCardRenderer::FORMAT_STANDARD, QrCardRenderer::printFormat('1'));
        $this->assertSame(QrCardRenderer::FORMAT_STANDARD, QrCardRenderer::printFormat('A3'));
        $this->assertSame(QrCardRenderer::FORMAT_STANDARD, QrCardRenderer::printFormat(null));
    }

    public function testACardEntryShowsItsCardBesideTheFieldsTheCardDoesNotHold(): void
    {
        $html = $this->getWiki()->services->get(EntryDisplay::class)->renderEntry(false, self::CARD);

        $this->assertStringContainsString('qrcard-entry-card', $html);
        $this->assertStringContainsString('Contenu de la page associée', $html);
        $this->assertStringContainsString('Carte de test qrcards', $html);
    }

    public function testTheQrcardFieldShowsTheCardInAnotherForm(): void
    {
        $services = $this->getWiki()->services;
        $forms = $services->get(FormManager::class);
        $this->assertNull($forms->getOne(self::OTHER_FORM), 'form ' . self::OTHER_FORM . ' must be free for this test');
        $forms->create([
            'id' => self::OTHER_FORM,
            'label' => 'Qrcards test other form',
            'description' => '',
            'template' => json_encode([
                ['type' => 'texte', 'name' => 'bf_titre', 'label' => 'Titre', 'required' => '1'],
                ['type' => 'qrcard'],
                ['type' => 'textelong', 'name' => 'bf_description', 'label' => 'Description'],
            ]),
        ]);
        $this->formsCreated[] = self::OTHER_FORM;
        $services->get(EntryManager::class)->create(self::OTHER_FORM, [
            'tag' => self::OTHER,
            'antispam' => 1,
            'bf_titre' => 'Fiche ordinaire avec carte',
            'bf_description' => 'Texte de la fiche ordinaire',
        ]);
        $this->created[] = self::OTHER;

        $html = $services->get(EntryDisplay::class)->renderEntry(false, self::OTHER);

        $this->assertStringContainsString('qrcard-entry-card', $html);
        $this->assertStringContainsString('flip-container', $html);
        $this->assertStringContainsString('Fiche ordinaire avec carte', $html);
        $this->assertStringContainsString('Texte de la fiche ordinaire', $html);
        $this->assertArrayNotHasKey('qrcard', $services->get(EntryManager::class)->getOne(self::OTHER) ?? []);
    }

    public function testTheFormBuilderOffersTheQrcardField(): void
    {
        $this->assertContains(
            'extensions/qrcards/javascripts/form-builder-qrcard.js',
            $this->getWiki()->services->get(FormBuilderExtensions::class)->forDesigner()['scripts']
        );
    }

    public function testACardSetListsItsCards(): void
    {
        $html = $this->getWiki()->services->get(EntryDisplay::class)->renderEntry(false, self::SET);

        $this->assertStringContainsString('Set de test qrcards', $html);
        $this->assertStringContainsString('Un set pour le test', $html);
        $this->assertStringContainsString('page-qrcard web', $html);
        $this->assertStringContainsString('Carte de test qrcards', $html);
    }

    public function testTheHexagonLayoutDrawsTheSameCard(): void
    {
        $services = $this->getWiki()->services;
        $entry = $services->get(EntryManager::class)->getOne(self::CARD) ?? [];
        $html = $services->get(TemplateEngine::class)->render(
            '@qrcards/card-layouts/hexa.twig',
            $services->get(QrCardRenderer::class)->variables($entry, QrCardRenderer::VIEW_WEB)
        );

        $this->assertStringContainsString('hexa-card', $html);
        $this->assertStringContainsString('Carte de test qrcards', $html);
        $this->assertStringContainsString('<strong>verso</strong>', $html);
    }

    public function testAMissingTagSaysSo(): void
    {
        $this->assertStringContainsString('yw-alert--danger', $this->getWiki()->services->get(ActionRunner::class)->action('qrcard', []));
    }
}
