<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\EventListener;

use Ibexa\AdminUi\Event\Options;
use Ibexa\AdminUi\EventListener\ContentProxyCreateDraftListener;
use Ibexa\Contracts\AdminUi\Autosave\AutosaveServiceInterface;
use Ibexa\Contracts\AdminUi\Event\ContentProxyCreateEvent;
use Ibexa\Contracts\AdminUi\Event\ContentProxyTranslateEvent;
use Ibexa\Contracts\Core\FieldType\Value;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

final class ContentProxyCreateDraftListenerTest extends TestCase
{
    public function testCreateContentAutosaveEnabled(): void
    {
        $autosaveService = $this->createMock(AutosaveServiceInterface::class);
        $autosaveService->method('isEnabled')->willReturn(true);

        $contentService = $this->createMock(ContentService::class);
        $contentService
            ->method('createContent')
            ->willReturn($this->createMock(Content::class));

        $router = $this->createMock(RouterInterface::class);
        $router
            ->method('generate')
            ->with('ibexa.content.draft.edit', [
                'contentId' => null,
                'versionNo' => null,
                'language' => 'eng-EN',
            ])
            ->willReturn('redirect_test_url');

        $createEvent = new ContentProxyCreateEvent(
            $this->createMock(ContentType::class),
            'eng-EN',
            1234
        );

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(
            new ContentProxyCreateDraftListener(
                $contentService,
                $this->createMock(LocationService::class),
                $autosaveService,
                $router
            )
        );

        $eventDispatcher->dispatch($createEvent);

        self::assertEquals(new RedirectResponse('redirect_test_url'), $createEvent->getResponse());
        self::assertInstanceOf(
            Content::class,
            $createEvent->getOptions()->get('contentDraft')
        );
    }

    public function testCreateContentOnTheFlyAutosaveEnabled(): void
    {
        $autosaveService = $this->createMock(AutosaveServiceInterface::class);
        $autosaveService->method('isEnabled')->willReturn(true);

        $contentInfo = $this->createMock(ContentInfo::class);

        $content = $this->createMock(Content::class);
        $content
            ->method('__get')
            ->will(self::returnCallback(static function ($argument) use ($contentInfo): ?\PHPUnit\Framework\MockObject\MockObject {
                if ($argument === 'contentInfo') {
                    return $contentInfo;
                }

                return null;
            }));

        $contentService = $this->createMock(ContentService::class);
        $contentService
            ->method('createContent')
            ->willReturn($content);

        $router = $this->createMock(RouterInterface::class);
        $router
            ->method('generate')
            ->with('ibexa.content.on_the_fly.edit', [
                'contentId' => null,
                'versionNo' => null,
                'languageCode' => 'eng-EN',
                'locationId' => null,
            ])
            ->willReturn('redirect_on_the_fly_test_url');

        $createEvent = new ContentProxyCreateEvent(
            $this->createMock(ContentType::class),
            'eng-EN',
            1234,
            new Options([
                'isOnTheFly' => true,
            ])
        );

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(
            new ContentProxyCreateDraftListener(
                $contentService,
                $this->createMock(LocationService::class),
                $autosaveService,
                $router
            )
        );

        $eventDispatcher->dispatch($createEvent);

        self::assertEquals(new RedirectResponse('redirect_on_the_fly_test_url'), $createEvent->getResponse());
        self::assertInstanceOf(
            Content::class,
            $createEvent->getOptions()->get('contentDraft')
        );
    }

    public function testTranslateContentAutosaveEnabled(): void
    {
        $autosaveService = $this->createMock(AutosaveServiceInterface::class);
        $autosaveService->method('isEnabled')->willReturn(true);

        $contentType = $this->getContentType([
            $this->getFieldDefinition('field_a', true),
        ]);

        $contentInfo = $this->createMock(ContentInfo::class);

        $content = $this->createMock(Content::class);
        $content
            ->method('getContentType')
            ->willReturn($contentType);

        $content
            ->method('getFields')
            ->willReturn([
                new Field([
                    'fieldDefIdentifier' => 'field_a',
                    'value' => 'test',
                ]),
            ]);

        $content
            ->method('__get')
            ->with('contentInfo')
            ->willReturn($contentInfo);

        $contentService = $this->createMock(ContentService::class);
        $contentService
            ->method('loadContent')
            ->with(123, ['eng-GB'])
            ->willReturn($content);

        $contentService
            ->method('createContentDraft')
            ->with($contentInfo)
            ->willReturn($this->createMock(Content::class));

        $router = $this->createMock(RouterInterface::class);
        $router
            ->method('generate')
            ->with('ibexa.content.draft.edit', [
                'contentId' => null,
                'versionNo' => null,
                'language' => 'pol-PL',
                'locationId' => null,
            ])
            ->willReturn('redirect_test_url');

        $translateEvent = new ContentProxyTranslateEvent(
            123,
            'eng-GB',
            'pol-PL'
        );

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(
            new ContentProxyCreateDraftListener(
                $contentService,
                $this->createMock(LocationService::class),
                $autosaveService,
                $router
            )
        );

        $eventDispatcher->dispatch($translateEvent, ContentProxyTranslateEvent::class);

        self::assertEquals(new RedirectResponse('redirect_test_url'), $translateEvent->getResponse());
    }

    public function testAutosaveDisabled(): void
    {
        $autosaveService = $this->createMock(AutosaveServiceInterface::class);
        $autosaveService->method('isEnabled')->willReturn(false);

        $contentService = $this->createMock(ContentService::class);
        $contentService
            ->expects(self::never())
            ->method('createContent');

        $createEvent = $this->createMock(ContentProxyCreateEvent::class);
        $createEvent
            ->expects(self::never())
            ->method('setResponse');

        $createOnTheFlyEvent = $this->createMock(ContentProxyCreateEvent::class);
        $createOnTheFlyEvent
            ->expects(self::never())
            ->method('setResponse');

        $createOnTheFlyEvent
            ->method('getOptions')
            ->willReturn(new Options([
                'isOnTheFly' => true,
            ]));

        $translateEvent = $this->createMock(ContentProxyTranslateEvent::class);
        $translateEvent
            ->expects(self::never())
            ->method('setResponse');

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(
            new ContentProxyCreateDraftListener(
                $contentService,
                $this->createMock(LocationService::class),
                $autosaveService,
                $this->createMock(RouterInterface::class)
            )
        );

        $eventDispatcher->dispatch($createEvent, ContentProxyCreateEvent::class);
        $eventDispatcher->dispatch($createOnTheFlyEvent, ContentProxyCreateEvent::class);
        $eventDispatcher->dispatch($translateEvent, ContentProxyTranslateEvent::class);
    }

    private function getContentType(array $fieldDefs = []): ContentType
    {
        return new ContentType([
            'fieldDefinitions' => new FieldDefinitionCollection($fieldDefs),
        ]);
    }

    private function getFieldDefinition(string $identifier = 'identifier', bool $isTranslatable = false): FieldDefinition
    {
        return new FieldDefinition([
            'identifier' => $identifier,
            'defaultValue' => $this->createMock(Value::class),
            'isTranslatable' => $isTranslatable,
        ]);
    }
}
