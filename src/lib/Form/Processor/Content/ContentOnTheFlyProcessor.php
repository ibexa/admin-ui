<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Form\Processor\Content;

use Ibexa\ContentForms\Data\Content\ContentCreateData;
use Ibexa\ContentForms\Data\Content\ContentUpdateData;
use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\Contracts\AdminUi\Event\ContentOnTheFlyEvents;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final readonly class ContentOnTheFlyProcessor implements EventSubscriberInterface
{
    public function __construct(
        private ContentService $contentService,
        private Environment $twig
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContentOnTheFlyEvents::CONTENT_CREATE_PUBLISH => ['processCreatePublish', 10],
            ContentOnTheFlyEvents::CONTENT_EDIT_PUBLISH => ['processEditPublish', 10],
        ];
    }

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function processCreatePublish(FormActionEvent $event): void
    {
        $locationId = $this->publishContent($event);

        // We only need to change the response so it's compatible with UDW
        $event->setResponse(
            new Response(
                $this->twig->render('@ibexadesign/ui/on_the_fly/content_create_response.html.twig', [
                    'locationId' => $locationId,
                ])
            )
        );
    }

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function processEditPublish(FormActionEvent $event): void
    {
        $locationId = $this->publishContent($event);

        // We only need to change the response so it's compatible with UDW
        $event->setResponse(
            new Response(
                $this->twig->render('@ibexadesign/ui/on_the_fly/content_edit_response.html.twig', [
                    'locationId' => $locationId,
                ])
            )
        );
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    private function publishContent(FormActionEvent $event): ?int
    {
        /** @var \Ibexa\ContentForms\Data\Content\ContentCreateData|\Ibexa\ContentForms\Data\Content\ContentUpdateData $data */
        $data = $event->getData();
        $form = $event->getForm();

        $draft = $this->saveDraft($data, $form->getConfig()->getOption('languageCode'));
        $versionInfo = $draft->getVersionInfo();
        $content = $this->contentService->publishVersion(
            $versionInfo,
            [$versionInfo->getInitialLanguage()->getLanguageCode()]
        );

        $referrerLocation = $event->getOption('referrerLocation');

        return $referrerLocation
            ? $referrerLocation->id
            : $content->getContentInfo()->getMainLocationId();
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    private function saveDraft(ContentCreateData|ContentUpdateData $data, string $languageCode): Content
    {
        $mainLanguageCode = $data instanceof ContentCreateData
            ? $data->mainLanguageCode
            : $data->getContentDraft()->getVersionInfo()->getContentInfo()->getMainLanguageCode();

        foreach ($data->getFieldsData() as $fieldDefIdentifier => $fieldData) {
            if ($mainLanguageCode !== $languageCode && !$fieldData->getFieldDefinition()->isTranslatable()) {
                continue;
            }

            $data->setField($fieldDefIdentifier, $fieldData->getValue(), $languageCode);
        }

        if ($data instanceof ContentCreateData) {
            return $this->contentService->createContent($data, $data->getLocationStructs());
        }

        return $this->contentService->updateContent($data->getContentDraft()->getVersionInfo(), $data);
    }
}
