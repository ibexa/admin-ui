<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Validator\Constraint;

use Ibexa\AdminUi\Validator\Constraints\UniqueURL;
use PHPUnit\Framework\TestCase;

class UniqueURLTest extends TestCase
{
    /** @var \Ibexa\AdminUi\Validator\Constraints\UniqueURL */
    private $constraint;

    protected function setUp(): void
    {
        $this->constraint = new UniqueURL();
    }

    public function testConstruct()
    {
        self::assertSame('ez.url.unique', $this->constraint->message);
    }

    public function testValidatedBy()
    {
        self::assertSame('ezplatform.content_forms.validator.unique_url', $this->constraint->validatedBy());
    }

    public function testGetTargets()
    {
        self::assertSame(UniqueURL::CLASS_CONSTRAINT, $this->constraint->getTargets());
    }
}
