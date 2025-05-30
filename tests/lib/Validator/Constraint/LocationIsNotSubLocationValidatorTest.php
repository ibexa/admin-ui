<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Validator\Constraint;

use Ibexa\AdminUi\Validator\Constraints\LocationIsNotSubLocation;
use Ibexa\AdminUi\Validator\Constraints\LocationIsNotSubLocationValidator;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class LocationIsNotSubLocationValidatorTest extends TestCase
{
    private ExecutionContextInterface&MockObject $executionContext;

    private LocationIsNotSubLocationValidator $validator;

    protected function setUp(): void
    {
        $this->executionContext = $this->createMock(ExecutionContextInterface::class);
        $this->validator = new LocationIsNotSubLocationValidator();
        $this->validator->initialize($this->executionContext);
    }

    public function testValid(): void
    {
        $location = $this
            ->getMockBuilder(Location::class)
            ->setMethodsExcept(['__get'])
            ->setConstructorArgs([['pathString' => '/1/2/3/']])
            ->getMock();

        $comparedLocation = $this
            ->getMockBuilder(Location::class)
            ->setMethodsExcept(['__get'])
            ->setConstructorArgs([['pathString' => '/3/5/']])
            ->getMock();

        $this->executionContext
            ->expects(self::never())
            ->method('addViolation');

        $constraint = new LocationIsNotSubLocation(['value' => $comparedLocation]);

        $this->validator->validate($location, $constraint);
    }

    public function testInvalid(): void
    {
        $location = $this
            ->getMockBuilder(Location::class)
            ->setMethodsExcept(['__get'])
            ->setConstructorArgs([['pathString' => '/1/2/3/']])
            ->getMock();

        $comparedLocation = $this
            ->getMockBuilder(Location::class)
            ->setMethodsExcept(['__get'])
            ->setConstructorArgs([['pathString' => '/1/2/']])
            ->getMock();

        $constraint = new LocationIsNotSubLocation(['value' => $comparedLocation]);

        $constraintViolationBuilder = $this
            ->getMockBuilder(ConstraintViolationBuilderInterface::class)
            ->getMock();

        $this->executionContext
            ->method('buildViolation')
            ->willReturn($constraintViolationBuilder);

        $constraintViolationBuilder
            ->method('setParameter')
            ->willReturn($constraintViolationBuilder);

        $constraintViolationBuilder
            ->method('setCode')
            ->willReturn($constraintViolationBuilder);

        $this->executionContext
            ->expects(self::once())
            ->method('buildViolation');

        $this->validator->validate($location, $constraint);
    }
}
