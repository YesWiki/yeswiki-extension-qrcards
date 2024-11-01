<?php

use YesWiki\Bazar\Service\FormManager;
use YesWiki\Core\YesWikiMigration;

class QrcardsAddCardSetForm extends YesWikiMigration
{
    public function run(): void
    {
        $formManager = $this->getService(FormManager::class);
        // test if the form exists, if not, install it
        $form = $formManager->getOne(1401);
        if (empty($form)) {
            $form = json_decode(file_get_contents('tools/qrcards/setup/forms/1401.json'), true);
            $formManager->create($form);
        }
    }
}
