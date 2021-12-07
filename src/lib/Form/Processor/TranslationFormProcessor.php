<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\AdminUi\Form\Processor;

use Ibexa\AdminUi\Form\Data\ContentTranslationData;
use Ibexa\ContentForms\Data\Content\ContentUpdateData;
use Ibexa\ContentForms\Event\ContentFormEvents;
use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\Repository\ContentService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listens for and processes RepositoryForm events: publish, remove draft, save draft...
 */
class TranslationFormProcessor implements EventSubscriberInterface
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    private $contentService;

    public function __construct(
        ContentService $contentService
    ) {
        $this->contentService = $contentService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ContentFormEvents::CONTENT_EDIT => ['createContentDraft', 20],
        ];
    }

    /**
     * Creates content draft based in data submitted by the user and injects ContentUpdateData to the event.
     *
     * This step is required to achieve compatibility with other FormProcessors.
     *
     * @param \Ibexa\ContentForms\Event\FormActionEvent $event
     */
    public function createContentDraft(FormActionEvent $event): void
    {
        /** @var \Ibexa\AdminUi\Form\Data\ContentTranslationData $data */
        $data = $event->getData();

        if (!$data instanceof ContentTranslationData) {
            return;
        }

        $contentDraft = $this->contentService->createContentDraft($data->content->contentInfo);
        $fields = array_filter($data->fieldsData, static function (FieldData $fieldData) use ($contentDraft, $data) {
            $mainLanguageCode = $contentDraft->getVersionInfo()->getContentInfo()->mainLanguageCode;

            return $mainLanguageCode === $data->initialLanguageCode
                || ($mainLanguageCode !== $data->initialLanguageCode && $fieldData->fieldDefinition->isTranslatable);
        });
        $contentUpdateData = new ContentUpdateData([
            'initialLanguageCode' => $data->initialLanguageCode,
            'contentDraft' => $contentDraft,
            'fieldsData' => $fields,
        ]);

        $event->setData($contentUpdateData);
    }
}

class_alias(TranslationFormProcessor::class, 'EzSystems\EzPlatformAdminUi\Form\Processor\TranslationFormProcessor');
