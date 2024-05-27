<?php

namespace YesWiki\Qrcards;

use YesWiki\Core\YesWikiHandler;
use YesWiki\Core\Service\AssetsManager;

class __EditHandler extends YesWikiHandler
{
    public function run()
    {
        // ugly workaround to get the styling in the picto images for qrcards
        $this->getService(AssetsManager::class)->AddCSSFile('tools/qrcards/styles/qrcards.css');
    }
}
