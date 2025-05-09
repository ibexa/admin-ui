<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\Mapper\ContentTypeLimitationMapper;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ContentTypeLimitation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ContentTypeLimitationMapperTest extends TestCase
{
    private const EXAMPLE_CONTENT_TYPE_ID_A = 1;
    private const EXAMPLE_CONTENT_TYPE_ID_B = 2;
    private const EXAMPLE_CONTENT_TYPE_ID_C = 3;

    private ContentTypeService&MockObject $contentTypeService;

    private LoggerInterface&MockObject $logger;

    private ContentTypeLimitationMapper $mapper;

    protected function setUp(): void
    {
        $this->contentTypeService = $this->createMock(ContentTypeService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->mapper = new ContentTypeLimitationMapper($this->contentTypeService);
        $this->mapper->setLogger($this->logger);
    }

    public function testMapLimitationValue(): void
    {
        $values = [
            self::EXAMPLE_CONTENT_TYPE_ID_A,
            self::EXAMPLE_CONTENT_TYPE_ID_B,
            self::EXAMPLE_CONTENT_TYPE_ID_C,
        ];

        $expected = [
            $this->createMock(ContentType::class),
            $this->createMock(ContentType::class),
            $this->createMock(ContentType::class),
        ];

        foreach ($values as $i => $value) {
            $this->contentTypeService
                ->expects(self::at($i))
                ->method('loadContentType')
                ->with($value)
                ->willReturn($expected[$i]);
        }

        $result = $this->mapper->mapLimitationValue(new ContentTypeLimitation([
            'limitationValues' => $values,
        ]));

        self::assertEquals($expected, $result);
        self::assertCount(3, $result);
    }

    public function testMapLimitationValueWithNotExistingContentType(): void
    {
        $this->contentTypeService
            ->expects(self::once())
            ->method('loadContentType')
            ->with(self::EXAMPLE_CONTENT_TYPE_ID_A)
            ->willThrowException($this->createMock(NotFoundException::class));

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with('Could not map the Limitation value: could not find a content type with ID ' . self::EXAMPLE_CONTENT_TYPE_ID_A);

        $actual = $this->mapper->mapLimitationValue(new ContentTypeLimitation([
            'limitationValues' => [self::EXAMPLE_CONTENT_TYPE_ID_A],
        ]));

        self::assertEmpty($actual);
    }
}
