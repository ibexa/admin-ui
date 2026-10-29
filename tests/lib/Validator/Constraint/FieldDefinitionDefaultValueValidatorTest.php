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

    /** @var ExecutionContextInterface&MockObject */
    private ExecutionContextInterface $executionContext;

    private FieldDefinitionDefaultValueValidator $validator;

    protected function setUp(): void
    {
        $fieldTypeService = $this->createStub(FieldTypeService::class);
        $fieldTypeService
            ->method('getFieldType')
            ->willReturn(new FieldType(new CountryType(self::COUNTRIES_INFO)));

        $this->executionContext = $this->createMock(ExecutionContextInterface::class);
        $this->validator = new FieldDefinitionDefaultValueValidator($fieldTypeService);
        $this->validator->initialize($this->executionContext);
    }

    public function testValidatesDefaultValueAgainstSubmittedFieldSettings(): void
    {
        $this->executionContext
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate(
            $this->createFieldDefinitionData(false, true),
            new FieldDefinitionDefaultValue()
        );
    }

    public function testAddsViolationWhenSubmittedFieldSettingsDoNotAllowDefaultValue(): void
    {
        $violationBuilder = $this->createViolationBuilder();
        $violationBuilder
            ->expects(self::once())
            ->method('addViolation');

        $this->executionContext
            ->expects(self::once())
            ->method('buildViolation')
            ->with('Field definition does not allow multiple countries to be selected.')
            ->willReturn($violationBuilder);

        $this->validator->validate(
            $this->createFieldDefinitionData(true, false),
            new FieldDefinitionDefaultValue()
        );
    }

    private function createFieldDefinitionData(
        bool $storedIsMultiple,
        bool $submittedIsMultiple
    ): FieldDefinitionData {
        return new FieldDefinitionData([
            'fieldDefinition' => new FieldDefinition([
                'id' => 1,
                'identifier' => 'country',
                'fieldGroup' => 'content',
                'position' => 0,
                'fieldTypeIdentifier' => 'ezcountry',
                'isTranslatable' => false,
                'isThumbnail' => false,
                'isRequired' => false,
                'isInfoCollector' => false,
                'isSearchable' => true,
                'mainLanguageCode' => 'eng-GB',
                'fieldSettings' => ['isMultiple' => $storedIsMultiple],
            ]),
            'fieldSettings' => ['isMultiple' => $submittedIsMultiple],
            'defaultValue' => new CountryValue(self::COUNTRIES_INFO),
        ]);
    }

    /**
     * @return ConstraintViolationBuilderInterface&MockObject
     */
    private function createViolationBuilder(): MockObject
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->method('setParameters')->willReturnSelf();
        $violationBuilder->method('atPath')->willReturnSelf();

        return $violationBuilder;
    }
}
