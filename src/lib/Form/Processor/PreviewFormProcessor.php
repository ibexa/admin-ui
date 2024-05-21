<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Processor;

use Exception;
use Ibexa\AdminUi\Form\Event\ContentEditEvents;
use Ibexa\ContentForms\Data\NewnessCheckable;
use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentStruct;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Listens for and processes RepositoryForm events.
 */
class PreviewFormProcessor implements EventSubscriberInterface
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    private $contentService;

    /** @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface */
    private $urlGenerator;

    /** @var \Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface */
    private $notificationHandler;

    /** @var \Ibexa\Contracts\Core\Repository\LocationService */
    private $locationService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
     * @param \Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface $notificationHandler
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     */
    public function __construct(
        ContentService $contentService,
        UrlGeneratorInterface $urlGenerator,
        TranslatableNotificationHandlerInterface $notificationHandler,
        LocationService $locationService
    ) {
        $this->contentService = $contentService;
        $this->urlGenerator = $urlGenerator;
        $this->notificationHandler = $notificationHandler;
        $this->locationService = $locationService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ContentEditEvents::CONTENT_PREVIEW => ['processPreview', 10],
        ];
    }

    /**
     * @param \Ibexa\ContentForms\Event\FormActionEvent $event
     *
     * @throws \InvalidArgumentException
     */
    public function processPreview(FormActionEvent $event): void
    {
        /** @var \Ibexa\ContentForms\Data\Content\ContentCreateData|\Ibexa\ContentForms\Data\Content\ContentUpdateData $data */
        $data = $event->getData();
        $form = $event->getForm();
        $languageCode = $form->getConfig()->getOption('languageCode');
        $referrerLocation = $event->getOption('referrerLocation');

        try {
            $contentDraft = $this->saveDraft($data, $languageCode, []);
            $contentLocation = $this->resolveLocation($contentDraft, $referrerLocation, $data);
            $url = $this->urlGenerator->generate('ibexa.content.preview', [
                'locationId' => null !== $contentLocation ? $contentLocation->id : null,
                'contentId' => $contentDraft->id,
                'versionNo' => $contentDraft->getVersionInfo()->versionNo,
                'languageCode' => $languageCode,
            ]);
        } catch (Exception $e) {
            $this->notificationHandler->error(
                /** @Desc("Cannot save content draft.") */
                'error.preview',
                [],
                'ibexa_content_preview'
            );
            $url = $this->getContentEditUrl($data, $languageCode);
        }

        $event->setResponse(
            new RedirectResponse($url)
        );
    }

    /**
     * Saves content draft corresponding to $data.
     * Depending on the nature of $data (create or update data), the draft will either be created or simply updated.
     *
     * @param \Ibexa\ContentForms\Data\Content\ContentCreateData|\Ibexa\Contracts\Core\Repository\Values\Content\ContentStruct|\Ibexa\ContentForms\Data\Content\ContentUpdateData $data
     * @param string $languageCode
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Content
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     */
    private function saveDraft(ContentStruct $data, string $languageCode, ?array $fieldIdentifiersToValidate): Content
    {
        $mainLanguageCode = $this->resolveMainLanguageCode($data);
        foreach ($data->fieldsData as $fieldDefIdentifier => $fieldData) {
            if ($mainLanguageCode != $languageCode && !$fieldData->fieldDefinition->isTranslatable) {
                continue;
            }

            $data->setField($fieldDefIdentifier, $fieldData->value, $languageCode);
        }

        if ($data->isNew()) {
            $contentDraft = $this->contentService->createContent($data, $data->getLocationStructs(), $fieldIdentifiersToValidate);
        } else {
            $contentDraft = $this->contentService->updateContent($data->contentDraft->getVersionInfo(), $data, $fieldIdentifiersToValidate);
        }

        return $contentDraft;
    }

    /**
     * Returns content create or edit URL depending on $data type.
     *
     * @param \Ibexa\ContentForms\Data\Content\ContentCreateData|\Ibexa\ContentForms\Data\Content\ContentUpdateData $data
     * @param string $languageCode
     *
     * @return string
     *
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     */
    private function getContentEditUrl($data, string $languageCode): string
    {
        return $data->isNew()
            ? $this->urlGenerator->generate('ibexa.content.create.proxy', [
                'parentLocationId' => $data->getLocationStructs()[0]->parentLocationId,
                'contentTypeIdentifier' => $data->contentType->identifier,
                'languageCode' => $languageCode,
            ])
            : $this->urlGenerator->generate('ibexa.content.draft.edit', [
                'contentId' => $data->contentDraft->id,
                'versionNo' => $data->contentDraft->getVersionInfo()->versionNo,
                'language' => $languageCode,
            ]);
    }

    /**
     * @param \Ibexa\ContentForms\Data\Content\ContentCreateData|\Ibexa\ContentForms\Data\Content\ContentUpdateData|\Ibexa\AdminUi\Form\Data\NewnessChecker $data
     *
     * @return string
     */
    private function resolveMainLanguageCode($data): string
    {
        return $data->isNew()
            ? $data->mainLanguageCode
            : $data->contentDraft->getVersionInfo()->getContentInfo()->mainLanguageCode;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location|null $referrerLocation
     * @param \Ibexa\ContentForms\Data\NewnessCheckable $data
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location|null
     */
    private function resolveLocation(Content $content, ?Location $referrerLocation, NewnessCheckable $data): ?Location
    {
        if ($data->isNew() || (!$content->contentInfo->published && null === $content->contentInfo->mainLocationId)) {
            return null; // no location exists until new content is published
        }

        return $referrerLocation ?? $this->locationService->loadLocation($content->contentInfo->mainLocationId);
    }
}
