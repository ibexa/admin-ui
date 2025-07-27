<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\Mapper\GroupLimitationMapper;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\UserGroupLimitation;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class GroupLimitationMapperTest extends TestCase
{
    public function testMapLimitationValue(): void
    {
        $expected = ['policy.limitation.group.self'];

        $translatorMock = $this->createMock(TranslatorInterface::class);
        $translatorMock
            ->expects(self::once())
            ->method('trans')
            ->willReturnArgument(0);

        $mapper = new GroupLimitationMapper($translatorMock);
        $result = $mapper->mapLimitationValue(new UserGroupLimitation([
            'limitationValues' => [1],
        ]));

        self::assertEquals($expected, $result);
    }
}
