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
    public const LANGUAGE_CODE = 'cyb-CY';

    /** @var \Ibexa\AdminUi\Form\Data\FormMapper\ContentTranslationMapper */
    private $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ContentTranslationMapper();
    }

    /**
     * @dataProvider paramsProvider
     */
    public function testMapToFormData(Content $content, array $params, ContentTranslationData $expectedData)
    {
        $actualData = $this->mapper->mapToFormData($content, $params);

        self::assertEquals($expectedData, $actualData);
    }

    public function paramsProvider(): array
    {
        $language = new Language(['languageCode' => self::LANGUAGE_CODE]);

        $field1 = $this->getField();
        $field2 = $this->getField('identifier2');
        $field3 = $this->getField('identifier3');

        $content_with_1_field = $this->getCompleteContent([$field1]);
        $content_with_3_fields = $this->getCompleteContent([$field1, $field2, $field3]);

        $contentTypeTranslatable = $this->getContentType([
            $this->getFieldDefinition($field1->fieldDefIdentifier, true),
        ]);
        $contentType = $this->getContentType([
            $this->getFieldDefinition(),
        ]);
        $contentTypeThreeFields = $this->getContentType([
            $this->getFieldDefinition($field1->fieldDefIdentifier),
            $this->getFieldDefinition($field2->fieldDefIdentifier),
            $this->getFieldDefinition($field3->fieldDefIdentifier),
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
                            'fieldDefinition' => $this->getFieldDefinition($field1->fieldDefIdentifier, true),
                            'value' => $this->createMock(Value::class),
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
                            'fieldDefinition' => $this->getFieldDefinition(),
                            'value' => $this->createMock(Value::class),
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
                            'fieldDefinition' => $this->getFieldDefinition($field1->fieldDefIdentifier),
                            'value' => $this->createMock(Value::class),
                        ]),
                        $field2->fieldDefIdentifier => new FieldData([
                            'field' => $field2,
                            'fieldDefinition' => $this->getFieldDefinition($field2->fieldDefIdentifier),
                            'value' => $this->createMock(Value::class),
                        ]),
                        $field3->fieldDefIdentifier => new FieldData([
                            'field' => $field3,
                            'fieldDefinition' => $this->getFieldDefinition($field3->fieldDefIdentifier),
                            'value' => $this->createMock(Value::class),
                        ]),
                    ],
                    'contentType' => $contentTypeThreeFields,
                ]),
            ],
        ];
    }

    /**
     * @dataProvider wrongParamsProvider
     */
    public function testMapToFormDataWithoutRequiredParameter($content, array $params, array $exception)
    {
        $this->expectException($exception['class']);
        $this->expectExceptionMessage($exception['message']);

        $this->mapper->mapToFormData($content, $params);
    }

    public function wrongParamsProvider(): array
    {
        return [
            'missing_language' => [
                $this->getCompleteContent(),
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
                $this->getCompleteContent(),
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
                $this->getCompleteContent(),
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
                $this->getCompleteContent(),
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
                $this->getCompleteContent(),
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

    private function getCompleteContent(array $fields = []): Content
    {
        return new Content([
            'internalFields' => $fields,
            'versionInfo' => new VersionInfo([
                'contentInfo' => new ContentInfo(['mainLanguageCode' => self::LANGUAGE_CODE]),
            ]),
        ]);
    }

    private function getField($fieldDefIdentifier = 'identifier', $languageCode = self::LANGUAGE_CODE, $value = 'string_value'): Field
    {
        return new Field([
            'fieldDefIdentifier' => $fieldDefIdentifier,
            'languageCode' => $languageCode,
            'value' => $this->createMock(Value::class),
        ]);
    }

    private function getContentType(array $fieldDefs = []): ContentType
    {
        return new ContentType([
            'fieldDefinitions' => new FieldDefinitionCollection($fieldDefs),
        ]);
    }

    private function getFieldDefinition(string $identifier = 'identifier', bool $isTranslatable = false): FieldDefinition
    {
        return new FieldDefinition([
            'identifier' => $identifier,
            'defaultValue' => $this->createMock(Value::class),
            'isTranslatable' => $isTranslatable,
        ]);
    }
}
