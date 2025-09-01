<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Processor;

use Exception;
use Ibexa\AdminUi\Form\Event\ContentEditEvents;
use Ibexa\ContentForms\Data\Content\ContentCreateData;
use Ibexa\ContentForms\Data\Content\ContentUpdateData;
use Ibexa\ContentForms\Data\NewnessCheckable;
use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\Core\Exception\InvalidArgumentException;
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
final readonly class PreviewFormProcessor implements EventSubscriberInterface
{
    public function __construct(
        private ContentService $contentService,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatableNotificationHandlerInterface $notificationHandler,
        private LocationService $locationService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContentEditEvents::CONTENT_PREVIEW => ['processPreview', 10],
        ];
    }

    /**
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
            $contentDraft = $this->saveDraft($data, $languageCode);
            $contentLocation = $this->resolveLocation($contentDraft, $referrerLocation, $data);
            $url = $this->urlGenerator->generate('ibexa.content.preview', [
                'locationId' => $contentLocation?->getId(),
                'contentId' => $contentDraft->getId(),
                'versionNo' => $contentDraft->getVersionInfo()->getVersionNo(),
                'languageCode' => $languageCode,
            ]);
        } catch (Exception) {
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
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     */
    private function saveDraft(
        ContentCreateData|ContentStruct|ContentUpdateData $data,
        string $languageCode
    ): Content {
        $mainLanguageCode = $this->resolveMainLanguageCode($data);
        foreach ($data->getFieldsData() as $fieldDefIdentifier => $fieldData) {
            if ($mainLanguageCode != $languageCode && !$fieldData->getFieldDefinition()->isTranslatable()) {
                continue;
            }

            $data->setField($fieldDefIdentifier, $fieldData->getValue(), $languageCode);
        }

        if ($data->isNew()) {
            $contentDraft = $this->contentService->createContent($data, $data->getLocationStructs(), []);
        } else {
            $contentDraft = $this->contentService->updateContent(
                $data->getContentDraft()->getVersionInfo(),
                $data,
                []
            );
        }

        return $contentDraft;
    }

    /**
     * Returns content create or edit URL depending on $data type.
     *
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     */
    private function getContentEditUrl(ContentCreateData|ContentUpdateData $data, string $languageCode): string
    {
        return $data->isNew() && $data instanceof ContentCreateData
            ? $this->urlGenerator->generate('ibexa.content.create.proxy', [
                'parentLocationId' => $data->getLocationStructs()[0]->parentLocationId,
                'contentTypeIdentifier' => $data->contentType->getIdentifier(),
                'languageCode' => $languageCode,
            ])
            : $this->urlGenerator->generate('ibexa.content.draft.edit', [
                'contentId' => $data->getContentDraft()->getId(),
                'versionNo' => $data->getContentDraft()->getVersionInfo()->getVersionNo(),
                'language' => $languageCode,
            ]);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Exception\InvalidArgumentException If unable to resolve main language code given $data
     */
    private function resolveMainLanguageCode(ContentStruct $data): string
    {
        if ($data instanceof ContentCreateData) {
            return $data->mainLanguageCode;
        }

        if ($data instanceof ContentUpdateData) {
            return $data->getContentDraft()->getVersionInfo()->getContentInfo()->getMainLanguageCode();
        }

        throw new InvalidArgumentException('$data', 'Unable to resolve main language code for data of type: ' . get_class($data));
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    private function resolveLocation(
        Content $content,
        ?Location $referrerLocation,
        NewnessCheckable $data
    ): ?Location {
        if (
            $data->isNew()
            || (!$content->getContentInfo()->isPublished() && null === $content->getContentInfo()->getMainLocationId())
        ) {
            return null; // no location exists until new content is published
        }

        $mainLocationId = $content->getContentInfo()->getMainLocationId();
        if ($mainLocationId === null) {
            return null;
        }

        return $referrerLocation ?? $this->locationService->loadLocation($mainLocationId);
    }
}
