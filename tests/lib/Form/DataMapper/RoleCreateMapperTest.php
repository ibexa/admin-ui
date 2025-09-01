<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\DataMapper;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Form\Data\Language\LanguageCreateData;
use Ibexa\AdminUi\Form\Data\Role\RoleCreateData;
use Ibexa\AdminUi\Form\DataMapper\RoleCreateMapper;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationCreateStruct;
use Ibexa\Core\Repository\Values\User\RoleCreateStruct;
use PHPUnit\Framework\TestCase;

final class RoleCreateMapperTest extends TestCase
{
    private RoleCreateMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new RoleCreateMapper();
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
        $this->expectExceptionMessage('Argument \'value\' is invalid: must be an instance of ' . RoleCreateStruct::class);

        $this->mapper->map(new LocationCreateStruct());
    }

    public function testReverseMapWithWrongInstance(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument \'data\' is invalid: must be an instance of ' . RoleCreateData::class);

        $this->mapper->reverseMap(new LanguageCreateData());
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public function dataProvider(): array
    {
        return [
            'simple' => [['identifier' => 'hash']],
        ];
    }

    /**
     * @param array<string, mixed> $properties
     */
    private function createStruct(array $properties): RoleCreateStruct
    {
        return new RoleCreateStruct($properties);
    }

    /**
     * @param array<string, mixed> $properties
     */
    private function createData(array $properties): RoleCreateData
    {
        return (new RoleCreateData())->setIdentifier($properties['identifier']);
    }
}
