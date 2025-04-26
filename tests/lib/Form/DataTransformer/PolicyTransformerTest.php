<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\DataTransformer;

use Ibexa\AdminUi\Form\DataTransformer\PolicyTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PolicyTransformerTest extends TestCase
{
    /**
     * @dataProvider transformDataProvider
     */
    public function testTransform(mixed $value, ?string $expected): void
    {
        $transformer = new PolicyTransformer();

        $result = $transformer->transform($value);

        self::assertEquals($expected, $result);
    }

    /**
     * @dataProvider transformWithInvalidInputDataProvider
     */
    public function testTransformWithInvalidInput(mixed $value): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a valid array of data.');

        $transformer = new PolicyTransformer();

        $transformer->transform($value);
    }

    /**
     * @dataProvider reverseTransformDataProvider
     *
     * @phpstan-param array{id: int, module: string, function: string}|null $expected
     */
    public function testReverseTransform(?string $value, ?array $expected): void
    {
        $transformer = new PolicyTransformer();
        $result = $transformer->reverseTransform($value);

        self::assertEquals($expected, $result);
    }

    /**
     * @dataProvider reverseTransformWithInvalidInputDataProvider
     */
    public function testReverseTransformWithInvalidInput(mixed $value, string $expectedMessage): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage($expectedMessage);

        $transformer = new PolicyTransformer();

        $transformer->reverseTransform($value);
    }

    /**
     * @return array<string, array{mixed, string|null}>
     */
    public function transformDataProvider(): array
    {
        return [
            'policy' => [
                ['id' => 123456, 'module' => 'module_name', 'function' => 'some_function'],
                '123456:module_name:some_function',
            ],
            'null' => [null, null],
        ];
    }

    /**
     * @return array<string, array{string|null, array{id: int, module: string, function: string}|null}>
     */
    public function reverseTransformDataProvider(): array
    {
        return [
            'string' => ['123456:module:function', ['id' => 123456, 'module' => 'module', 'function' => 'function']],
            'null' => [null, null],
        ];
    }

    /**
     * @return array<string, array{mixed}>
     */
    public function transformWithInvalidInputDataProvider(): array
    {
        return [
            'integer' => [123456],
            'bool' => [true],
            'float' => [12.34],
            'empty_array' => [[]],
            'object' => [new \stdClass()],
            'string' => ['some string'],
            'empty_string' => [''],
            'missing_id' => [['module' => 'module_name', 'function' => 'some_function']],
            'missing_module' => [['id' => 123456, 'function' => 'some_function']],
            'missing_function' => [['id' => 123456, 'module' => 'module_name']],
        ];
    }

    /**
     * @return array<string, array{mixed, string}>
     */
    public function reverseTransformWithInvalidInputDataProvider(): array
    {
        $stringExpected = 'Expected a string.';
        $atLeast3Parts = 'Policy string must contain at least 3 parts.';

        return [
            'integer' => [123456, $stringExpected],
            'bool' => [true, $stringExpected],
            'float' => [12.34, $stringExpected],
            'array' => [[], $stringExpected],
            'object' => [new \stdClass(), $stringExpected],
            '2_parts' => ['123456:module', $atLeast3Parts],
            '1_part' => ['123456', $atLeast3Parts],
            'empty_string' => ['', $atLeast3Parts],
        ];
    }
}
