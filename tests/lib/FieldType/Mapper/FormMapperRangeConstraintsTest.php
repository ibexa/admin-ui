<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\FieldType\Mapper;

use Ibexa\AdminUi\FieldType\FieldDefinitionFormMapperInterface;
use Ibexa\AdminUi\FieldType\Mapper\BinaryFileFormMapper;
use Ibexa\AdminUi\FieldType\Mapper\ImageFormMapper;
use Ibexa\AdminUi\FieldType\Mapper\MediaFormMapper;
use Ibexa\AdminUi\FieldType\Mapper\UserAccountFormMapper;
use Ibexa\AdminUi\Form\Data\ContentTypeData;
use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\ContentForms\ConfigResolver\MaxUploadSize;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\ContentTypeDraft;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Symfony 8 no longer accepts an array of options in constraint constructors,
 * so the Range constraints built by the mappers must be created with named arguments.
 */
final class FormMapperRangeConstraintsTest extends TestCase
{
    /**
     * @return iterable<string, array{
     *     \Ibexa\AdminUi\FieldType\FieldDefinitionFormMapperInterface,
     *     array<string, array{int|null, int|null}>
     * }>
     */
    public static function provideMappers(): iterable
    {
        $maxSize = (new MaxUploadSize())->get(MaxUploadSize::MEGABYTES);

        yield 'binaryfile' => [
            new BinaryFileFormMapper(new MaxUploadSize()),
            ['maxSize' => [0, $maxSize]],
        ];

        yield 'image' => [
            new ImageFormMapper([], new MaxUploadSize(), self::createStub(MimeTypesInterface::class)),
            ['maxSize' => [0, $maxSize]],
        ];

        yield 'media' => [
            new MediaFormMapper(new MaxUploadSize()),
            ['maxSize' => [0, $maxSize]],
        ];

        yield 'user account' => [
            new UserAccountFormMapper(),
            [
                'minLength' => [0, 255],
                'passwordTTL' => [0, null],
                'passwordTTLWarning' => [0, null],
            ],
        ];
    }

    /**
     * @param array<string, array{int|null, int|null}> $expectedRanges field name => [min, max]
     */
    #[DataProvider('provideMappers')]
    public function testMapperAddsRangeConstraints(
        FieldDefinitionFormMapperInterface $mapper,
        array $expectedRanges
    ): void {
        $ranges = [];

        $form = $this->createMock(FormInterface::class);
        $form
            ->method('add')
            ->willReturnCallback(static function (string $child, ?string $type = null, array $options = []) use (&$ranges, $form): FormInterface {
                foreach ($options['constraints'] ?? [] as $constraint) {
                    if ($constraint instanceof Range) {
                        $ranges[$child] = [$constraint->min, $constraint->max];
                    }
                }

                return $form;
            });

        $mapper->mapFieldDefinitionForm($form, $this->createFieldDefinitionData());

        self::assertSame($expectedRanges, $ranges);
    }

    private function createFieldDefinitionData(): FieldDefinitionData
    {
        $contentTypeData = new ContentTypeData([
            'contentTypeDraft' => new ContentTypeDraft([
                'innerContentType' => new ContentType(['identifier' => 'foo']),
            ]),
        ]);
        $contentTypeData->languageCode = 'eng-GB';
        $contentTypeData->mainLanguageCode = 'eng-GB';

        return new FieldDefinitionData(['contentTypeData' => $contentTypeData]);
    }
}
