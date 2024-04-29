<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Form\DataMapper;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Form\Data\Policy\PolicyUpdateData;
use Ibexa\AdminUi\Form\DataMapper\PolicyUpdateMapper;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ContentTypeLimitation;
use Ibexa\Core\Repository\Values\User\PolicyUpdateStruct;
use PHPUnit\Framework\TestCase;

class PolicyUpdateMapperTest extends TestCase
{
    /** @var \Ibexa\AdminUi\Form\DataMapper\PolicyUpdateMapper */
    private $mapper;

    protected function setUp(): void
    {
        /* TODO - test skipped, because tested class need to be improved */
        self::markTestSkipped();
        $this->mapper = new PolicyUpdateMapper();
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
    public function testMap(array $properties)
    {
        $data = $this->mapper->map($this->createStruct($properties));

        self::assertEquals($this->createData($properties), $data);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param array $properties
     */
    public function testReverseMap(array $properties)
    {
        $struct = $this->mapper->reverseMap($this->createData($properties));

        self::assertEquals($this->createStruct($properties), $struct);
    }

    public function testMapWithWrongInstance()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument \'value\' is invalid: must be an instance of ' . PolicyUpdateStruct::class);

        $this->mapper->map(new LocationCreateStruct());
    }

    public function dataProvider(): array
    {
        return [
            'simple' => [['limitation' => new ContentTypeLimitation()]],
        ];
    }

    /**
     * @param array $properties
     *
     * @return \Ibexa\Core\Repository\Values\User\PolicyUpdateStruct
     */
    private function createStruct(array $properties): PolicyUpdateStruct
    {
        $struct = new PolicyUpdateStruct();
        $struct->addLimitation($properties['limitation']);

        return $struct;
    }

    /**
     * @param array $properties
     *
     * @return \Ibexa\AdminUi\Form\Data\Policy\PolicyUpdateData
     */
    private function createData(array $properties): PolicyUpdateData
    {
        return new PolicyUpdateData(['module' => $properties['module'], 'function' => $properties['function']]);
    }
}

class_alias(PolicyUpdateMapperTest::class, 'EzSystems\EzPlatformAdminUi\Tests\Form\DataMapper\PolicyUpdateMapperTest');
