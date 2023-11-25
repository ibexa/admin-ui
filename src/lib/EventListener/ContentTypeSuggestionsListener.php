<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\EventListener;

use Ibexa\AdminUi\Form\Type\Event\ContentCreateContentTypeChoiceLoaderEvent;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\ContentTypeTermAggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ParentLocationId;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContentTypeSuggestionsListener implements EventSubscriberInterface
{
    private const SUGGESTIONS_AGGREGATION_KEY = 'suggestions';

    private SearchService $searchService;

    private TranslatorInterface $translator;

    private int $limit;

    public function __construct(SearchService $searchService, TranslatorInterface $translator, int $limit = 4)
    {
        $this->searchService = $searchService;
        $this->translator = $translator;
        $this->limit = $limit;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContentCreateContentTypeChoiceLoaderEvent::RESOLVE_CONTENT_TYPES => 'onResolveContentTypes',
        ];
    }

    public function onResolveContentTypes(ContentCreateContentTypeChoiceLoaderEvent $event): void
    {
        if ($this->limit < 1 || $event->getTargetLocation() === null) {
            return;
        }

        if (!$this->searchService->supports(SearchService::CAPABILITY_AGGREGATIONS)) {
            return;
        }

        $suggestions = $this->getSuggestions($event->getTargetLocation());
        if (!empty($suggestions)) {
            $suggestions = array_filter(
                $suggestions,
                fn (ContentType $contentType): bool => $this->isContentTypeAvailable(
                    $event->getContentTypeGroups(),
                    $contentType
                )
            );

            $event->setContentTypeGroups([
                $this->getSuggestionsGroupLabel() => $suggestions,
            ] + $event->getContentTypeGroups());
        }
    }

    /**
     * @param array<string, \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType[]> $contentTypes
     */
    private function isContentTypeAvailable(array $contentTypes, ContentType $needle): bool
    {
        foreach ($contentTypes as $group) {
            foreach ($group as $contentType) {
                if ($contentType->identifier === $needle->identifier) {
                    return true;
                }
            }
        }

        return false;
    }

    private function getSuggestionsGroupLabel(): string
    {
        return $this->translator->trans(
            /** @Desc("Suggestions") */
            'content_type_suggestions',
            [],
            'ibexa_content_create'
        );
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType[]
     */
    private function getSuggestions(Location $location): array
    {
        $aggregation = new ContentTypeTermAggregation(self::SUGGESTIONS_AGGREGATION_KEY);
        $aggregation->setLimit($this->limit);

        $query = new LocationQuery();
        $query->limit = 0;
        $query->filter = new ParentLocationId($location->id);
        $query->aggregations[] = $aggregation;
        $query->performCount = false;

        $results = $this->searchService->findLocations($query);

        if ($results->aggregations->has(self::SUGGESTIONS_AGGREGATION_KEY)) {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult\TermAggregationResult $aggregationResult */
            $aggregationResult = $results->aggregations->get(self::SUGGESTIONS_AGGREGATION_KEY);

            $suggestions = [];
            foreach ($aggregationResult->getEntries() as $entry) {
                $suggestions[] = $entry->getKey();
            }

            return $suggestions;
        }

        return [];
    }
}
