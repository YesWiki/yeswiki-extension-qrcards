<?php

use YesWiki\Core\YesWikiMigration;
use YesWiki\Qrcards\Service\QrcardsSetup;

/** The card set form, 1401. */
class QrcardsAddCardSetForm extends YesWikiMigration
{
    public function run(): void
    {
        $this->getService(QrcardsSetup::class)->installForm(QrcardsSetup::CARD_SET_FORM);
    }
}
