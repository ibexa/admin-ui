<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\AdminUi\Form\DataMapper;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Form\Data\Language\LanguageCreateData;
use Ibexa\AdminUi\Form\Data\Role\RoleUpdateData;
use Ibexa\AdminUi\Form\DataMapper\RoleUpdateMapper;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\User\RoleUpdateStruct;
use PHPUnit\Framework\TestCase;

class RoleUpdateMapperTest extends TestCase
{
    /** @var \Ibexa\AdminUi\Form\DataMapper\RoleUpdateMapper */
    private $mapper;

    protected function setUp(): void
    {
        $this->mapper = new RoleUpdateMapper();
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

        $this->assertEquals($this->createData($properties), $data);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param array $properties
     */
    public function testReverseMap(array $properties)
    {
        $struct = $this->mapper->reverseMap($this->createData($properties));

        $this->assertEquals($this->createStruct($properties), $struct);
    }

    public function testMapWithWrongInstance()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument \'value\' is invalid: must be an instance of ' . RoleUpdateStruct::class);

        $this->mapper->map(new LocationCreateStruct());
    }

    public function testReverseMapWithWrongInstance()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument \'data\' is invalid: must be an instance of ' . RoleUpdateData::class);

        $this->mapper->reverseMap(new LanguageCreateData());
    }

    public function dataProvider(): array
    {
        return [
            'simple' => [['identifier' => 'hash']],
        ];
    }

    /**
     * @param array $properties
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\RoleUpdateStruct
     */
    private function createStruct(array $properties): RoleUpdateStruct
    {
        return new RoleUpdateStruct($properties);
    }

    /**
     * @param array $properties
     *
     * @return \Ibexa\AdminUi\Form\Data\Role\RoleUpdateData
     */
    private function createData(array $properties): RoleUpdateData
    {
        return (new RoleUpdateData())
            ->setIdentifier($properties['identifier']);
    }
}

class_alias(RoleUpdateMapperTest::class, 'EzSystems\EzPlatformAdminUi\Tests\Form\DataMapper\RoleUpdateMapperTest');
