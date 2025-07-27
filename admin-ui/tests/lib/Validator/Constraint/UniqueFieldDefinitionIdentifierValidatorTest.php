<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Validator\Constraint;

use Ibexa\AdminUi\Form\Data\ContentTypeData;
use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\AdminUi\Validator\Constraints\UniqueFieldDefinitionIdentifier;
use Ibexa\AdminUi\Validator\Constraints\UniqueFieldDefinitionIdentifierValidator;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\ContentTypeDraft;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueFieldDefinitionIdentifierValidatorTest extends TestCase
{
    private ExecutionContextInterface&MockObject $executionContext;

    private UniqueFieldDefinitionIdentifierValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->executionContext = $this->createMock(ExecutionContextInterface::class);
        $this->validator = new UniqueFieldDefinitionIdentifierValidator();
        $this->validator->initialize($this->executionContext);
    }

    public function testNotFieldDefinitionData(): void
    {
        $this->executionContext
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate('foo', new UniqueFieldDefinitionIdentifier());
    }

    public function testValid(): void
    {
        $this->executionContext
            ->expects(self::never())
            ->method('buildViolation');

        $contentTypeData = new ContentTypeData([
            'contentTypeDraft' => new ContentTypeDraft([
                'innerContentType' => new ContentType([
                    'identifier' => 'test',
                ]),
            ]),
        ]);

        $fieldDefData1 = new FieldDefinitionData(['identifier' => 'foo', 'contentTypeData' => $contentTypeData]);
        $contentTypeData->addFieldDefinitionData($fieldDefData1);
        $fieldDefData2 = new FieldDefinitionData(['identifier' => 'bar', 'contentTypeData' => $contentTypeData]);
        $contentTypeData->addFieldDefinitionData($fieldDefData2);
        $fieldDefData3 = new FieldDefinitionData(['identifier' => 'baz', 'contentTypeData' => $contentTypeData]);
        $contentTypeData->addFieldDefinitionData($fieldDefData3);

        $this->validator->validate($fieldDefData1, new UniqueFieldDefinitionIdentifier());
    }

    public function testInvalid(): void
    {
        $identifier = 'foo';
        $constraint = new UniqueFieldDefinitionIdentifier();
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->executionContext
            ->expects(self::once())
            ->method('buildViolation')
            ->with($constraint->message)
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder
            ->expects(self::once())
            ->method('atPath')
            ->with('identifier')
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder
            ->expects(self::once())
            ->method('setParameter')
            ->with('%identifier%', $identifier)
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder
            ->expects(self::once())
            ->method('addViolation');

        $contentTypeData = new ContentTypeData([
            'contentTypeDraft' => new ContentTypeDraft([
                'innerContentType' => new ContentType([
                    'identifier' => 'test',
                ]),
            ]),
        ]);

        $fieldDefData1 = new FieldDefinitionData(['identifier' => $identifier, 'contentTypeData' => $contentTypeData]);
        $contentTypeData->addFieldDefinitionData($fieldDefData1);
        $fieldDefData2 = new FieldDefinitionData(['identifier' => 'bar', 'contentTypeData' => $contentTypeData]);
        $contentTypeData->addFieldDefinitionData($fieldDefData2);
        $fieldDefData3 = new FieldDefinitionData(['identifier' => $identifier, 'contentTypeData' => $contentTypeData]);
        $contentTypeData->addFieldDefinitionData($fieldDefData3);

        $this->validator->validate($fieldDefData1, $constraint);
    }
}
