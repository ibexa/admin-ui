<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\EventListener;

use Ibexa\Contracts\AdminUi\Autosave\AutosaveServiceInterface;
use Ibexa\Contracts\AdminUi\Event\ContentProxyCreateEvent;
use Ibexa\Contracts\AdminUi\Event\ContentProxyTranslateEvent;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

final readonly class ContentProxyCreateDraftListener implements EventSubscriberInterface
{
    public function __construct(
        private ContentService $contentService,
        private LocationService $locationService,
        private AutosaveServiceInterface $autosaveService,
        private RouterInterface $router
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContentProxyCreateEvent::class => 'create',
            ContentProxyTranslateEvent::class => 'translate',
        ];
    }

    public function create(ContentProxyCreateEvent $event): void
    {
        if (!$this->autosaveService->isEnabled()) {
            return;
        }

        $options = $event->getOptions();

        $createContentStruct = $this->contentService->newContentCreateStruct(
            $event->getContentType(),
            $event->getLanguageCode()
        );

        $contentDraft = $this->contentService->createContent(
            $createContentStruct,
            [
                $this->locationService->newLocationCreateStruct($event->getParentLocationId()),
            ],
            []
        );

        $options->set(ContentProxyCreateEvent::OPTION_CONTENT_DRAFT, $contentDraft);

        if ($options->get(ContentProxyCreateEvent::OPTION_IS_ON_THE_FLY, false)) {
            $response = new RedirectResponse(
                $this->router->generate('ibexa.content.on_the_fly.edit', [
                    'contentId' => $contentDraft->getId(),
                    'versionNo' => $contentDraft->getVersionInfo()->getVersionNo(),
                    'languageCode' => $event->getLanguageCode(),
                    'locationId' => $contentDraft->getContentInfo()->getMainLocationId(),
                ])
            );
        } else {
            $response = new RedirectResponse(
                $this->router->generate('ibexa.content.draft.edit', [
                    'contentId' => $contentDraft->getId(),
                    'versionNo' => $contentDraft->getVersionInfo()->getVersionNo(),
                    'language' => $event->getLanguageCode(),
                ])
            );
        }

        $event->setResponse($response);
    }

    public function translate(ContentProxyTranslateEvent $event): void
    {
        if (!$this->autosaveService->isEnabled()) {
            return;
        }

        $fromLanguageCode = $event->getFromLanguageCode();
        $content = $this->contentService->loadContent(
            $event->getContentId(),
            $fromLanguageCode !== null
                ? [$fromLanguageCode]
                : null
        );

        $toLanguageCode = $event->getToLanguageCode();

        $contentUpdateStruct = $this->contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = $toLanguageCode;
        if ($fromLanguageCode !== null) {
            $contentUpdateStruct->fields = $this->getTranslatedContentFields($content, $toLanguageCode);
        }

        $contentDraft = $this->contentService->createContentDraft($content->getContentInfo());

        $this->contentService->updateContent(
            $contentDraft->getVersionInfo(),
            $contentUpdateStruct,
            []
        );

        $response = new RedirectResponse(
            $this->router->generate('ibexa.content.draft.edit', [
                'contentId' => $contentDraft->getId(),
                'versionNo' => $contentDraft->getVersionInfo()->getVersionNo(),
                'language' => $toLanguageCode,
                'locationId' => $event->getLocationId(),
            ])
        );

        $event->setResponse($response);
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Field[]
     */
    private function getTranslatedContentFields(Content $content, string $languageCode): array
    {
        $contentType = $content->getContentType();

        $translatableFields = array_filter($content->getFields(), static function (Field $field) use ($contentType): bool {
            return $contentType->getFieldDefinition(
                $field->getFieldDefinitionIdentifier()
            )->isTranslatable();
        });

        return array_map(static function (Field $field) use ($languageCode): Field {
            return new Field([
                'value' => $field->getValue(),
                'fieldDefIdentifier' => $field->getFieldDefinitionIdentifier(),
                'fieldTypeIdentifier' => $field->getFieldTypeIdentifier(),
                'languageCode' => $languageCode,
            ]);
        }, $translatableFields);
    }
}
