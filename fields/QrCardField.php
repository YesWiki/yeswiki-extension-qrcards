<?php

namespace YesWiki\Qrcards\Field;

use YesWiki\Content\Attribute\Field;
use YesWiki\Content\Field\BazarField;
use YesWiki\Content\Field\ContributesNoSearchableText;
use YesWiki\Kernel\Service\AssetRegistry;
use YesWiki\Qrcards\Service\QrCardRenderer;

/** `qrcard`: shows the entry drawn as its QR card where the field sits, in any form; it stores nothing. */
#[Field(['qrcard'])]
class QrCardField extends BazarField
{
    use ContributesNoSearchableText;

    protected function getValue($entry)
    {
        return null;
    }

    public function getValueStructure()
    {
        return [];
    }

    protected function renderInput($entry)
    {
        return null;
    }

    public function formatValuesBeforeSave($entry)
    {
        return [];
    }

    protected function renderStatic($entry)
    {
        if (!is_array($entry) || empty($entry['tag'])) {
            return '';
        }
        $assets = $this->getService(AssetRegistry::class);
        $assets->addCssFile(QrCardRenderer::ASSETS . '/styles/qrcards.css');
        $assets->addJsFile(QrCardRenderer::ASSETS . '/javascripts/qrcards.js');
        $print = $this->getRequest()->query->get('print');
        $view = $print ? QrCardRenderer::VIEW_PRINT : QrCardRenderer::VIEW_WEB;
        $card = $this->getService(QrCardRenderer::class)->render($entry, $view, QrCardRenderer::printFormat($print));

        return $view === QrCardRenderer::VIEW_PRINT
            ? '<div class="page-qrcard print">' . $card . '</div>'
            : '<div class="qrcard-entry-card">' . $card . '</div>';
    }
}
