<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\Processor;

use Ibexa\AdminUi\Form\Event\ContentEditEvents;
use Ibexa\AdminUi\Form\Processor\PreviewFormProcessor;
use Ibexa\ContentForms\Data\Content\ContentCreateData;
use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as APIContent;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationCreateStruct;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PreviewFormProcessorTest extends TestCase
{
    private ContentService&MockObject $contentService;

    private UrlGeneratorInterface&MockObject $urlGenerator;

    private TranslatableNotificationHandlerInterface&MockObject $notificationHandler;

    private LocationService&MockObject $locationService;

    protected function setUp(): void
    {
        $this->contentService = $this->createMock(ContentService::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->notificationHandler = $this->createMock(TranslatableNotificationHandlerInterface::class);
        $this->locationService = $this->createMock(LocationService::class);
    }

    private function createPreviewFormProcessor(
        ?ContentService $contentService = null,
        ?UrlGeneratorInterface $urlGenerator = null,
        ?TranslatableNotificationHandlerInterface $notificationHandler = null,
        ?LocationService $locationService = null
    ): PreviewFormProcessor {
        return new PreviewFormProcessor(
            $contentService ?? $this->contentService,
            $urlGenerator ?? $this->urlGenerator,
            $notificationHandler ?? $this->notificationHandler,
            $locationService ?? $this->locationService
        );
    }

    public function testProcessPreview(): void
    {
        $languageCode = 'cyb-CY';
        $contentDraftId = 123;
        $locationId = null;
        $url = 'http://url';
        $fieldDefinitionIdentifier = 'identifier_1';
        $fieldDataValue = 'some_value';

        /** $data variable in PreviewFormProcessor class */
        $contentStruct = $this->generateContentStruct(
            $languageCode,
            $fieldDefinitionIdentifier,
            $fieldDataValue
        );

        $contentDraft = $this->generateContentDraft($contentDraftId, $languageCode, $locationId);
        $contentService = $this->generateContentServiceMock($contentStruct, $contentDraft);
        $urlGenerator = $this->generateUrlGeneratorMock($contentDraft, $languageCode, $url, $locationId);

        $config = $this->generateConfigMock($languageCode);
        $form = $this->generateFormMock($config);

        $event = new FormActionEvent($form, $contentStruct, 'fooAction');

        $previewFormProcessor = $this->createPreviewFormProcessor($contentService, $urlGenerator, $this->notificationHandler);
        $previewFormProcessor->processPreview($event);

        self::assertEquals(new RedirectResponse($url), $event->getResponse());
    }

    public function testProcessPreviewHandleExceptionWithNew(): void
    {
        $languageCode = 'cyb-CY';
        $contentDraftId = 123;
        $url = 'http://url';
        $fieldDefinitionIdentifier = 'identifier_1';
        $locationId = 55;
        $fieldDataValue = 'some_value';

        $contentStruct = $this->generateContentStruct($languageCode, $fieldDefinitionIdentifier, $fieldDataValue);

        $config = $this->generateConfigMock($languageCode);

        $form = $this->generateFormMock($config);

        $event = new FormActionEvent($form, $contentStruct, 'fooAction');

        $contentDraft = $this->generateContentDraft($contentDraftId, $languageCode, $locationId);
        $contentService = $this->createMock(ContentService::class);
        $contentService
            ->expects(self::once())
            ->method('createContent')
            ->will(self::throwException(new class('Location not found') extends \Exception {
            }));

        $urlGenerator = $this->generateUrlGeneratorForContentEditUrlMock($contentDraft, $languageCode, $url);

        $previewFormProcessor = $this->createPreviewFormProcessor($contentService, $urlGenerator, $this->notificationHandler);

        $previewFormProcessor->processPreview($event);

        self::assertEquals(new RedirectResponse($url), $event->getResponse());
    }

    public function testSubscribedEvents(): void
    {
        $previewFormProcessor = $this->createPreviewFormProcessor();

        self::assertSame(
            [
                ContentEditEvents::CONTENT_PREVIEW => ['processPreview', 10],
            ],
            $previewFormProcessor::getSubscribedEvents()
        );
    }

    private function generateContentStruct(string $mainLanguageCode, string $fieldDefinitionIdentifier, string $fieldDataValue): ContentCreateData
    {
        $contentStruct = new ContentCreateData([
            'mainLanguageCode' => $mainLanguageCode,
            'contentType' => new ContentType(['identifier' => 123]),
        ]);
        $contentStruct->addFieldData(new FieldData([
            'fieldDefinition' => new FieldDefinition([
                'identifier' => $fieldDefinitionIdentifier,
            ]),
            'value' => $fieldDataValue,
        ]));
        $contentStruct->addLocationStruct(new LocationCreateStruct(['parentLocationId' => 234]));

        return $contentStruct;
    }

    private function generateContentServiceMock(ContentCreateData $contentStruct, APIContent $contentDraft): ContentService&MockObject
    {
        $contentService = $this->createMock(ContentService::class);
        $contentService
            ->expects(self::once())
            ->method('createContent')
            ->with($contentStruct, $contentStruct->getLocationStructs())
            ->willReturn($contentDraft);

        return $contentService;
    }

    /**
     * @phpstan-return \Symfony\Component\Form\FormConfigInterface<mixed>&MockObject
     */
    private function generateConfigMock(string $languageCode): FormConfigInterface&MockObject
    {
        $config = $this->createMock(FormConfigInterface::class);
        $config
            ->expects(self::once())
            ->method('getOption')
            ->with('languageCode')
            ->willReturn($languageCode);

        return $config;
    }

    /**
     * @param \Symfony\Component\Form\FormConfigInterface<mixed>&\PHPUnit\Framework\MockObject\MockObject $config
     */
    private function generateFormMock(FormConfigInterface&MockObject $config): FormInterface&MockObject
    {
        $form = $this->createMock(FormInterface::class);
        $form
            ->expects(self::once())
            ->method('getConfig')
            ->willReturn($config);

        return $form;
    }

    private function generateUrlGeneratorMock(
        APIContent $contentDraft,
        string $languageCode,
        string $url,
        ?int $locationId = null
    ): UrlGeneratorInterface&MockObject {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->method('generate')
            ->with('ibexa.content.preview', [
                'contentId' => $contentDraft->id,
                'versionNo' => $contentDraft->getVersionInfo()->versionNo,
                'languageCode' => $languageCode,
                'locationId' => $locationId,
            ])
            ->willReturn($url);

        return $urlGenerator;
    }

    private function generateUrlGeneratorForContentEditUrlMock(APIContent $contentDraft, string $languageCode, string $url): UrlGeneratorInterface&MockObject
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects(self::once())
            ->method('generate')
            ->with('ibexa.content.create.proxy', [
                'parentLocationId' => '234',
                'contentTypeIdentifier' => $contentDraft->id,
                'languageCode' => $languageCode,
            ])
            ->willReturn($url);

        return $urlGenerator;
    }

    private function generateContentDraft(int $contentDraftId, string $languageCode, ?int $mainLocationId): APIContent
    {
        return new Content([
            'versionInfo' => new VersionInfo(
                [
                    'contentInfo' => new ContentInfo([
                        'id' => $contentDraftId,
                        'mainLanguageCode' => $languageCode,
                        'mainLocationId' => $mainLocationId,
                    ]),
                ]
            ),
        ]);
    }
}
