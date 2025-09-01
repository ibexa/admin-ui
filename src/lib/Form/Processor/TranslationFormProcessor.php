<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

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
final readonly class TranslationFormProcessor implements EventSubscriberInterface
{
    public function __construct(
        private ContentService $contentService
    ) {
    }

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
     */
    public function createContentDraft(FormActionEvent $event): void
    {
        $data = $event->getData();
        if (!$data instanceof ContentTranslationData) {
            return;
        }

        $contentDraft = $this->contentService->createContentDraft($data->content->getContentInfo());
        $fields = array_filter($data->fieldsData, static function (FieldData $fieldData) use ($contentDraft, $data): bool {
            $mainLanguageCode = $contentDraft->getVersionInfo()->getContentInfo()->getMainLanguageCode();

            return $mainLanguageCode === $data->initialLanguageCode
                || $fieldData->getFieldDefinition()->isTranslatable();
        });
        $contentUpdateData = new ContentUpdateData([
            'initialLanguageCode' => $data->initialLanguageCode,
            'contentDraft' => $contentDraft,
            'fieldsData' => $fields,
        ]);

        $event->setData($contentUpdateData);
    }
}
