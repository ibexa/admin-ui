<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Validator\Constraint;

use Ibexa\AdminUi\Validator\Constraints\UniqueContentTypeIdentifier;
use PHPUnit\Framework\TestCase;

class UniqueContentTypeIdentifierTest extends TestCase
{
    public function testConstruct(): void
    {
        $constraint = new UniqueContentTypeIdentifier();
        self::assertSame('ez.content_type.identifier.unique', $constraint->message);
    }

    public function testValidatedBy(): void
    {
        $constraint = new UniqueContentTypeIdentifier();
        self::assertSame('ezplatform.content_forms.validator.unique_content_type_identifier', $constraint->validatedBy());
    }

    public function testGetTargets(): void
    {
        $constraint = new UniqueContentTypeIdentifier();
        self::assertSame(UniqueContentTypeIdentifier::CLASS_CONSTRAINT, $constraint->getTargets());
    }
}
