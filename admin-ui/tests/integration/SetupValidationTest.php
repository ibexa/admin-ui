<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\AdminUi;

use Ibexa\Contracts\Core\Test\IbexaKernelTestCase;

/**
 * @group integration
 *
 * @coversNothing
 */
final class SetupValidationTest extends IbexaKernelTestCase
{
    public function testCompilesSuccessfully(): void
    {
        self::bootKernel();

        $this->expectNotToPerformAssertions();
    }
}
