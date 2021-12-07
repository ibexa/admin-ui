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
    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    private $contentService;

    /** @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface */
    private $urlGenerator;

    /** @var \Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface */
    private $notificationHandler;

    /** @var \Ibexa\Contracts\Core\Repository\LocationService */
    private $locationService;

    protected function setUp(): void
    {
        $this->contentService = $this->createMock(ContentService::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->notificationHandler = $this->createMock(TranslatableNotificationHandlerInterface::class);
        $this->locationService = $this->createMock(LocationService::class);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService|null $contentService
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface|null $urlGenerator
     * @param \Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface|null $notificationHandler
     * @param \Ibexa\Contracts\Core\Repository\LocationService|null $locationService
     *
     * @return \Ibexa\AdminUi\Form\Processor\PreviewFormProcessor
     */
    private function createPreviewFormProcessor(
        ContentService $contentService = null,
        UrlGeneratorInterface $urlGenerator = null,
        TranslatableNotificationHandlerInterface $notificationHandler = null,
        LocationService $locationService = null
    ): PreviewFormProcessor {
        return new PreviewFormProcessor(
            $contentService ?? $this->contentService,
            $urlGenerator ?? $this->urlGenerator,
            $notificationHandler ?? $this->notificationHandler,
            $locationService ?? $this->locationService
        );
    }

    public function testProcessPreview()
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

        $this->assertEquals(new RedirectResponse($url), $event->getResponse());
    }

    public function testProcessPreviewHandleExceptionWithNew()
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
            ->will($this->throwException(new class('Location not found') extends \Exception {
            }));

        $urlGenerator = $this->generateUrlGeneratorForContentEditUrlMock($contentDraft, $languageCode, $url);

        $previewFormProcessor = $this->createPreviewFormProcessor($contentService, $urlGenerator, $this->notificationHandler);

        $previewFormProcessor->processPreview($event);

        $this->assertEquals(new RedirectResponse($url), $event->getResponse());
    }

    public function testSubscribedEvents()
    {
        $previewFormProcessor = $this->createPreviewFormProcessor();

        $this->assertSame([ContentEditEvents::CONTENT_PREVIEW => ['processPreview', 10]], $previewFormProcessor::getSubscribedEvents());
    }

    /**
     * @param string $mainLanguageCode
     * @param string $fieldDefinitionIdentifier
     * @param string $fieldDataValue
     *
     * @return \Ibexa\ContentForms\Data\Content\ContentCreateData
     */
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

    /**
     * @param \Ibexa\ContentForms\Data\Content\ContentCreateData $contentStruct
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $contentDraft
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function generateContentServiceMock(ContentCreateData $contentStruct, APIContent $contentDraft): MockObject
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
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function generateConfigMock($languageCode): MockObject
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
     * @param $config
     *
     * @return \Symfony\Component\Form\FormInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function generateFormMock($config): MockObject
    {
        $form = $this->createMock(FormInterface::class);
        $form
            ->expects(self::once())
            ->method('getConfig')
            ->willReturn($config);

        return $form;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $contentDraft
     * @param string $languageCode
     * @param string $url
     * @param int|null $locationId
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function generateUrlGeneratorMock(
        APIContent $contentDraft,
        string $languageCode,
        string $url,
        ?int $locationId = null
    ): MockObject {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->method('generate')
            ->with('ezplatform.content.preview', [
                'contentId' => $contentDraft->id,
                'versionNo' => $contentDraft->getVersionInfo()->versionNo,
                'languageCode' => $languageCode,
                'locationId' => $locationId,
            ])
            ->willReturn($url);

        return $urlGenerator;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $contentDraft
     * @param string $languageCode
     * @param string $url
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function generateUrlGeneratorForContentEditUrlMock(APIContent $contentDraft, string $languageCode, string $url): MockObject
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects(self::once())
            ->method('generate')
            ->with('ezplatform.content.create.proxy', [
                'parentLocationId' => '234',
                'contentTypeIdentifier' => $contentDraft->id,
                'languageCode' => $languageCode,
            ])
            ->willReturn($url);

        return $urlGenerator;
    }

    /**
     * @param $contentDraftId
     * @param $languageCode
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Content
     */
    private function generateContentDraft($contentDraftId, $languageCode, $mainLocationId): APIContent
    {
        $contentDraft = new Content([
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

        return $contentDraft;
    }
}

class_alias(PreviewFormProcessorTest::class, 'EzSystems\EzPlatformAdminUi\Tests\Form\Processor\PreviewFormProcessorTest');
