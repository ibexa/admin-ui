<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Validator\Constraint;

use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\AdminUi\Validator\Constraints\FieldDefinitionDefaultValue;
use Ibexa\AdminUi\Validator\Constraints\FieldDefinitionDefaultValueValidator;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Core\FieldType\Country\Type as CountryType;
use Ibexa\Core\FieldType\Country\Value as CountryValue;
use Ibexa\Core\FieldType\Integer\Type as IntegerType;
use Ibexa\Core\FieldType\Integer\Value as IntegerValue;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Repository\Values\ContentType\FieldType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @covers \Ibexa\AdminUi\Validator\Constraints\FieldDefinitionDefaultValueValidator
 */
final class FieldDefinitionDefaultValueValidatorTest extends TestCase
{
    private const COUNTRIES_INFO = [
        'BE' => ['Name' => 'Belgium', 'Alpha2' => 'BE', 'Alpha3' => 'BEL', 'IDC' => 32],
        'FR' => ['Name' => 'France', 'Alpha2' => 'FR', 'Alpha3' => 'FRA', 'IDC' => 33],
    ];

    private const INTEGER_DEFAULT_VALUE = 50;

    /** @var ExecutionContextInterface&MockObject */
    private ExecutionContextInterface $executionContext;

    private FieldDefinitionDefaultValueValidator $validator;

    protected function setUp(): void
    {
        $countryType = new CountryType(self::COUNTRIES_INFO);
        $integerType = new IntegerType();

        $fieldTypeService = $this->createStub(FieldTypeService::class);
        $fieldTypeService
            ->method('getFieldType')
            ->willReturnMap([
                [$countryType->getFieldTypeIdentifier(), new FieldType($countryType)],
                [$integerType->getFieldTypeIdentifier(), new FieldType($integerType)],
            ]);

        $this->executionContext = $this->createMock(ExecutionContextInterface::class);
        $this->validator = new FieldDefinitionDefaultValueValidator($fieldTypeService);
        $this->validator->initialize($this->executionContext);
    }

    /**
     * @dataProvider provideDefaultValuesAllowedBySubmittedData
     */
    public function testValidatesDefaultValueAgainstSubmittedData(FieldDefinitionData $fieldDefinitionData): void
    {
        $this->executionContext
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate($fieldDefinitionData, new FieldDefinitionDefaultValue());
    }

    /**
     * @return iterable<string, array{FieldDefinitionData}>
     */
    public static function provideDefaultValuesAllowedBySubmittedData(): iterable
    {
        yield 'country with multiple choice enabled in the same submit' => [
            self::createCountryFieldDefinitionData(false, true),
        ];

        yield 'integer with maximum value raised in the same submit' => [
            self::createIntegerFieldDefinitionData(10, 100),
        ];

        yield 'country with field settings not submitted and stored ones allowing multiple countries' => [
            self::createCountryFieldDefinitionData(true, null),
        ];

        yield 'integer with validator configuration not submitted and stored one allowing the value' => [
            self::createIntegerFieldDefinitionData(100, null),
        ];
    }

    /**
     * @dataProvider provideDefaultValuesNotAllowedBySubmittedData
     */
    public function testAddsViolationWhenSubmittedDataDoesNotAllowDefaultValue(FieldDefinitionData $fieldDefinitionData): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->method('setParameters')->willReturnSelf();
        $violationBuilder
            ->expects(self::once())
            ->method('atPath')
            ->with('defaultValue')
            ->willReturnSelf();
        $violationBuilder
            ->expects(self::once())
            ->method('addViolation');

        $this->executionContext
            ->expects(self::once())
            ->method('buildViolation')
            ->willReturn($violationBuilder);

        $this->validator->validate($fieldDefinitionData, new FieldDefinitionDefaultValue());
    }

    /**
     * @return iterable<string, array{FieldDefinitionData}>
     */
    public static function provideDefaultValuesNotAllowedBySubmittedData(): iterable
    {
        yield 'country with multiple choice disabled in the same submit' => [
            self::createCountryFieldDefinitionData(true, false),
        ];

        yield 'integer with maximum value lowered in the same submit' => [
            self::createIntegerFieldDefinitionData(100, 10),
        ];
    }

    public function testSkipsStoredDefaultValueNotSubmittedWithForm(): void
    {
        $this->executionContext
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate(
            self::createCountryFieldDefinitionData(true, false, false),
            new FieldDefinitionDefaultValue()
        );
    }

    private static function createCountryFieldDefinitionData(
        bool $storedIsMultiple,
        ?bool $submittedIsMultiple,
        bool $isDefaultValueSubmitted = true
    ): FieldDefinitionData {
        $storedDefaultValue = new CountryValue(self::COUNTRIES_INFO);

        return self::createFieldDefinitionData(
            (new CountryType(self::COUNTRIES_INFO))->getFieldTypeIdentifier(),
            [
                'fieldSettings' => ['isMultiple' => $storedIsMultiple],
                'defaultValue' => $storedDefaultValue,
            ],
            [
                'fieldSettings' => $submittedIsMultiple === null ? null : ['isMultiple' => $submittedIsMultiple],
                'defaultValue' => $isDefaultValueSubmitted ? new CountryValue(self::COUNTRIES_INFO) : $storedDefaultValue,
            ]
        );
    }

    private static function createIntegerFieldDefinitionData(
        int $storedMaxValue,
        ?int $submittedMaxValue
    ): FieldDefinitionData {
        return self::createFieldDefinitionData(
            (new IntegerType())->getFieldTypeIdentifier(),
            [
                'validatorConfiguration' => ['IntegerValueValidator' => ['maxIntegerValue' => $storedMaxValue]],
                'defaultValue' => new IntegerValue(),
            ],
            [
                'validatorConfiguration' => $submittedMaxValue === null
                    ? null
                    : ['IntegerValueValidator' => ['maxIntegerValue' => $submittedMaxValue]],
                'defaultValue' => new IntegerValue(self::INTEGER_DEFAULT_VALUE),
            ]
        );
    }

    /**
     * @param array<string, mixed> $storedProperties
     * @param array<string, mixed> $submittedProperties
     */
    private static function createFieldDefinitionData(
        string $fieldTypeIdentifier,
        array $storedProperties,
        array $submittedProperties
    ): FieldDefinitionData {
        return new FieldDefinitionData([
            'fieldDefinition' => new FieldDefinition($storedProperties + [
                'id' => 1,
                'identifier' => 'field',
                'fieldGroup' => 'content',
                'position' => 0,
                'fieldTypeIdentifier' => $fieldTypeIdentifier,
                'isTranslatable' => false,
                'isThumbnail' => false,
                'isRequired' => false,
                'isInfoCollector' => false,
                'isSearchable' => true,
                'mainLanguageCode' => 'eng-GB',
            ]),
        ] + $submittedProperties);
    }
}
