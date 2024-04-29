<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\EventListener;

use Ibexa\AdminUi\EventListener\ContentTypeSuggestionsListener;
use Ibexa\AdminUi\Form\Type\Event\ContentCreateContentTypeChoiceLoaderEvent;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult\TermAggregationResult;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult\TermAggregationResultEntry;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResultCollection;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContentTypeSuggestionsListenerTest extends TestCase
{
    /** @var \Ibexa\Contracts\Core\Repository\SearchService&\PHPUnit\Framework\MockObject\MockObject */
    private SearchService $searchService;

    /** @var \Symfony\Contracts\Translation\TranslatorInterface&\PHPUnit\Framework\MockObject\MockObject */
    private TranslatorInterface $translator;

    protected function setUp(): void
    {
        $this->searchService = $this->createMock(SearchService::class);

        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')->willReturnArgument(0);
    }

    public function testSubscribedEvents(): void
    {
        $actualSubservients = array_keys(ContentTypeSuggestionsListener::getSubscribedEvents());

        self::assertEquals([
            ContentCreateContentTypeChoiceLoaderEvent::RESOLVE_CONTENT_TYPES,
        ], $actualSubservients);
    }

    public function testSkipSuggestionComputationIfAggregationAPIIsNotSupported(): void
    {
        $this->disableSupportForAggregationAPI();
        $this->expectSuggestionsAreNotComputed();

        $event = new ContentCreateContentTypeChoiceLoaderEvent([], $this->createExampleLocation());

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber($this->createDefaultListener());
        $eventDispatcher->dispatch($event, ContentCreateContentTypeChoiceLoaderEvent::RESOLVE_CONTENT_TYPES);

        self::assertEmpty($event->getContentTypeGroups());
    }

    public function testSkipSuggestionComputationIfDisabled(): void
    {
        $this->enableSupportForAggregationAPI();
        $this->expectSuggestionsAreNotComputed();

        $listener = new ContentTypeSuggestionsListener(
            $this->searchService,
            $this->translator,
            /* Disable suggestion computation */
            0
        );

        $event = new ContentCreateContentTypeChoiceLoaderEvent([], $this->createExampleLocation());

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber($listener);
        $eventDispatcher->dispatch($event, ContentCreateContentTypeChoiceLoaderEvent::RESOLVE_CONTENT_TYPES);

        self::assertEmpty($event->getContentTypeGroups());
    }

    public function testSkipSuggestionComputationIfTargetLocationIsMissing(): void
    {
        $this->enableSupportForAggregationAPI();
        $this->expectSuggestionsAreNotComputed();

        $event = new ContentCreateContentTypeChoiceLoaderEvent([], /* Target location is missing */ null);

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber($this->createDefaultListener());
        $eventDispatcher->dispatch($event, ContentCreateContentTypeChoiceLoaderEvent::RESOLVE_CONTENT_TYPES);

        self::assertEmpty($event->getContentTypeGroups());
    }

    public function testSuggestions(): void
    {
        $this->enableSupportForAggregationAPI();

        $article = $this->createExampleContentType('article');
        $folder = $this->createExampleContentType('folder');
        $image = $this->createExampleContentType('image');

        $results = new SearchResult();
        $results->aggregations = new AggregationResultCollection([
            'suggestions' => new TermAggregationResult(
                'suggestions',
                [
                    new TermAggregationResultEntry($article, 20),
                    new TermAggregationResultEntry($folder, 3),
                ]
            ),
        ]);

        $this->searchService->method('findLocations')->willReturn($results);

        $event = new ContentCreateContentTypeChoiceLoaderEvent(
            [
                'content_type_group_content' => [$article, $folder],
                'content_type_group_media' => [$image],
            ],
            $this->createExampleLocation()
        );

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber($this->createDefaultListener());
        $eventDispatcher->dispatch($event, ContentCreateContentTypeChoiceLoaderEvent::RESOLVE_CONTENT_TYPES);

        self::assertEquals([
            'content_type_suggestions' => [$article, $folder],
            'content_type_group_content' => [$article, $folder],
            'content_type_group_media' => [$image],
        ], $event->getContentTypeGroups());
    }

    private function disableSupportForAggregationAPI(): void
    {
        $this->searchService
            ->method('supports')
            ->with(SearchService::CAPABILITY_AGGREGATIONS)
            ->willReturn(false);
    }

    private function enableSupportForAggregationAPI(): void
    {
        $this->searchService
            ->method('supports')
            ->with(SearchService::CAPABILITY_AGGREGATIONS)
            ->willReturn(true);
    }

    private function createExampleLocation(int $id = 1): Location
    {
        $location = $this->createMock(Location::class);
        $location->method('__get')->with('id')->willReturn($id);

        return $location;
    }

    private function createExampleContentType(string $identifier): ContentType
    {
        $contentType = $this->createMock(ContentType::class);
        $contentType->method('__get')->with('identifier')->willReturn($identifier);

        return $contentType;
    }

    private function createDefaultListener(): ContentTypeSuggestionsListener
    {
        return new ContentTypeSuggestionsListener(
            $this->searchService,
            $this->translator,
        );
    }

    private function expectSuggestionsAreNotComputed(): void
    {
        $this->searchService->expects(self::never())->method('findLocations');
    }
}
