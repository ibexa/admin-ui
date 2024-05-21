<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Form\DataMapper;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Form\Data\Language\LanguageCreateData;
use Ibexa\AdminUi\Form\Data\Language\LanguageDeleteData;
use Ibexa\AdminUi\Form\DataMapper\LanguageCreateMapper;
use Ibexa\Contracts\Core\Repository\Values\Content\LanguageCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationCreateStruct;
use PHPUnit\Framework\TestCase;

class LanguageCreateMapperTest extends TestCase
{
    /** @var \Ibexa\AdminUi\Form\DataMapper\LanguageCreateMapper */
    private $mapper;

    protected function setUp(): void
    {
        $this->mapper = new LanguageCreateMapper();
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
        $this->expectExceptionMessage('Argument \'value\' is invalid: must be an instance of ' . LanguageCreateStruct::class);

        $this->mapper->map(new LocationCreateStruct());
    }

    public function testReverseMapWithWrongInstance()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument \'data\' is invalid: must be an instance of ' . LanguageCreateData::class);

        $this->mapper->reverseMap(new LanguageDeleteData());
    }

    public function dataProvider()
    {
        return [
            'enabled_true' => [['languageCode' => 'AB', 'name' => 'Lorem', 'enabled' => true]],
            'enabled_false' => [['languageCode' => 'CD', 'name' => 'Ipsum', 'enabled' => false]],
        ];
    }

    /**
     * @param array $properties
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\LanguageCreateStruct
     */
    private function createStruct(array $properties): LanguageCreateStruct
    {
        return new LanguageCreateStruct($properties);
    }

    /**
     * @param array $properties
     *
     * @return \Ibexa\AdminUi\Form\Data\Language\LanguageCreateData
     */
    private function createData(array $properties): LanguageCreateData
    {
        return (new LanguageCreateData())
            ->setLanguageCode($properties['languageCode'])
            ->setName($properties['name'])
            ->setEnabled($properties['enabled']);
    }
}
