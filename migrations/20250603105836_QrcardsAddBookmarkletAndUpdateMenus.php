<?php

use YesWiki\Bazar\Service\EntryManager;
use YesWiki\Bazar\Service\FormManager;
use YesWiki\Core\Service\DbService;
use YesWiki\Core\Service\PageManager;
use YesWiki\Core\Service\TripleStore;
use YesWiki\Core\YesWikiMigration;

class QrcardsAddBookmarkletAndUpdateMenus extends YesWikiMigration
{
    public function run()
    {
        $pageManager = $this->getService(PageManager::class);
        $tripleStore = $this->getService(TripleStore::class);
        $dbService = $this->getService(DbService::class);
        $entryManager = $this->getService(EntryManager::class);
        $formManager = $this->getService(FormManager::class);

        $this->updatePageContent(
            'DocumentationQrCartes',
            file_get_contents('tools/qrcards/setup/pages/DocumentationQrCartes.txt'),
            ['{baseUrl}' => $this->wiki->config['base_url']]
        );

        $newMenuContent = "\n" . '{{buttondropdown class="btn-qrcards" icon="far fa-clone" caret="0" title="Qr Cartes" hideifnoaccess="true"}}
 - {{button nobtn="1" icon="fas fa-eye" text="Voir les cartes" link="VoirQrCards"}}
 - {{button nobtn="1" icon="fa fa-plus-circle" text="Ajouter une carte" link="AjouterQrCards"}}
 - {{button nobtn="1" icon="fas fa-star" text="Cartes favorites" link="FavorisQrCards"}}
 - {{button nobtn="1" icon="fas fa-book-reader" text="Documentation" link="DocumentationQrCartes"}}
 - {{button nobtn="1" icon="fas fa-code-branch" text="WikiCartothèque" link="https://metacartes.net/wikicartes/"}}
{{end elem="buttondropdown"}}' . "\n";
        $re = '/({{buttondropdown.*title="Qr Cartes".*}})[\s\S]*({{end elem="buttondropdown"}})/miU';
        $pageRapideHaut = $pageManager->getOne('PageRapideHaut');
        $pageRapideHaut = preg_replace($re, $newMenuContent, $pageRapideHaut['body']);
        $pageManager = $this->getService(PageManager::class);
        $pageManager->save('PageRapideHaut', $pageRapideHaut, '', true);
    }

    private function updatePageContent(string $pageName, string $content, array $replacements = []): void
    {
        $pageManager = $this->getService(PageManager::class);
        if (!empty($replacements)) {
            $content = str_replace(array_keys($replacements), array_values($replacements), $content);
        }
        $pageManager->save($pageName, $content, '', true);
    }
}
