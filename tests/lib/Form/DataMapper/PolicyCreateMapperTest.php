<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Form\DataMapper;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Form\Data\Policy\PolicyCreateData;
use Ibexa\AdminUi\Form\Data\Policy\PolicyUpdateData;
use Ibexa\AdminUi\Form\DataMapper\PolicyCreateMapper;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationCreateStruct;
use Ibexa\Core\Repository\Values\User\PolicyCreateStruct;
use PHPUnit\Framework\TestCase;

class PolicyCreateMapperTest extends TestCase
{
    /** @var \Ibexa\AdminUi\Form\DataMapper\PolicyCreateMapper */
    private PolicyCreateMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new PolicyCreateMapper();
    }

    protected function tearDown(): void
    {
        unset($this->mapper);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param array $properties
     */
    public function testMap(array $properties): void
    {
        $data = $this->mapper->map($this->createStruct($properties));

        self::assertEquals($this->createData($properties), $data);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param array $properties
     */
    public function testReverseMap(array $properties): void
    {
        $struct = $this->mapper->reverseMap($this->createData($properties));

        self::assertEquals($this->createStruct($properties), $struct);
    }

    public function testMapWithWrongInstance(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument \'value\' is invalid: must be an instance of ' . PolicyCreateStruct::class);

        $this->mapper->map(new LocationCreateStruct());
    }

    public function testReverseMapWithWrongInstance(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument \'data\' is invalid: must be an instance of ' . PolicyCreateData::class);

        $this->mapper->reverseMap(new PolicyUpdateData());
    }

    public function dataProvider(): array
    {
        return [
            'simple' => [['module' => 'module', 'function' => 'function']],
        ];
    }

    /**
     * @param array $properties
     *
     * @return \Ibexa\Core\Repository\Values\User\PolicyCreateStruct
     */
    private function createStruct(array $properties): PolicyCreateStruct
    {
        return new PolicyCreateStruct($properties);
    }

    /**
     * @param array $properties
     *
     * @return \Ibexa\AdminUi\Form\Data\Policy\PolicyCreateData
     */
    private function createData(array $properties): PolicyCreateData
    {
        return (new PolicyCreateData())
            ->setModule($properties['module'])
            ->setFunction($properties['function']);
    }
}
