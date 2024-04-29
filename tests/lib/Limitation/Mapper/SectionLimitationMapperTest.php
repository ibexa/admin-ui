<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\Mapper\SectionLimitationMapper;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\SectionLimitation;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SectionLimitationMapperTest extends TestCase
{
    private const EXAMPLE_SECTION_ID = 0xFF;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Ibexa\Contracts\Core\Repository\SectionService */
    private $sectionServiceMock;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var \Ibexa\AdminUi\Limitation\Mapper\SectionLimitationMapper */
    private $mapper;

    protected function setUp(): void
    {
        $this->sectionServiceMock = $this->createMock(SectionService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->mapper = new SectionLimitationMapper($this->sectionServiceMock);
        $this->mapper->setLogger($this->logger);
    }

    public function testMapLimitationValue()
    {
        $values = ['3', '5', '7'];

        $expected = [];
        foreach ($values as $i => $value) {
            $expected[$i] = new Section([
                'id' => $value,
            ]);

            $this->sectionServiceMock
                ->expects(self::at($i))
                ->method('loadSection')
                ->with($value)
                ->willReturn($expected[$i]);
        }

        $result = $this->mapper->mapLimitationValue(new SectionLimitation([
            'limitationValues' => $values,
        ]));

        self::assertEquals($expected, $result);
    }

    public function testMapLimitationValueWithNotExistingContentType()
    {
        $values = [self::EXAMPLE_SECTION_ID];

        $this->sectionServiceMock
            ->expects(self::once())
            ->method('loadSection')
            ->with($values[0])
            ->willThrowException($this->createMock(NotFoundException::class));

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with('Could not map the Limitation value: could not find a Section with ID ' . $values[0]);

        $actual = $this->mapper->mapLimitationValue(new SectionLimitation([
            'limitationValues' => $values,
        ]));

        self::assertEmpty($actual);
    }
}

class_alias(SectionLimitationMapperTest::class, 'EzSystems\EzPlatformAdminUi\Tests\Limitation\Mapper\SectionLimitationMapperTest');
