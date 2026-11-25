<?php

namespace YesWiki\Qrcards\Service;

use YesWiki\Content\Entity\ExtendsFormBuilder;

/** Puts the `qrcard` field in the form builder. */
class QrcardsFormBuilder implements ExtendsFormBuilder
{
    public function formBuilderScripts(): array
    {
        return [QrCardRenderer::ASSETS . '/javascripts/form-builder-qrcard.js'];
    }

    public function formBuilderData(): array
    {
        return [];
    }
}
