<?php

use YesWiki\Core\Service\DbService;
use YesWiki\Core\Service\PageManager;
use YesWiki\Core\YesWikiMigration;

class QrcardsAddLinkToHomePageAndBiggerIconText extends YesWikiMigration
{
    public function run(): void
    {
        $dbService = $this->getService(DbService::class);
        $pageManager = $this->getService(PageManager::class);

        // update to new ListeTypeCarte
        $pageManager->save('ListeTypeCarte', file_get_contents('tools/qrcards/setup/lists/ListeTypeCarte.json'));

        // add link to wikicarte to existing wikis
        $dbService->query("UPDATE {$dbService->prefixTable('pages')} SET `body` = REPLACE( `body` , '{{button icon=\"fas fa-book-reader\" link=\"DocumentationQrCartes\" nobtn=\"1\" text=\"Documentation\" }}\r\n', '{{button icon=\"fas fa-book-reader\" link=\"DocumentationQrCartes\" nobtn=\"1\" text=\"Documentation\" }}\r\n - {{button icon=\"fas fa-code-branch\" link=\"https://metacartes.net/wikicartes/\" nobtn=\"1\" text=\"Portail wikicartes\" }}\r\n' ) WHERE tag='PageRapideHaut' AND latest='Y'");

        // change form for longer inputs
        $dbService->query("UPDATE {$dbService->prefixTable('nature')} SET `bn_template` = REPLACE( `bn_template` , '***15***15', '***30***30' ) WHERE bn_id_nature=1400");
    }
}
