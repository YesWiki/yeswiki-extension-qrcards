<?php

use YesWiki\Core\YesWikiMigration;
use YesWiki\Bazar\Service\EntryManager;
use YesWiki\Bazar\Service\FormManager;
use YesWiki\Core\Service\TripleStore;
use YesWiki\Core\Service\AclService;
use YesWiki\Core\Service\DbService;
use YesWiki\Core\Service\PageManager;

class QrcardsInitialDatabaseAndPagesSetup extends YesWikiMigration
{
    public function run()
    {
        $pageManager = $this->getService(PageManager::class);
        $tripleStore = $this->getService(TripleStore::class);
        $dbService = $this->getService(DbService::class);
        $entryManager = $this->getService(EntryManager::class);
        $formManager = $this->getService(FormManager::class);

        $glob = glob('tools/qrcards/setup/lists/*.json');
        foreach ($glob as $filename) {
            $listname = str_replace(array('tools/qrcards/setup/lists/', '.json'), '', $filename);
            if (file_exists($filename) && !$pageManager->getOne($listname)) {
                // save the page with the list value
                $pageManager->save($listname, file_get_contents($filename));
                // in case, there is already some triples for the list, delete them
                $tripleStore->delete($listname, 'http://outils-reseaux.org/_vocabulary/type', null);
                // create the triple to specify this page is a list
                $tripleStore->create($listname, 'http://outils-reseaux.org/_vocabulary/type', 'liste', '', '');
            }
        }

        $glob = glob('tools/qrcards/setup/forms/*.json');
        foreach ($glob as $filename) {
            $formId = str_replace(array('tools/qrcards/setup/forms/', '.json'), '', $filename);

            // test if the form exists, if not, install it
            $form = $formManager->getOne($formId);
            if (empty($form)) {
                $form = json_decode(file_get_contents($filename), true);
                $formManager->create($form);
            }
        }

        $glob = glob('tools/qrcards/setup/entries/*.json');
        foreach ($glob as $filename) {
            $entryName = str_replace(array('tools/qrcards/setup/entries/', '.json'), '', $filename);
            if (file_exists($filename) && !$pageManager->getOne($entryName)) {
                // save the page with the list value
                $entry = json_decode(file_get_contents($filename), true);
                $entry['antispam'] = 1;
                $entryManager->create(1400, $entry);
            }
        }

        $glob = glob('tools/qrcards/setup/pages/*.txt');
        foreach ($glob as $filename) {
            $pageName = str_replace(array('tools/qrcards/setup/pages/', '.txt'), '', $filename);
            $this->updatePage($pageName, file_get_contents($filename), ['{QrcardFormId}' => 1400]);
        }

        $glob = glob('tools/qrcards/setup/appendtopages/*.json');
        foreach ($glob as $filename) {
            $pageName = str_replace(array('tools/qrcards/setup/appendtopages/', '.json'), '', $filename);
            $this->updatePage($pageName, file_get_contents($filename), ['{QrcardFormId}' => 1400], true);
        }
    }

    private function updatePage(string $pageName, string $content, array $replacements = [], $append = false): string
    {
        $aclService = $this->getService(AclService::class);
        $pageManager = $this->getService(PageManager::class);
        // if the page doesn't exist, create it with a default version
        if (!$page = $pageManager->getOne($pageName)) {
            if (!empty($replacements)) {
                $content = str_replace(array_keys($replacements), array_values($replacements), $content);
            }
            $aclService->delete($pageName); // to clear acl cache
            $aclService->save($pageName, 'read', '@admins');
            $aclService->save($pageName, 'write', '@admins');
            $pageManager->save($pageName, $content, "", true);
        } else {
            if ($append) {
                $content = json_decode($content, true);
                if (strpos($page['body'], $content['content']) === false) {
                    $newContent = str_replace($content['replace'], $content['content'], $page['body']);
                    $pageManager->save($pageName, $newContent, "", true);
                }
            }
        }
        return '';
    }
}
