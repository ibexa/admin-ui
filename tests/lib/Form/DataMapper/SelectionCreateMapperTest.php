<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Form\DataMapper;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Form\Data\Language\LanguageCreateData;
use Ibexa\AdminUi\Form\Data\Section\SectionCreateData;
use Ibexa\AdminUi\Form\DataMapper\SectionCreateMapper;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\Content\SectionCreateStruct;
use PHPUnit\Framework\TestCase;

class SelectionCreateMapperTest extends TestCase
{
    /** @var \Ibexa\AdminUi\Form\DataMapper\SectionCreateMapper */
    private $mapper;

    protected function setUp(): void
    {
        $this->mapper = new SectionCreateMapper();
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
        $this->expectExceptionMessage('Argument \'value\' is invalid: must be an instance of ' . SectionCreateStruct::class);

        $this->mapper->map(new LocationCreateStruct());
    }

    public function testReverseMapWithWrongInstance()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument \'data\' is invalid: must be an instance of ' . SectionCreateData::class);

        $this->mapper->reverseMap(new LanguageCreateData());
    }

    public function dataProvider(): array
    {
        return [
            'simple' => [['identifier' => 'hash', 'name' => 'Lorem']],
            'without_name' => [['identifier' => 'hash', 'name' => null]],
            'without_identifier' => [['identifier' => null, 'name' => 'Lorem']],
            'with_null' => [['identifier' => null, 'name' => null]],
        ];
    }

    /**
     * @param array $properties
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\SectionCreateStruct
     */
    private function createStruct(array $properties): SectionCreateStruct
    {
        return new SectionCreateStruct($properties);
    }

    /**
     * @param array $properties
     *
     * @return \Ibexa\AdminUi\Form\Data\Section\SectionCreateData
     */
    private function createData(array $properties): SectionCreateData
    {
        return new SectionCreateData($properties['identifier'], $properties['name']);
    }
}

class_alias(SelectionCreateMapperTest::class, 'EzSystems\EzPlatformAdminUi\Tests\Form\DataMapper\SelectionCreateMapperTest');
