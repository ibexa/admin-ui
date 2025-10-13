<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Validator\Constraint;

use Ibexa\AdminUi\Validator\Constraints\LocationIsContainer;
use Ibexa\AdminUi\Validator\Constraints\LocationIsContainerValidator;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class LocationIsContainerValidatorTest extends TestCase
{
    private ExecutionContextInterface&MockObject $executionContext;

    private LocationIsContainerValidator $validator;

    private Location&MockObject $location;

    private ContentType&MockObject $contentType;

    protected function setUp(): void
    {
        $this->executionContext = $this->createMock(ExecutionContextInterface::class);
        $this->validator = new LocationIsContainerValidator();
        $this->validator->initialize($this->executionContext);

        $content = $this->createMock(Content::class);

        $this->location = $this->createMock(Location::class);
        $this->location
            ->method('getContent')
            ->willReturn($content);

        $this->contentType = $this->createMock(ContentType::class);

        $content
            ->method('getContentType')
            ->willReturn($this->contentType);
    }

    public function testValid(): void
    {
        $this->contentType
            ->method('isContainer')
            ->willReturn(true);

        $this->executionContext
            ->expects(self::never())
            ->method('addViolation');

        $this->validator->validate($this->location, new LocationIsContainer());
    }

    public function testInvalid(): void
    {
        $this->contentType
            ->method('isContainer')
            ->willReturn(false);

        $this->executionContext
            ->expects(self::once())
            ->method('addViolation');

        $this->validator->validate($this->location, new LocationIsContainer());
    }
}
