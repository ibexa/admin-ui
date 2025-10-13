<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Validator\Constraint;

use Ibexa\AdminUi\Validator\Constraints\UniqueFieldDefinitionIdentifier;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class UniqueFieldDefinitionIdentifierTest extends TestCase
{
    public function testConstruct(): void
    {
        $constraint = new UniqueFieldDefinitionIdentifier();
        self::assertSame('ez.field_definition.identifier.unique', $constraint->message);
    }

    public function testGetTartes(): void
    {
        $constraint = new UniqueFieldDefinitionIdentifier();
        self::assertSame(Constraint::CLASS_CONSTRAINT, $constraint->getTargets());
    }
}
