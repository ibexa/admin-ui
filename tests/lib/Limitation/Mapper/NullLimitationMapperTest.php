<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\Mapper\NullLimitationMapper;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ContentTypeLimitation;
use PHPUnit\Framework\TestCase;

class NullLimitationMapperTest extends TestCase
{
    public function testMapLimitationValue()
    {
        $values = ['foo', 'bar', 'baz'];

        // NullLimitationMapper accepts all types of limitations
        $limitation = new ContentTypeLimitation([
            'limitationValues' => $values,
        ]);

        $mapper = new NullLimitationMapper(null);
        $result = $mapper->mapLimitationValue($limitation);

        self::assertEquals($values, $result);
    }
}

class_alias(NullLimitationMapperTest::class, 'EzSystems\EzPlatformAdminUi\Tests\Limitation\Mapper\NullLimitationMapperTest');
