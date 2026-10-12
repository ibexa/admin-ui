<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Util;

use Ibexa\AdminUi\Util\ContentTypeFieldsExpressionParser;
use Ibexa\AdminUi\Util\ContentTypeFieldsParsedStructure;
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
     * @dataProvider dataProviderForTestParse
     */
    public function testParse(
        string $expression,
        ContentTypeFieldsParsedStructure $expectedResult
    ): void {
        $result = $this->contentTypeFieldsExpressionExtractor->parseExpression($expression);

        self::assertSame($expectedResult->getGroups(), $result->getGroups());
        self::assertSame($expectedResult->getContentTypes(), $result->getContentTypes());
        self::assertSame($expectedResult->getFields(), $result->getFields());
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
     * @return iterable<string, array{string, ContentTypeFieldsParsedStructure}>
     */
    public function dataProviderForTestParse(): iterable
    {
        yield 'product content type group, every content type, few fields' => [
            'product/*/{name, description}',
            new ContentTypeFieldsParsedStructure(
                ['product'],
                null,
                ['name', 'description'],
            ),
        ];

        yield 'product content type group, every content type, singular field' => [
            'product/*/name',
            new ContentTypeFieldsParsedStructure(
                ['product'],
                null,
                ['name'],
            ),
        ];

        yield 'media content type group, file content type, singular field' => [
            'media/file/name',
            new ContentTypeFieldsParsedStructure(
                ['media'],
                ['file'],
                ['name'],
            ),
        ];

        yield 'media content type group, file content type, few field' => [
            'media/file/{name,path}',
            new ContentTypeFieldsParsedStructure(
                ['media'],
                ['file'],
                ['name', 'path'],
            ),
        ];

        yield 'file content type, few fields, without group' => [
            'file/{name, description}',
            new ContentTypeFieldsParsedStructure(
                null,
                ['file'],
                ['name', 'description'],
            ),
        ];

        yield 'file content type, all fields, without group' => [
            'file/*',
            new ContentTypeFieldsParsedStructure(
                null,
                ['file'],
                null,
            ),
        ];

        yield 'file content type duplicated, all fields, without group' => [
            '{file, file}/*',
            new ContentTypeFieldsParsedStructure(
                null,
                ['file'],
                null,
            ),
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

        yield 'empty {} list' => [
            'file/{}',
        ];

        yield 'missing opening {' => [
            'file/field1, field2}',
        ];

        yield 'missing closing {' => [
            'file/{field1, field2,',
        ];
    }
}
