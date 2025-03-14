<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Util;

use Ibexa\AdminUi\Util\ContentTypeFieldsExpressionParser;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ContentTypeFieldsExpressionParserTest extends TestCase
{
    private ContentTypeFieldsExpressionParser $contentTypeFieldsExpressionExtractor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contentTypeFieldsExpressionExtractor = new ContentTypeFieldsExpressionParser();
    }

    /**
     * @param array{0: non-empty-list<string>|null, 1: non-empty-list<string>|null, 2: non-empty-list<string>|null} $expectedResult
     *
     * @dataProvider dataProviderForTestParse
     */
    public function testParse(string $expression, array $expectedResult): void
    {
        $result = $this->contentTypeFieldsExpressionExtractor->parseExpression($expression);

        self::assertSame($expectedResult, $result);
    }

    /**
     * @dataProvider dataProviderForTestParseInvalidExpressions
     */
    public function testParseInvalidExpression(string $expression): void
    {
        $this->expectException(RuntimeException::class);

        $this->contentTypeFieldsExpressionExtractor->parseExpression($expression);
    }

    /**
     * @return iterable<string, array{0: string, 1: array{0: non-empty-list<string>|null, 1: non-empty-list<string>|null, 2: non-empty-list<string>|null}}>
     */
    public function dataProviderForTestParse(): iterable
    {
        yield 'product content type group, every content type, few fields' => [
            'product/*/{name, description}',
            [
                ['product'],
                null,
                ['name', 'description'],
            ],
        ];

        yield 'product content type group, every content type, singular field' => [
            'product/*/name',
            [
                ['product'],
                null,
                ['name'],
            ],
        ];

        yield 'media content type group, file content type, singular field' => [
            'media/file/name',
            [
                ['media'],
                ['file'],
                ['name'],
            ],
        ];

        yield 'media content type group, file content type, few field' => [
            'media/file/{name,path}',
            [
                ['media'],
                ['file'],
                ['name', 'path'],
            ],
        ];

        yield 'file content type, few fields, without group' => [
            'file/{name, description}',
            [
                null,
                ['file'],
                ['name', 'description'],
            ],
        ];

        yield 'file content type, all fields, without group' => [
            'file/*',
            [
                null,
                ['file'],
                null,
            ],
        ];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public function dataProviderForTestParseInvalidExpressions(): iterable
    {
        yield 'file content type, without fields' => [
            'file/',
        ];

        yield 'file content type, without fields, two slashes' => [
            '/file/',
        ];

        yield 'file content type, two fields, starts with slash' => [
            '/file/{field1, field2}',
        ];

        yield 'file content type' => [
            'file',
        ];

        yield 'file content type, fields being identifier and wildcard' => [
            'file/{field1, *}',
        ];
    }
}
