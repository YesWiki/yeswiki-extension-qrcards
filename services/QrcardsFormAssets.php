<?php

namespace YesWiki\Qrcards\Service;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use YesWiki\Content\Service\EntryManager;
use YesWiki\Kernel\Performable\PerformableEvent;
use YesWiki\Kernel\Service\AssetRegistry;
use YesWiki\Kernel\Service\PageContext;

/** Adds qrcards.css wherever the card or card set form is filled in: `{{bazar id="1400"}}` and a card's edit screen. */
class QrcardsFormAssets implements EventSubscriberInterface
{
    public const STYLESHEET = QrCardRenderer::ASSETS . '/styles/qrcards.css';
    public const FORMS = [QrcardsSetup::CARD_FORM, QrcardsSetup::CARD_SET_FORM];

    public function __construct(
        private readonly AssetRegistry $assets,
        private readonly EntryManager $entryManager,
        private readonly PageContext $pageContext,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'action.bazar.before' => 'onForm',
            'handler.edit.before' => 'onEdit',
            'handler.editiframe.before' => 'onEdit',
        ];
    }

    public function onForm(PerformableEvent $event): void
    {
        $id = $event->getArguments()['id'] ?? null;
        if (is_scalar($id) && in_array((string)$id, self::FORMS, true)) {
            $this->assets->addCssFile(self::STYLESHEET);
        }
    }

    public function onEdit(PerformableEvent $event): void
    {
        $tag = $this->pageContext->getTag();
        if ($tag === '' || !$this->entryManager->isEntry($tag)) {
            return;
        }
        $formId = $this->entryManager->getOne($tag)['form_id'] ?? null;
        if (is_scalar($formId) && in_array((string)$formId, self::FORMS, true)) {
            $this->assets->addCssFile(self::STYLESHEET);
        }
    }
}
