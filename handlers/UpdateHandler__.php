<?php

/**
 * Handler called after the 'update' handler. to install the qrcards templates and create default pages
 * needed ones.
 *
 * @category YesWiki
 * @package  qrcards
 * @author   Adrien Cheype <adrien.cheype@gmail.com>
 * @author   Florian Schmitt <mrflos@lilo.org>
 * @author   Jérémy Dufraisse <jeremy.dufraisse-info@orange.fr>
 * @license  https://www.gnu.org/licenses/agpl-3.0.en.html AGPL 3.0
 * @link     https://yeswiki.net
 */

namespace YesWiki\Qrcards;

use YesWiki\Bazar\Service\EntryManager;
use YesWiki\Bazar\Service\FormManager;
use YesWiki\Core\Service\AclService;
use YesWiki\Core\Service\DbService;
use YesWiki\Core\Service\PageManager;
use YesWiki\Core\Service\TripleStore;
use YesWiki\Core\YesWikiHandler;

class UpdateHandler__ extends YesWikiHandler
{
    public function run()
    {
        $output = '';
        if ($this->wiki->UserIsAdmin()) {
            $pageManager = $this->getService(PageManager::class);
            $tripleStore = $this->getService(TripleStore::class);
            $dbService = $this->getService(DbService::class);
            $entryManager = $this->getService(EntryManager::class);
            $formManager = $this->getService(FormManager::class);

            $output .= '<strong>Extension Qrcards</strong><br/>';

            $glob = glob('tools/qrcards/setup/lists/*.json');
            foreach ($glob as $filename) {
                $listname = str_replace(array('tools/qrcards/setup/lists/', '.json'), '', $filename);
                if (file_exists($filename) && !$pageManager->getOne($listname)) {
                    $output .= 'ℹ️ Adding the <em>' . $listname . '</em> list<br />';
                    // save the page with the list value
                    $pageManager->save($listname, file_get_contents($filename));
                    // in case, there is already some triples for the list, delete them
                    $tripleStore->delete($listname, 'http://outils-reseaux.org/_vocabulary/type', null);
                    // create the triple to specify this page is a list
                    $tripleStore->create($listname, 'http://outils-reseaux.org/_vocabulary/type', 'liste', '', '');
                    $output .= '✅ Done !<br />';
                } else {
                    $output .= '✅ The <em>' . $listname . '</em> list already exists.<br />';
                }
            }

            $glob = glob('tools/qrcards/setup/forms/*.json');
            foreach ($glob as $filename) {
                $formId = str_replace(array('tools/qrcards/setup/forms/', '.json'), '', $filename);

                // test if the form exists, if not, install it
                $form = $formManager->getOne($formId);
                if (empty($form)) {
                    $form = json_decode(file_get_contents($filename), true);
                    $output .= "ℹ️ Adding <em>" . $form['bn_label_nature'] . "</em> form into <em>{$dbService->prefixTable('nature')}</em> table.<br />";

                    $formManager->create($form);

                    $output .= '✅ Done !<br />';
                } else {
                    $output .= "✅ The <em>" . $form['bn_label_nature'] . "</em> form already exists in the <em>{$dbService->prefixTable('nature')}</em> table.<br />";
                }
            }

            $glob = glob('tools/qrcards/setup/entries/*.json');
            foreach ($glob as $filename) {
                $entryName = str_replace(array('tools/qrcards/setup/entries/', '.json'), '', $filename);
                if (file_exists($filename) && !$pageManager->getOne($entryName)) {
                    $output .= 'ℹ️ Adding the <em>' . $entryName . '</em> entry<br />';
                    // save the page with the list value
                    $entry = json_decode(file_get_contents($filename), true);
                    $entry['antispam'] = 1;
                    $entryManager->create(1400, $entry);
                    $output .= '✅ Done !<br />';
                } else {
                    $output .= '✅ The <em>' . $entryName . '</em> entry already exists.<br />';
                }
            }

            $glob = glob('tools/qrcards/setup/pages/*.txt');
            foreach ($glob as $filename) {
                $pageName = str_replace(array('tools/qrcards/setup/pages/', '.txt'), '', $filename);
                $output .= $this->updatePage($pageName, file_get_contents($filename), ['{QrcardFormId}' => 1400]);
            }

            $glob = glob('tools/qrcards/setup/appendtopages/*.json');
            foreach ($glob as $filename) {
                $pageName = str_replace(array('tools/qrcards/setup/appendtopages/', '.json'), '', $filename);
                $output .= $this->updatePage($pageName, file_get_contents($filename), ['{QrcardFormId}' => 1400], true);
            }

            $output .= '<hr />';
        }

        // add the content before footer
        $this->output = str_replace(
            '<!-- end handler /update -->',
            $output . '<!-- end handler /update -->',
            $this->output
        );
    }

    private function updatePage(string $pageName, string $content, array $replacements = [], $append = false): string
    {
        $output = '';
        $aclService = $this->getService(AclService::class);
        $pageManager = $this->getService(PageManager::class);
        // if the page doesn't exist, create it with a default version
        if (!$page = $pageManager->getOne($pageName)) {
            $output .= "ℹ️ Adding the <em>$pageName</em> page<br />";
            if (!empty($replacements)) {
                $content = str_replace(array_keys($replacements), array_values($replacements), $content);
            }
            $aclService->delete($pageName); // to clear acl cache
            $aclService->save($pageName, 'read', '@admins');
            $aclService->save($pageName, 'write', '@admins');
            $pageManager->save($pageName, $content, "", true);
            $output .= '✅ Done !<br />';
        } else {
            if ($append) {
                $content = json_decode($content, true);
                if (strpos($page['body'], $content['content']) === false) {
                    $newContent = str_replace($content['replace'], $content['content'], $page['body']);
                    $pageManager->save($pageName, $newContent, "", true);
                    $output .= "✅ The <em>$pageName</em> page was extented.<br />";
                } else {
                    $output .= "✅ The <em>$pageName</em> page was already extented.<br />";
                }
            } else {
                $output .= "✅ The <em>$pageName</em> page already exists.<br />";
            }
        }
        return $output;
    }
}
