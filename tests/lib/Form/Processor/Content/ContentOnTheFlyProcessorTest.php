<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\Processor\Content;

use Ibexa\AdminUi\Form\Processor\Content\ContentOnTheFlyProcessor;
use Ibexa\ContentForms\Data\Content\ContentCreateData;
use Ibexa\ContentForms\Data\Content\ContentUpdateData;
use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Repository\Values\Content\Location;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * @covers \Ibexa\AdminUi\Form\Processor\Content\ContentOnTheFlyProcessor
 */
final class ContentOnTheFlyProcessorTest extends TestCase
{
    private const CREATE_RESPONSE_TEMPLATE = '@ibexadesign/ui/on_the_fly/content_create_response.html.twig';
    private const EDIT_RESPONSE_TEMPLATE = '@ibexadesign/ui/on_the_fly/content_edit_response.html.twig';

    private const MAIN_LOCATION_ID = 42;
    private const REFERRER_LOCATION_ID = 55;
    private const LANGUAGE_CODE = 'eng-GB';
    private const RENDERED_RESPONSE = 'rendered-response';

    private ContentService&MockObject $contentService;

    private Environment&MockObject $twig;

    private ContentOnTheFlyProcessor $processor;

    protected function setUp(): void
    {
        $this->contentService = $this->createMock(ContentService::class);
        $this->twig = $this->createMock(Environment::class);
        $this->processor = new ContentOnTheFlyProcessor($this->contentService, $this->twig);
    }

    public function testProcessCreatePublishPublishesNewContentSynchronously(): void
    {
        $draft = $this->createDraft();
        $data = $this->createCreateData();

        $this->contentService
            ->expects(self::once())
            ->method('createContent')
            ->with(self::identicalTo($data), [])
            ->willReturn($draft);

        $this->expectPublishVersion($draft);
        $this->expectRender(self::CREATE_RESPONSE_TEMPLATE, self::MAIN_LOCATION_ID);

        $event = $this->createEvent($data);

        $this->processor->processCreatePublish($event);

        $this->assertRenderedResponse($event);
    }

    public function testProcessCreatePublishPublishesExistingContentDraftSynchronously(): void
    {
        $draft = $this->createDraft();
        $data = $this->createUpdateData($draft);

        $this->contentService
            ->expects(self::once())
            ->method('updateContent')
            ->with(self::identicalTo($draft->getVersionInfo()), self::identicalTo($data))
            ->willReturn($draft);

        $this->expectPublishVersion($draft);
        $this->expectRender(self::CREATE_RESPONSE_TEMPLATE, self::REFERRER_LOCATION_ID);

        $event = $this->createEvent($data, [
            'referrerLocation' => new Location(['id' => self::REFERRER_LOCATION_ID]),
        ]);

        $this->processor->processCreatePublish($event);

        $this->assertRenderedResponse($event);
    }

    public function testProcessEditPublishRendersEditResponse(): void
    {
        $draft = $this->createDraft();
        $data = $this->createUpdateData($draft);

        $this->contentService
            ->method('updateContent')
            ->willReturn($draft);

        $this->expectPublishVersion($draft);
        $this->expectRender(self::EDIT_RESPONSE_TEMPLATE, self::MAIN_LOCATION_ID);

        $event = $this->createEvent($data);

        $this->processor->processEditPublish($event);

        $this->assertRenderedResponse($event);
    }

    private function expectPublishVersion(Content $draft): void
    {
        $this->contentService
            ->expects(self::once())
            ->method('publishVersion')
            ->with(self::identicalTo($draft->getVersionInfo()), [self::LANGUAGE_CODE])
            ->willReturn($this->createPublishedContent());
    }

    private function expectRender(string $template, int $locationId): void
    {
        $this->twig
            ->expects(self::once())
            ->method('render')
            ->with($template, ['locationId' => $locationId])
            ->willReturn(self::RENDERED_RESPONSE);
    }

    private function createCreateData(): ContentCreateData
    {
        return new ContentCreateData([
            'mainLanguageCode' => self::LANGUAGE_CODE,
            'fieldsData' => [],
        ]);
    }

    private function createUpdateData(Content $contentDraft): ContentUpdateData
    {
        return new ContentUpdateData([
            'contentDraft' => $contentDraft,
            'fieldsData' => [],
        ]);
    }

    private function createDraft(): Content
    {
        $contentInfo = new ContentInfo([
            'mainLanguageCode' => self::LANGUAGE_CODE,
        ]);

        $versionInfo = $this->createStub(VersionInfo::class);
        $versionInfo->method('getInitialLanguage')->willReturn(
            new Language(['languageCode' => self::LANGUAGE_CODE])
        );
        $versionInfo->method('getContentInfo')->willReturn($contentInfo);

        $draft = $this->createStub(Content::class);
        $draft->method('getVersionInfo')->willReturn($versionInfo);

        return $draft;
    }

    private function createPublishedContent(): Content
    {
        $contentInfo = new ContentInfo([
            'mainLocationId' => self::MAIN_LOCATION_ID,
            'mainLanguageCode' => self::LANGUAGE_CODE,
            'status' => ContentInfo::STATUS_PUBLISHED,
        ]);

        $publishedContent = $this->createStub(Content::class);
        $publishedContent->method('getContentInfo')->willReturn($contentInfo);

        return $publishedContent;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function createEvent(
        ContentCreateData|ContentUpdateData $data,
        array $options = []
    ): FormActionEvent {
        $formConfig = $this->createStub(FormConfigInterface::class);
        $formConfig->method('getOption')->willReturn(self::LANGUAGE_CODE);

        $form = $this->createStub(FormInterface::class);
        $form->method('getConfig')->willReturn($formConfig);

        return new FormActionEvent($form, $data, 'publish', $options);
    }

    private function assertRenderedResponse(FormActionEvent $event): void
    {
        $response = $event->getResponse();

        self::assertInstanceOf(Response::class, $response);
        self::assertSame(self::RENDERED_RESPONSE, $response->getContent());
    }
}
