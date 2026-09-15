<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Form\Data\FormMapper;

use Ibexa\AdminUi\Form\Data\ContentTranslationData;
use Ibexa\AdminUi\Form\Data\FormMapper\ContentTranslationMapper;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\FieldType\Value;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType as ApiContentType;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

final class ContentTranslationMapperTest extends TestCase
{
    public const string LANGUAGE_CODE = 'cyb-CY';

    private ContentTranslationMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ContentTranslationMapper();
    }

    /**
     * @param array<string, mixed> $params
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('paramsProvider')]
    public function testMapToFormData(Content $content, array $params, ContentTranslationData $expectedData): void
    {
        $actualData = $this->mapper->mapToFormData($content, $params);

        self::assertEquals($expectedData, $actualData);
    }

    /**
     * @return array<string, array{
     *     \Ibexa\Core\Repository\Values\Content\Content,
     *     array{
     *       language: \Ibexa\Contracts\Core\Repository\Values\Content\Language,
     *       contentType: ApiContentType,
     *       baseLanguage: \Ibexa\Contracts\Core\Repository\Values\Content\Language|null
     *     },
     *     \Ibexa\AdminUi\Form\Data\ContentTranslationData
     * }>
     */
    public static function paramsProvider(): array
    {
        $language = new Language(['languageCode' => self::LANGUAGE_CODE]);

        $field1 = self::getField();
        $field2 = self::getField('identifier2');
        $field3 = self::getField('identifier3');

        $content_with_1_field = self::getCompleteContent([$field1]);
        $content_with_3_fields = self::getCompleteContent([$field1, $field2, $field3]);

        $contentTypeTranslatable = self::getContentType([
            self::getFieldDefinition($field1->fieldDefIdentifier, true),
        ]);
        $contentType = self::getContentType([
            self::getFieldDefinition(),
        ]);
        $contentTypeThreeFields = self::getContentType([
            self::getFieldDefinition($field1->fieldDefIdentifier),
            self::getFieldDefinition($field2->fieldDefIdentifier),
            self::getFieldDefinition($field3->fieldDefIdentifier),
        ]);

        return [
            'no_base_language' => [
                $content_with_1_field,
                [
                    'language' => $language,
                    'contentType' => $contentTypeTranslatable,
                    'baseLanguage' => null,
                ],
                new ContentTranslationData([
                    'content' => $content_with_1_field,
                    'initialLanguageCode' => self::LANGUAGE_CODE,
                    'fieldsData' => [
                        $field1->fieldDefIdentifier => new FieldData([
                            'field' => $field1,
                            'fieldDefinition' => self::getFieldDefinition($field1->fieldDefIdentifier, true),
                            'value' => self::createStub(Value::class),
                        ]),
                    ],
                    'contentType' => $contentTypeTranslatable,
                ]),
            ],
            'one_field' => [
                $content_with_1_field,
                [
                    'language' => $language,
                    'contentType' => $contentType,
                    'baseLanguage' => $language,
                ],
                new ContentTranslationData([
                    'content' => $content_with_1_field,
                    'initialLanguageCode' => self::LANGUAGE_CODE,
                    'fieldsData' => [
                        $field1->fieldDefIdentifier => new FieldData([
                            'field' => $field1,
                            'fieldDefinition' => self::getFieldDefinition(),
                            'value' => self::createStub(Value::class),
                        ]),
                    ],
                    'contentType' => $contentType,
                ]),
            ],
            'tree_fields' => [
                $content_with_3_fields,
                [
                    'language' => $language,
                    'contentType' => $contentTypeThreeFields,
                    'baseLanguage' => $language,
                ],
                new ContentTranslationData([
                    'content' => $content_with_3_fields,
                    'initialLanguageCode' => self::LANGUAGE_CODE,
                    'fieldsData' => [
                        $field1->fieldDefIdentifier => new FieldData([
                            'field' => $field1,
                            'fieldDefinition' => self::getFieldDefinition($field1->fieldDefIdentifier),
                            'value' => self::createStub(Value::class),
                        ]),
                        $field2->fieldDefIdentifier => new FieldData([
                            'field' => $field2,
                            'fieldDefinition' => self::getFieldDefinition($field2->fieldDefIdentifier),
                            'value' => self::createStub(Value::class),
                        ]),
                        $field3->fieldDefIdentifier => new FieldData([
                            'field' => $field3,
                            'fieldDefinition' => self::getFieldDefinition($field3->fieldDefIdentifier),
                            'value' => self::createStub(Value::class),
                        ]),
                    ],
                    'contentType' => $contentTypeThreeFields,
                ]),
            ],
        ];
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $exception
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('wrongParamsProvider')]
    public function testMapToFormDataWithoutRequiredParameter(Content $content, array $params, array $exception): void
    {
        $this->expectException($exception['class']);
        $this->expectExceptionMessage($exception['message']);

        $this->mapper->mapToFormData($content, $params);
    }

    /**
     * @return array<string, array{
     *     \Ibexa\Core\Repository\Values\Content\Content,
     *     array<string, mixed>,
     *     array{class: class-string<\Throwable>, message: string}
     * }>
     */
    public static function wrongParamsProvider(): array
    {
        return [
            'missing_language' => [
                self::getCompleteContent(),
                [
                    'contentType' => 'contentType',
                    'baseLanguage' => 'baseLanguage',
                ],
                [
                    'class' => MissingOptionsException::class,
                    'message' => 'The required option "language" is missing.',
                ],
            ],
            'missing_content_type' => [
                self::getCompleteContent(),
                [
                    'language' => 'language',
                    'baseLanguage' => null,
                ],
                [
                    'class' => MissingOptionsException::class,
                    'message' => 'The required option "contentType" is missing.',
                ],
            ],
            'wrong_type_of_language' => [
                self::getCompleteContent(),
                [
                    'language' => 'language',
                    'contentType' => new ContentType(),
                    'baseLanguage' => null,
                ],
                [
                    'class' => InvalidOptionsException::class,
                    'message' => sprintf('The option "language" with value "language" is expected to be of type "%s", but is of type "string".', Language::class),
                ],
            ],
            'wrong_type_of_content_type' => [
                self::getCompleteContent(),
                [
                    'language' => new Language(),
                    'contentType' => 'content_type',
                    'baseLanguage' => null,
                ],
                [
                    'class' => InvalidOptionsException::class,
                    'message' => sprintf('The option "contentType" with value "content_type" is expected to be of type "%s", but is of type "string".', ApiContentType::class),
                ],
            ],
            'wrong_type_of_base_language' => [
                self::getCompleteContent(),
                [
                    'language' => new Language(),
                    'contentType' => new ContentType(),
                    'baseLanguage' => 'base_language',
                ],
                [
                    'class' => InvalidOptionsException::class,
                    'message' => sprintf('The option "baseLanguage" with value "base_language" is expected to be of type "null" or "%s", but is of type "string".', Language::class),
                ],
            ],
        ];
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Field[] $fields
     */
    private static function getCompleteContent(array $fields = []): Content
    {
        return new Content([
            'internalFields' => $fields,
            'versionInfo' => new VersionInfo([
                'contentInfo' => new ContentInfo(['mainLanguageCode' => self::LANGUAGE_CODE]),
            ]),
        ]);
    }

    private static function getField(string $fieldDefIdentifier = 'identifier', string $languageCode = self::LANGUAGE_CODE): Field
    {
        return new Field([
            'fieldDefIdentifier' => $fieldDefIdentifier,
            'languageCode' => $languageCode,
            'value' => self::createStub(Value::class),
        ]);
    }

    /**
     * @param array<\Ibexa\Core\Repository\Values\ContentType\FieldDefinition> $fieldDefs
     */
    private static function getContentType(array $fieldDefs = []): ContentType
    {
        return new ContentType([
            'fieldDefinitions' => new FieldDefinitionCollection($fieldDefs),
        ]);
    }

    private static function getFieldDefinition(string $identifier = 'identifier', bool $isTranslatable = false): FieldDefinition
    {
        return new FieldDefinition([
            'identifier' => $identifier,
            'defaultValue' => self::createStub(Value::class),
            'isTranslatable' => $isTranslatable,
        ]);
    }
}
