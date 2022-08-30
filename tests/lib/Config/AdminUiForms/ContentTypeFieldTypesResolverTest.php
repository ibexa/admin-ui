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
    private const PARAM_NAME = 'admin_ui_forms.content_type_edit.fieldtypes';
    private const SCOPE = 'admin_group';

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
     * @param array{
     *      bool,
     *      array<string, array{
     *          'meta': bool
     *      }>
     * } $expectedFieldTypes
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
     * @return iterable<array{
     *      bool,
     *      array<string, array{
     *          'meta': bool
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
                'foo',
                'bar' => [
                    'meta' => true,
                ],
                'baz' => [
                    'meta' => false,
                ],
            ],
        ];
    }

    private function mockConfigResolverHasParameter(bool $hasParameter): void
    {
        $this->configResolver
            ->expects(self::once())
            ->method('hasParameter')
            ->with(
                self::PARAM_NAME,
                null,
                self::SCOPE
            )
            ->willReturn($hasParameter);
    }

    private function mockConfigResolverGetParameter(bool $hasParameter, array $configuredFieldTypes): void
    {
        if ($hasParameter) {
            $this->configResolver
                ->expects(self::once())
                ->method('getParameter')
                ->with(
                    self::PARAM_NAME,
                    null,
                    self::SCOPE
                )
                ->willReturn($configuredFieldTypes);
        }
    }
}
