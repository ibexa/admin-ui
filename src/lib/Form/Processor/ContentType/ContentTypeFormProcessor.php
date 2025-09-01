<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Processor\ContentType;

use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\Contracts\AdminUi\Event\FormEvents;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeDraft;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

final class ContentTypeFormProcessor implements EventSubscriberInterface
{
    /** @var array<string, mixed> */
    private array $options;

    private ?FieldsGroupsList $groupsList = null;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private readonly ContentTypeService $contentTypeService,
        private readonly RouterInterface $router,
        array $options = []
    ) {
        $this->setOptions($options);
    }

    public function setGroupsList(FieldsGroupsList $groupsList): void
    {
        $this->groupsList = $groupsList;
    }

    public function setOptions(array $options = []): void
    {
        $this->options = $options + ['redirectRouteAfterPublish' => null];
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::CONTENT_TYPE_UPDATE => 'processDefaultAction',
            FormEvents::CONTENT_TYPE_ADD_FIELD_DEFINITION => 'processAddFieldDefinition',
            FormEvents::CONTENT_TYPE_REMOVE_FIELD_DEFINITION => 'processRemoveFieldDefinition',
            FormEvents::CONTENT_TYPE_PUBLISH => 'processPublishContentType',
            FormEvents::CONTENT_TYPE_PUBLISH_AND_EDIT => 'processPublishAndEditContentType',
            FormEvents::CONTENT_TYPE_REMOVE_DRAFT => 'processRemoveContentTypeDraft',
        ];
    }

    public function processDefaultAction(FormActionEvent $event): void
    {
        // Don't update anything if we just want to cancel the draft.
        if ($event->getClickedButton() === 'removeDraft') {
            return;
        }

        // Always update FieldDefinitions and ContentTypeDraft
        /** @var \Ibexa\AdminUi\Form\Data\ContentTypeData $contentTypeData */
        $contentTypeData = $event->getData();
        $contentTypeDraft = $contentTypeData->contentTypeDraft;
        foreach ($contentTypeData->getFlatFieldDefinitionsData() as $fieldDefData) {
            $this->contentTypeService->updateFieldDefinition(
                $contentTypeDraft,
                $fieldDefData->fieldDefinition,
                $fieldDefData
            );
        }

        // Update enabled FieldDefinitions and remove disabled.
        foreach ($contentTypeData->getFlatMetaFieldDefinitionsData() as $fieldDefData) {
            if ($fieldDefData->enabled) {
                $this->contentTypeService->updateFieldDefinition(
                    $contentTypeDraft,
                    $fieldDefData->fieldDefinition,
                    $fieldDefData
                );
            } else {
                $this->contentTypeService->removeFieldDefinition($contentTypeDraft, $fieldDefData->fieldDefinition);
            }
        }

        $contentTypeData->sortFieldDefinitions();
        $this->contentTypeService->updateContentTypeDraft($contentTypeDraft, $contentTypeData);
    }

    public function processAddFieldDefinition(FormActionEvent $event): void
    {
        // Reload the draft, to make sure we include any changes made in the current form submit
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($event->getData()->contentTypeDraft->id);
        $fieldTypeIdentifier = $event->getForm()->get('fieldTypeSelection')->getData();

        $targetLanguageCode = $event->getForm()->getConfig()->getOption('languageCode');
        if ($contentTypeDraft->mainLanguageCode !== $targetLanguageCode) {
            throw new InvalidArgumentException(
                'languageCode',
                'Field definitions can only be added to the main language translation'
            );
        }

        $maxFieldPos = 0;
        foreach ($contentTypeDraft->getFieldDefinitions() as $existingFieldDef) {
            if ($existingFieldDef->getPosition() > $maxFieldPos) {
                $maxFieldPos = $existingFieldDef->position;
            }
        }

        $fieldDefCreateStruct = new FieldDefinitionCreateStruct([
            'fieldTypeIdentifier' => $fieldTypeIdentifier,
            'identifier' => $this->resolveNewFieldDefinitionIdentifier(
                $contentTypeDraft,
                $maxFieldPos,
                $fieldTypeIdentifier
            ),
            'names' => [$event->getOption('languageCode') => 'New FieldDefinition'],
            'position' => $maxFieldPos + 1,
        ]);

        if (isset($this->groupsList)) {
            $fieldDefCreateStruct->fieldGroup = $this->groupsList->getDefaultGroup();
        }

        $this->contentTypeService->addFieldDefinition($contentTypeDraft, $fieldDefCreateStruct);
    }

    public function processRemoveFieldDefinition(FormActionEvent $event): void
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft */
        $contentTypeDraft = $event->getData()->contentTypeDraft;

        // Accessing FieldDefinition user selection through the form and not the data,
        // as "selected" is not a property of FieldDefinitionData.
        /** @var \Symfony\Component\Form\FormInterface $fieldDefForm */
        foreach ($event->getForm()->get('fieldDefinitionsData') as $fieldDefForm) {
            if ($fieldDefForm->get('selected')->getData() === true) {
                $this->contentTypeService->removeFieldDefinition(
                    $contentTypeDraft,
                    $fieldDefForm->getData()->fieldDefinition
                );
            }
        }
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function processPublishContentType(FormActionEvent $event): void
    {
        $contentTypeDraft = $event->getData()->contentTypeDraft;
        $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);
        if (isset($this->options['redirectRouteAfterPublish'])) {
            $event->setResponse(
                new RedirectResponse($this->router->generate($this->options['redirectRouteAfterPublish']))
            );
        }
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function processPublishAndEditContentType(FormActionEvent $event): void
    {
        $eventData = $event->getData();
        /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft */
        $contentTypeDraft = $eventData->contentTypeDraft;
        $languageCode = $eventData->languageCode;

        $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);

        $contentType = $this->contentTypeService->loadContentType($contentTypeDraft->id, [$languageCode]);
        $this->contentTypeService->createContentTypeDraft($contentType);
    }

    public function processRemoveContentTypeDraft(FormActionEvent $event): void
    {
        $contentTypeDraft = $event->getData()->contentTypeDraft;
        $this->contentTypeService->deleteContentType($contentTypeDraft);
        if (isset($this->options['redirectRouteAfterPublish'])) {
            $event->setResponse(
                new RedirectResponse($this->router->generate($this->options['redirectRouteAfterPublish']))
            );
        }
    }

    private function resolveNewFieldDefinitionIdentifier(
        ContentTypeDraft $contentTypeDraft,
        int $startIndex,
        string $fieldTypeIdentifier
    ): string {
        $fieldDefinitionIdentifiers = $contentTypeDraft
            ->getFieldDefinitions()
            ->map(static function (FieldDefinition $fieldDefinition): string {
                return $fieldDefinition->getIdentifier();
            });

        do {
            $fieldDefinitionIdentifier = sprintf('new_%s_%d', $fieldTypeIdentifier, ++$startIndex);
        } while (in_array($fieldDefinitionIdentifier, $fieldDefinitionIdentifiers, true));

        return $fieldDefinitionIdentifier;
    }
}
