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
        $this->assertSame('ez.url.unique', $this->constraint->message);
    }

    public function testValidatedBy()
    {
        $this->assertSame('ezplatform.content_forms.validator.unique_url', $this->constraint->validatedBy());
    }

    public function testGetTargets()
    {
        $this->assertSame(UniqueURL::CLASS_CONSTRAINT, $this->constraint->getTargets());
    }
}

class_alias(UniqueURLTest::class, 'EzSystems\EzPlatformAdminUi\Tests\Validator\Constraint\UniqueURLTest');
