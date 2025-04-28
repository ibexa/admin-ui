<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Validator\Constraint;

use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\AdminUi\Validator\Constraints\ValidatorConfiguration;
use Ibexa\AdminUi\Validator\Constraints\ValidatorConfigurationValidator;
use Ibexa\Contracts\Core\Repository\FieldType;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Core\FieldType\ValidationError;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ValidatorConfigurationValidatorTest extends TestCase
{
    private ExecutionContextInterface&MockObject $executionContext;

    private FieldTypeService&MockObject $fieldTypeService;

    private ValidatorConfigurationValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->executionContext = $this->createMock(ExecutionContextInterface::class);
        $this->fieldTypeService = $this->createMock(FieldTypeService::class);
        $this->validator = new ValidatorConfigurationValidator($this->fieldTypeService);
        $this->validator->initialize($this->executionContext);
    }

    public function testNotFieldDefinitionData(): void
    {
        $this->executionContext
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate('foo', new ValidatorConfiguration());
    }

    public function testValid(): void
    {
        $this->executionContext
            ->expects(self::never())
            ->method('buildViolation');

        $fieldTypeIdentifier = 'ezstring';
        $fieldDefinition = new FieldDefinition(['fieldTypeIdentifier' => $fieldTypeIdentifier]);
        $validatorConfiguration = ['foo' => 'bar'];
        $fieldDefData = new FieldDefinitionData(['identifier' => 'foo', 'fieldDefinition' => $fieldDefinition, 'validatorConfiguration' => $validatorConfiguration]);
        $fieldType = $this->createMock(FieldType::class);
        $this->fieldTypeService
            ->expects(self::once())
            ->method('getFieldType')
            ->with($fieldTypeIdentifier)
            ->willReturn($fieldType);
        $fieldType
            ->expects(self::once())
            ->method('validateValidatorConfiguration')
            ->with($validatorConfiguration)
            ->willReturn([]);

        $this->validator->validate($fieldDefData, new ValidatorConfiguration());
    }

    public function testInvalid(): void
    {
        $fieldTypeIdentifier = 'ezstring';
        $fieldDefinition = new FieldDefinition(['fieldTypeIdentifier' => $fieldTypeIdentifier]);
        $validatorConfiguration = ['%foo%' => 'bar'];
        $fieldDefData = new FieldDefinitionData(['identifier' => 'foo', 'fieldDefinition' => $fieldDefinition, 'validatorConfiguration' => $validatorConfiguration]);
        $fieldType = $this->createMock(FieldType::class);
        $this->fieldTypeService
            ->expects(self::once())
            ->method('getFieldType')
            ->with($fieldTypeIdentifier)
            ->willReturn($fieldType);

        $errorParameter = 'bar';
        $errorMessage = 'error';
        $fieldType
            ->expects(self::once())
            ->method('validateValidatorConfiguration')
            ->with($validatorConfiguration)
            ->willReturn([new ValidationError($errorMessage, null, ['%foo%' => $errorParameter])]);

        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->executionContext
            ->expects(self::once())
            ->method('buildViolation')
            ->willReturn($constraintViolationBuilder);
        $this->executionContext
            ->expects(self::once())
            ->method('buildViolation')
            ->with($errorMessage)
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder
            ->expects(self::once())
            ->method('setParameters')
            ->with(['%foo%' => $errorParameter])
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder
            ->expects(self::once())
            ->method('addViolation');

        $this->validator->validate($fieldDefData, new ValidatorConfiguration());
    }
}
