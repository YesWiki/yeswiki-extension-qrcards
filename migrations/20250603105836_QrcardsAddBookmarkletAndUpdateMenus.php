<?php

use YesWiki\Core\YesWikiMigration;
use YesWiki\Qrcards\Service\QrcardsSetup;

/** The documentation page with its bookmarklet, the card form's bookmarklet, and the current quick access dropdown. */
class QrcardsAddBookmarkletAndUpdateMenus extends YesWikiMigration
{
    public function run()
    {
        $setup = $this->getService(QrcardsSetup::class);
        $setup->refreshPage('DocumentationQrCartes');
        $setup->enableBookmarklet();
        $setup->placeInQuickMenu();
    }
}
