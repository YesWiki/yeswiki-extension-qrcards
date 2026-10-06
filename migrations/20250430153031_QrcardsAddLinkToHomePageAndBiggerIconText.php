<?php

use YesWiki\Core\YesWikiMigration;
use YesWiki\Qrcards\Service\QrcardsSetup;

/** The ten card types, the WikiCartothèque link in the quick access dropdown, and 30-character icon captions. */
class QrcardsAddLinkToHomePageAndBiggerIconText extends YesWikiMigration
{
    public function run(): void
    {
        $setup = $this->getService(QrcardsSetup::class);
        $setup->refreshList('ListeTypeCarte');
        $setup->placeInQuickMenu();
        $setup->widenIconTexts();
    }
}
