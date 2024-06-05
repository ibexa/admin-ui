<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Validator\Constraint;

use Ibexa\AdminUi\Form\Data\ContentTypeData;
use Ibexa\AdminUi\Validator\Constraints\UniqueContentTypeIdentifier;
use Ibexa\AdminUi\Validator\Constraints\UniqueContentTypeIdentifierValidator;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeDraft;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Values\ContentType\ContentType as APIContentType;
use Ibexa\Core\Repository\Values\ContentType\ContentTypeDraft as APIContentTypeDraft;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class UniqueContentTypeIdentifierValidatorTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $contentTypeService;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $executionContext;

    /**
     * @var \Ibexa\AdminUi\Validator\Constraints\UniqueContentTypeIdentifierValidator
     */
    private $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->contentTypeService = $this->createMock(ContentTypeService::class);
        $this->executionContext = $this->createMock(ExecutionContextInterface::class);
        $this->validator = new UniqueContentTypeIdentifierValidator($this->contentTypeService);
        $this->validator->initialize($this->executionContext);
    }

    public function testNotContentTypeData(): void
    {
        $value = new stdClass();
        $this->contentTypeService
            ->expects(self::never())
            ->method('loadContentTypeByIdentifier');
        $this->executionContext
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate($value, new UniqueContentTypeIdentifier());
    }

    public function testNullContentTypeIdentifier(): void
    {
        $value = new ContentTypeData([
            'identifier' => null,
            'contentTypeDraft' => $this->getContentTypeDraft(),
        ]);

        $this->contentTypeService
            ->expects(self::never())
            ->method('loadContentTypeByIdentifier');
        $this->executionContext
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate($value, new UniqueContentTypeIdentifier());
    }

    public function testValid(): void
    {
        $identifier = 'foo_identifier';
        $value = new ContentTypeData([
            'identifier' => $identifier,
            'contentTypeDraft' => $this->getContentTypeDraft(),
        ]);

        $this->contentTypeService
            ->expects(self::once())
            ->method('loadContentTypeByIdentifier')
            ->with($identifier)
            ->willThrowException(new NotFoundException('foo', 'bar'));
        $this->executionContext
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate($value, new UniqueContentTypeIdentifier());
    }

    public function testEditingContentTypeDraftFromExistingContentTypeIsValid(): void
    {
        $identifier = 'foo_identifier';
        $contentTypeId = 123;
        $contentTypeDraft = $this->getMockBuilder(ContentTypeDraft::class)
            ->setConstructorArgs([[
                'id' => $contentTypeId,
                'identifier' => $identifier,
            ]])
            ->getMockForAbstractClass();

        $value = new ContentTypeData(['identifier' => $identifier, 'contentTypeDraft' => $contentTypeDraft]);
        $returnedContentType = $this->getMockBuilder(ContentType::class)
            ->setConstructorArgs([['id' => $contentTypeId]])
            ->getMockForAbstractClass();
        $this->contentTypeService
            ->expects(self::once())
            ->method('loadContentTypeByIdentifier')
            ->with($identifier)
            ->willReturn($returnedContentType);
        $this->executionContext
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate($value, new UniqueContentTypeIdentifier());
    }

    public function testInvalid(): void
    {
        $identifier = 'foo_identifier';
        $contentTypeDraft = $this->getMockBuilder(ContentTypeDraft::class)
            ->setConstructorArgs([[
                'id' => 456,
                'identifier' => $identifier,
            ]])
            ->getMockForAbstractClass();

        $value = new ContentTypeData(['identifier' => $identifier, 'contentTypeDraft' => $contentTypeDraft]);
        $constraint = new UniqueContentTypeIdentifier();
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $returnedContentType = $this->getMockBuilder(ContentType::class)
            ->setConstructorArgs([['id' => 123]])
            ->getMockForAbstractClass();
        $this->contentTypeService
            ->expects(self::once())
            ->method('loadContentTypeByIdentifier')
            ->with($identifier)
            ->willReturn($returnedContentType);
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

        $this->validator->validate($value, $constraint);
    }

    private function getContentTypeDraft(): ContentTypeDraft
    {
        return new APIContentTypeDraft([
            'innerContentType' => new APIContentType([
                'identifier' => 'foo',
            ]),
        ]);
    }
}
