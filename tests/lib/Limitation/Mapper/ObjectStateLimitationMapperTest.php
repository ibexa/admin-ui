<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\Mapper\ObjectStateLimitationMapper;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ObjectStateLimitation;
use Ibexa\Core\Repository\Values\ObjectState\ObjectState;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ObjectStateLimitationMapperTest extends TestCase
{
    private const EXAMPLE_OBJECT_STATE_ID_A = 1;
    private const EXAMPLE_OBJECT_STATE_ID_B = 2;
    private const EXAMPLE_OBJECT_STATE_ID_C = 3;

    /** @var \Ibexa\Contracts\Core\Repository\ObjectStateService|\PHPUnit\Framework\MockObject\MockObject */
    private MockObject $objectStateService;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private MockObject $logger;

    /** @var \Ibexa\AdminUi\Limitation\Mapper\ObjectStateLimitationMapper */
    private ObjectStateLimitationMapper $mapper;

    protected function setUp(): void
    {
        $this->objectStateService = $this->createMock(ObjectStateService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->mapper = new ObjectStateLimitationMapper($this->objectStateService);
        $this->mapper->setLogger($this->logger);
    }

    public function testMapLimitationValue(): void
    {
        $values = [
            self::EXAMPLE_OBJECT_STATE_ID_A,
            self::EXAMPLE_OBJECT_STATE_ID_B,
            self::EXAMPLE_OBJECT_STATE_ID_C,
        ];

        $expected = [
            $this->createStateMock('foo'),
            $this->createStateMock('bar'),
            $this->createStateMock('baz'),
        ];

        foreach ($values as $i => $value) {
            $this->objectStateService
                ->expects(self::at($i))
                ->method('loadObjectState')
                ->with($value)
                ->willReturn($expected[$i]);
        }

        $result = $this->mapper->mapLimitationValue(new ObjectStateLimitation([
            'limitationValues' => $values,
        ]));

        self::assertEquals([
            'foo:foo', 'bar:bar', 'baz:baz',
        ], $result);
    }

    public function testMapLimitationValueWithNotExistingObjectState(): void
    {
        $this->objectStateService
            ->expects(self::once())
            ->method('loadObjectState')
            ->with(self::EXAMPLE_OBJECT_STATE_ID_A)
            ->willThrowException($this->createMock(NotFoundException::class));

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with('Could not map the Limitation value: could not find an Object state with ID ' . self::EXAMPLE_OBJECT_STATE_ID_A);

        $actual = $this->mapper->mapLimitationValue(new ObjectStateLimitation([
            'limitationValues' => [self::EXAMPLE_OBJECT_STATE_ID_A],
        ]));

        self::assertEmpty($actual);
    }

    private function createStateMock(string $value): MockObject
    {
        $stateGroupMock = $this->createMock(ObjectStateGroup::class);
        $stateGroupMock
            ->expects(self::once())
            ->method('getName')
            ->willReturn($value);

        $stateMock = $this->createMock(ObjectState::class);
        $stateMock
            ->expects(self::any())
            ->method('getObjectStateGroup')
            ->willReturn($stateGroupMock);

        $stateMock
            ->expects(self::any())
            ->method('getName')
            ->willReturn($value);

        return $stateMock;
    }
}
