<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\DataMapper;

use Ibexa\AdminUi\Exception\InvalidArgumentException;
use Ibexa\AdminUi\Form\Data\Language\LanguageCreateData;
use Ibexa\AdminUi\Form\Data\Section\SectionUpdateData;
use Ibexa\AdminUi\Form\DataMapper\SectionUpdateMapper;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use Ibexa\Contracts\Core\Repository\Values\Content\SectionUpdateStruct;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-type TSectionProperties array{identifier: string, name: string}
 */
final class SelectionUpdateMapperTest extends TestCase
{
    private SectionUpdateMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new SectionUpdateMapper();
    }

    protected function tearDown(): void
    {
        unset($this->mapper);
    }

    /**
     * @dataProvider dataProvider
     *
     * @phpstan-param TSectionProperties $properties
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function testMap(array $properties): void
    {
        $data = $this->mapper->map($this->createStruct($properties));

        self::assertEquals($this->createData($properties), $data);
    }

    /**
     * @dataProvider dataProvider
     *
     * @phpstan-param TSectionProperties $properties
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function testReverseMap(array $properties): void
    {
        $struct = $this->mapper->reverseMap($this->createData($properties));

        self::assertEquals($this->createStruct($properties), $struct);
    }

    public function testMapWithWrongInstance(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument \'value\' is invalid: must be an instance of ' . SectionUpdateStruct::class);

        $this->mapper->map(new LocationCreateStruct());
    }

    public function testReverseMapWithWrongInstance(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument \'data\' is invalid: must be an instance of ' . SectionUpdateData::class);

        $this->mapper->reverseMap(new LanguageCreateData());
    }

    /**
     * @phpstan-return iterable<string, array<TSectionProperties>>
     */
    public static function dataProvider(): iterable
    {
        yield 'simple' => [['identifier' => 'hash', 'name' => 'Lorem']];
        yield 'without_name' => [['identifier' => 'hash', 'name' => '']];
        yield 'without_identifier' => [['identifier' => '', 'name' => 'Lorem']];
        yield 'with_null' => [['identifier' => '', 'name' => '']];
    }

    /**
     * @phpstan-param TSectionProperties $properties
     */
    private function createStruct(array $properties): SectionUpdateStruct
    {
        return new SectionUpdateStruct($properties);
    }

    /**
     * @phpstan-param TSectionProperties $properties
     */
    private function createData(array $properties): SectionUpdateData
    {
        return new SectionUpdateData(new Section($properties));
    }
}
