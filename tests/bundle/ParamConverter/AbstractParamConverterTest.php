<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\ParamConverter;

use Ibexa\Tests\Bundle\Core\Converter\AbstractParamConverterTest as CoreAbstractParamConverterTest;

abstract class AbstractParamConverterTest extends CoreAbstractParamConverterTest
{
    public const SUPPORTED_CLASS = null;

    public function testSupports()
    {
        $config = $this->createConfiguration(static::SUPPORTED_CLASS);

        self::assertTrue($this->converter->supports($config));
    }
}
