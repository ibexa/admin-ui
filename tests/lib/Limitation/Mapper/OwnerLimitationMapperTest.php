<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\Mapper\OwnerLimitationMapper;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\OwnerLimitation;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class OwnerLimitationMapperTest extends TestCase
{
    public function testMapLimitationValue(): void
    {
        $expected = ['policy.limitation.owner.self'];

        $translatorMock = $this->createMock(TranslatorInterface::class);
        $translatorMock
            ->expects(self::once())
            ->method('trans')
            ->willReturnArgument(0);

        $mapper = new OwnerLimitationMapper($translatorMock);
        $result = $mapper->mapLimitationValue(new OwnerLimitation([
            'limitationValues' => [1],
        ]));

        self::assertEquals($expected, $result);
    }
}
