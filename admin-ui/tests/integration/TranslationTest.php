<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\AdminUi;

use Ibexa\Contracts\Test\Core\Translation\AbstractTranslationCase;

final class TranslationTest extends AbstractTranslationCase
{
    public static function provideConfigNamesForTranslation(): iterable
    {
        yield ['ibexa_admin_ui'];
    }
}
