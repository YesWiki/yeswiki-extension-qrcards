<?php

namespace YesWiki\Qrcards\Action;

use YesWiki\Content\Service\EntryManager;
use YesWiki\Core\YesWikiAction;
use YesWiki\Kernel\Component\Category;
use YesWiki\Kernel\Component\Component;
use YesWiki\Kernel\Component\ProvidesComponents;
use YesWiki\Kernel\Component\Setting;
use YesWiki\Kernel\Performable\RegisteredAction;
use YesWiki\Qrcards\Service\QrCardRenderer;

/** `{{qrcard tag="my-card" view="print"}}`: one card entry drawn as its printable card. */
class QrCardAction extends YesWikiAction implements RegisteredAction, ProvidesComponents
{
    public static function performableName(): string
    {
        return 'qrcard';
    }

    public function components(): array
    {
        return [
            Component::for('qrcard')
                ->category(Category::Media)
                ->label(_t('QRCARDS_CARD_LABEL'))
                ->description(_t('QRCARDS_CARD_DESCRIPTION'))
                ->icon('qrcode')
                ->settings(
                    Setting::entry('tag')->label(_t('QRCARDS_CARD_TAG'))->required(),
                    Setting::choice('view', [
                        QrCardRenderer::VIEW_WEB => _t('QRCARDS_VIEW_WEB'),
                        QrCardRenderer::VIEW_PRINT => _t('QRCARDS_VIEW_PRINT'),
                    ])->label(_t('QRCARDS_CARD_VIEW'))->default(QrCardRenderer::VIEW_WEB),
                ),
        ];
    }

    public function formatArguments($arg): array
    {
        $view = (string)($arg['view'] ?? '');
        if ($view === '') {
            $view = $this->getRequest()->query->get('print') ? QrCardRenderer::VIEW_PRINT : QrCardRenderer::VIEW_WEB;
        }

        return [
            'tag' => trim((string)($arg['tag'] ?? '')),
            'view' => $view === QrCardRenderer::VIEW_PRINT ? QrCardRenderer::VIEW_PRINT : QrCardRenderer::VIEW_WEB,
        ];
    }

    public function run(): string
    {
        if ($this->arguments['tag'] === '') {
            return '<div class="yw-alert yw-alert--danger">qrcard : ' . _t('QRCARDS_CARD_TAG_REQUIRED') . '</div>';
        }
        $entry = $this->getService(EntryManager::class)->getOne($this->arguments['tag']);
        if (empty($entry)) {
            return '';
        }

        return $this->getService(QrCardRenderer::class)->render($entry, $this->arguments['view']);
    }
}
