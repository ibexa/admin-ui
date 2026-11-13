<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\UI\Value;

use Ibexa\AdminUi\UI\Dataset\DatasetFactory;
use Ibexa\AdminUi\UI\Service\PathService;
use Ibexa\AdminUi\UI\Value\ValueFactory;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as APILocation;
use Ibexa\Contracts\Core\Repository\Values\Content\RelationList\Item\RelationListItem;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Ibexa\Core\Repository\LocationResolver\LocationResolver;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Core\Repository\Values\Content\Relation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\AdminUi\UI\Value\ValueFactory
 */
final class ValueFactoryTest extends TestCase
{
    /** @var LocationResolver&MockObject */
    private LocationResolver $locationResolver;

    private ValueFactory $valueFactory;

    protected function setUp(): void
    {
        $this->locationResolver = $this->createMock(LocationResolver::class);

        $this->valueFactory = new ValueFactory(
            $this->createMock(UserService::class),
            $this->createMock(LanguageService::class),
            $this->createMock(LocationService::class),
            $this->createMock(ContentTypeService::class),
            $this->createMock(SearchService::class),
            $this->createMock(ObjectStateService::class),
            $this->createMock(PermissionResolver::class),
            $this->createMock(PathService::class),
            $this->createMock(DatasetFactory::class),
            $this->createMock(UserLanguagePreferenceProviderInterface::class),
            $this->locationResolver
        );
    }

    /**
     * @return iterable<string, array{?int, ?int}>
     */
    public static function provideForCreateRelation(): iterable
    {
        yield 'source and destination with main Location' => [10, 20];
        // Content sent to Trash has no main Location
        yield 'destination in Trash' => [10, null];
        yield 'source in Trash' => [null, 20];
    }

    /**
     * @dataProvider provideForCreateRelation
     */
    public function testCreateRelationItem(
        ?int $sourceMainLocationId,
        ?int $destinationMainLocationId
    ): void {
        $this->expectLocationsResolved($sourceMainLocationId, $destinationMainLocationId);

        $relation = $this->valueFactory->createRelationItem(
            new RelationListItem($this->createRelation($sourceMainLocationId, $destinationMainLocationId)),
            $this->createContent()
        );

        self::assertSame($sourceMainLocationId, self::getLocationId($relation->getResolvedSourceLocation()));
        self::assertSame($destinationMainLocationId, self::getLocationId($relation->getResolvedDestinationLocation()));
    }

    /**
     * @dataProvider provideForCreateRelation
     */
    public function testCreateRelation(
        ?int $sourceMainLocationId,
        ?int $destinationMainLocationId
    ): void {
        $this->expectLocationsResolved($sourceMainLocationId, $destinationMainLocationId);

        $relation = $this->valueFactory->createRelation(
            $this->createRelation($sourceMainLocationId, $destinationMainLocationId),
            $this->createContent()
        );

        self::assertSame($sourceMainLocationId, self::getLocationId($relation->getResolvedSourceLocation()));
        self::assertSame($destinationMainLocationId, self::getLocationId($relation->getResolvedDestinationLocation()));
    }

    private function expectLocationsResolved(
        ?int $sourceMainLocationId,
        ?int $destinationMainLocationId
    ): void {
        // the viewed Content is always resolved, relation sides only when they have a main Location
        $expectedCount = 1 + count(array_filter([$sourceMainLocationId, $destinationMainLocationId]));

        $this->locationResolver
            ->expects(self::exactly($expectedCount))
            ->method('resolveLocation')
            ->willReturnCallback(static function (ContentInfo $contentInfo): Location {
                self::assertNotNull($contentInfo->getMainLocationId());

                return new Location(['id' => $contentInfo->getMainLocationId()]);
            });
    }

    private static function getLocationId(?APILocation $location): ?int
    {
        return $location !== null ? $location->getId() : null;
    }

    private function createRelation(
        ?int $sourceMainLocationId,
        ?int $destinationMainLocationId
    ): Relation {
        return new Relation([
            'type' => Relation::LINK,
            'sourceContentInfo' => new ContentInfo(['id' => 1, 'mainLocationId' => $sourceMainLocationId]),
            'destinationContentInfo' => new ContentInfo(['id' => 2, 'mainLocationId' => $destinationMainLocationId]),
        ]);
    }

    private function createContent(): Content
    {
        $contentType = $this->createMock(ContentType::class);
        $contentType
            ->expects(self::once())
            ->method('getName')
            ->willReturn('Article');

        $content = $this->createMock(Content::class);
        $content
            ->expects(self::once())
            ->method('getContentType')
            ->willReturn($contentType);
        $content
            ->expects(self::once())
            ->method('getName')
            ->willReturn('Linking article');
        $content
            ->expects(self::once())
            ->method('__get')
            ->with('contentInfo')
            ->willReturn(new ContentInfo(['id' => 3, 'mainLocationId' => 30]));

        return $content;
    }
}
