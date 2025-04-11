<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Validator\Constraint;

use Ibexa\AdminUi\Form\Data\URL\URLUpdateData;
use Ibexa\AdminUi\Validator\Constraints\UniqueURL;
use Ibexa\AdminUi\Validator\Constraints\UniqueURLValidator;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\URLService;
use Ibexa\Contracts\Core\Repository\Values\URL\URL;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueURLValidatorTest extends TestCase
{
    /** @var \Ibexa\Contracts\Core\Repository\URLService|\PHPUnit\Framework\MockObject\MockObject */
    private MockObject $urlService;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Validator\Context\ExecutionContextInterface */
    private MockObject $executionContext;

    /** @var \Ibexa\AdminUi\Validator\Constraints\UniqueURLValidator */
    private UniqueURLValidator $validator;

    protected function setUp(): void
    {
        $this->urlService = $this->createMock(URLService::class);
        $this->executionContext = $this->createMock(ExecutionContextInterface::class);

        $this->validator = new UniqueURLValidator($this->urlService);
        $this->validator->initialize($this->executionContext);
    }

    public function testUnsupportedValueType(): void
    {
        $value = new stdClass();

        $this->urlService
            ->expects(self::never())
            ->method('loadByUrl');

        $this->executionContext
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate($value, new UniqueURL());
    }

    public function testValid(): void
    {
        $url = 'http://ibexa.co';

        $this->urlService
            ->expects(self::once())
            ->method('loadByUrl')
            ->with($url)
            ->willThrowException($this->createMock(NotFoundException::class));

        $this->executionContext
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate(new URLUpdateData([
            'id' => 1,
            'url' => $url,
        ]), new UniqueURL());
    }

    public function testInvalid(): void
    {
        $constraint = new UniqueURL();
        $url = 'http://ibexa.co';

        $this->urlService
            ->expects(self::once())
            ->method('loadByUrl')
            ->with($url)
            ->willReturn(new URL([
                'id' => 2,
                'url' => $url,
            ]));

        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->executionContext
            ->expects(self::once())
            ->method('buildViolation')
            ->with($constraint->message)
            ->willReturn($constraintViolationBuilder);

        $constraintViolationBuilder
            ->expects(self::once())
            ->method('atPath')
            ->with('url')
            ->willReturn($constraintViolationBuilder);

        $constraintViolationBuilder
            ->expects(self::once())
            ->method('setParameter')
            ->with('%url%', $url)
            ->willReturn($constraintViolationBuilder);

        $constraintViolationBuilder
            ->expects(self::once())
            ->method('addViolation');

        $this->validator->validate(new URLUpdateData([
            'id' => 1,
            'url' => $url,
        ]), $constraint);
    }

    public function testEditingIsValid(): void
    {
        $id = 1;
        $url = 'http://ibexa.co';

        $this->urlService
            ->expects(self::once())
            ->method('loadByUrl')
            ->with($url)
            ->willReturn(new URL([
                'id' => $id,
                'url' => $url,
            ]));

        $this->executionContext
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate(new URLUpdateData([
            'id' => $id,
            'url' => $url,
        ]), new UniqueURL());
    }
}
