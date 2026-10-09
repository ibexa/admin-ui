<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\Limitation\Mapper;

use Ibexa\AdminUi\Limitation\Mapper\ChangeOwnerLimitationMapper;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ChangeOwnerLimitation;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ChangeOwnerLimitationMapperTest extends TestCase
{
    private const FORBID_LABEL_KEY = 'policy.limitation.change_owner.forbid';

    private ChangeOwnerLimitationMapper $mapper;

    protected function setUp(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        $this->mapper = new ChangeOwnerLimitationMapper($translator);
    }

    /**
     * @dataProvider provideLimitationValues
     *
     * @param array<int|string> $limitationValues
     * @param string[] $expected
     */
    public function testMapLimitationValue(array $limitationValues, array $expected): void
    {
        $result = $this->mapper->mapLimitationValue($this->createLimitation($limitationValues));

        self::assertSame($expected, $result);
    }

    /**
     * @return iterable<string, array{array<int|string>, string[]}>
     */
    public static function provideLimitationValues(): iterable
    {
        yield 'self marker is displayed as the Forbid label' => [
            [ChangeOwnerLimitation::LIMITATION_VALUE_SELF],
            [self::FORBID_LABEL_KEY],
        ];

        yield 'self marker loaded from persistence as a numeric string' => [
            ['-1'],
            [self::FORBID_LABEL_KEY],
        ];

        yield 'explicit user ID is displayed as is' => [
            [14],
            ['14'],
        ];

        yield 'self marker mixed with explicit user IDs' => [
            [ChangeOwnerLimitation::LIMITATION_VALUE_SELF, 14, '42'],
            [self::FORBID_LABEL_KEY, '14', '42'],
        ];

        yield 'no values' => [
            [],
            [],
        ];
    }

    /**
     * @param array<int|string> $limitationValues
     */
    private function createLimitation(array $limitationValues): ChangeOwnerLimitation
    {
        $limitation = new ChangeOwnerLimitation([]);
        $limitation->limitationValues = $limitationValues;

        return $limitation;
    }
}
