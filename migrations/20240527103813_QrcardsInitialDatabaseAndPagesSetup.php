<?php

use YesWiki\Core\YesWikiMigration;
use YesWiki\Qrcards\Service\QrcardsSetup;

/** The card form, its value lists, two sample cards, the qrcards pages, their tab menu and the quick access dropdown. */
class QrcardsInitialDatabaseAndPagesSetup extends YesWikiMigration
{
    public function run()
    {
        $setup = $this->getService(QrcardsSetup::class);
        $setup->installLists();
        $setup->installForm(QrcardsSetup::CARD_FORM);
        $setup->installEntries();
        $setup->installPages();
        $setup->installMenu();
        $setup->placeInQuickMenu();
    }
}
