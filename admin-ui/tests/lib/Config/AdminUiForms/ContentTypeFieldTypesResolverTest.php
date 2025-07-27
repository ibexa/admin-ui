<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Config\AdminUiForms;

use Ibexa\AdminUi\Config\AdminUiForms\ContentTypeFieldTypesResolver;
use Ibexa\AdminUi\Config\AdminUiForms\ContentTypeFieldTypesResolverInterface;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\AdminUi\Config\AdminUiForms\ContentTypeFieldTypesResolver
 */
final class ContentTypeFieldTypesResolverTest extends TestCase
{
    private const PARAM_NAME = 'admin_ui_forms.content_type_edit.field_types';

    private ContentTypeFieldTypesResolverInterface $contentTypeFieldTypesResolver;

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private ConfigResolverInterface $configResolver;

    protected function setUp(): void
    {
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->contentTypeFieldTypesResolver = new ContentTypeFieldTypesResolver($this->configResolver);
    }

    /**
     * @dataProvider provideDataForTestGetFieldTypes
     *
     * @param array<string, array{
     *     'position'?: int,
     *     'meta'?: bool,
     *  }> $expectedFieldTypes
     */
    public function testGetFieldTypes(bool $hasParameter, array $expectedFieldTypes): void
    {
        $this->mockConfigResolverHasParameter($hasParameter);
        $this->mockConfigResolverGetParameter($hasParameter, $expectedFieldTypes);

        self::assertEquals(
            $expectedFieldTypes,
            $this->contentTypeFieldTypesResolver->getFieldTypes()
        );
    }

    /**
     * @dataProvider provideDataForTestGetMetaFieldTypes
     *
     * @param array<string, array{
     *     'position'?: int,
     *     'meta'?: bool,
     *  }> $fieldTypes
     * @param array<string, array{
     *     'meta': bool,
     *     'position': int,
     *  }>
     * $expectedMetaFieldTypes
     */
    public function testGetMetaFieldTypes(
        bool $hasParameter,
        array $fieldTypes,
        array $expectedMetaFieldTypes
    ): void {
        $this->mockConfigResolverHasParameter($hasParameter);
        $this->mockConfigResolverGetParameter($hasParameter, $fieldTypes);

        self::assertEquals(
            $expectedMetaFieldTypes,
            $this->contentTypeFieldTypesResolver->getMetaFieldTypes()
        );
    }

    /**
     * @dataProvider provideDataForTestGetMetaFieldTypeIdentifiers
     *
     * @param array<string, array{
     *     'meta'?: bool,
     *     'position'?: int,
     *  }> $metaFieldTypes
     * @param array<string> $expectedIdentifiers
     */
    public function testGetMetaFieldTypeIdentifiers(
        bool $hasParameter,
        array $metaFieldTypes,
        array $expectedIdentifiers
    ): void {
        $this->mockConfigResolverHasParameter($hasParameter);
        $this->mockConfigResolverGetParameter($hasParameter, $metaFieldTypes);

        self::assertEquals(
            $expectedIdentifiers,
            $this->contentTypeFieldTypesResolver->getMetaFieldTypeIdentifiers()
        );
    }

    /**
     * @return iterable<array{
     *      bool,
     *      array<string, array{
     *          'meta'?: bool,
     *          'position'?: int,
     *      }>
     * }>
     */
    public function provideDataForTestGetFieldTypes(): iterable
    {
        yield [
            false,
            [],
        ];

        yield [
            true,
            [
                'foo' => [],
                'bar' => [
                    'meta' => true,
                    'position' => 2,
                ],
                'baz' => [
                    'meta' => false,
                    'position' => 10,
                ],
            ],
        ];
    }

    /**
     * @return iterable<array{
     *      bool,
     *      array<string, array{
     *          'meta'?: bool,
     *          'position'?: int,
     *      }>,
     *      array<string, array{
     *          'meta': bool,
     *          'position': int,
     *      }>
     * }>
     */
    public function provideDataForTestGetMetaFieldTypes(): iterable
    {
        yield [
            false,
            [],
            [],
        ];

        $foo = [
            'meta' => true,
            'position' => 2,
        ];

        $bar = [
            'meta' => true,
            'position' => 10,
        ];

        yield [
            true,
            [
                'bar' => $bar,
                'foo' => $foo,
                'baz' => [
                    'meta' => false,
                ],
            ],
            [
                'foo' => $foo,
                'bar' => $bar,
            ],
        ];
    }

    /**
     * @return iterable<array{
     *      bool,
     *      array<string, array{
     *          'meta': bool,
     *          'position': int,
     *      }>,
     *      array<string>
     * }>
     */
    public function provideDataForTestGetMetaFieldTypeIdentifiers(): iterable
    {
        yield [
            false,
            [],
            [],
        ];

        yield [
            true,
            [
                'foo' => [
                    'meta' => true,
                    'position' => 1,
                ],
                'bar' => [
                    'meta' => true,
                    'position' => 2,
                ],
            ],
            ['foo', 'bar'],
        ];
    }

    private function mockConfigResolverHasParameter(bool $hasParameter): void
    {
        $this->configResolver
            ->expects(self::once())
            ->method('hasParameter')
            ->with(self::PARAM_NAME)
            ->willReturn($hasParameter);
    }

    private function mockConfigResolverGetParameter(bool $hasParameter, array $configuredFieldTypes): void
    {
        if ($hasParameter) {
            $this->configResolver
                ->expects(self::once())
                ->method('getParameter')
                ->with(self::PARAM_NAME)
                ->willReturn($configuredFieldTypes);
        }
    }
}
