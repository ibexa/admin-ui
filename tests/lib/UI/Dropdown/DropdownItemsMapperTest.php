<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\AdminUi\UI\Dropdown;

use Ibexa\AdminUi\UI\Dropdown\DropdownItemsMapper;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Form\ChoiceList\View\ChoiceGroupView;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DropdownItemsMapperTest extends TestCase
{
    private TranslatorInterface $translator;

    private PropertyAccessorInterface $propertyAccessor;

    protected function setUp(): void
    {
        $translator = self::createStub(TranslatorInterface::class);
        $translator
            ->method('trans')
            ->willReturnCallback(
                static fn (string $id, array $parameters, ?string $domain): string => sprintf('%s@%s%s', $id, $domain, implode('', $parameters))
            );

        $this->translator = $translator;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function testFlatChoicesBecomeItems(): void
    {
        $items = $this->map(
            false,
            [],
            [new ChoiceView(null, '1', 'One'), new ChoiceView(null, '2', 'Two')]
        );

        self::assertSame(
            [
                ['id' => '1', 'label' => 'One'],
                ['id' => '2', 'label' => 'Two'],
            ],
            $items,
            'Flat choices should map to items in order.'
        );
    }

    public function testNestedGroupsKeepTheirDepth(): void
    {
        $items = $this->map(
            false,
            [],
            [
                new ChoiceView(null, 'loose', 'Loose'),
                new ChoiceGroupView('Fruits', [
                    new ChoiceView(null, 'apple', 'Apple'),
                    new ChoiceGroupView('Berries', [
                        new ChoiceView(null, 'cherry', 'Cherry'),
                        new ChoiceGroupView('Wild', [new ChoiceView(null, 'blueberry', 'Blueberry')]),
                    ]),
                ]),
            ]
        );

        self::assertSame(
            [
                ['id' => 'loose', 'label' => 'Loose'],
                [
                    'label' => 'Fruits',
                    'items' => [
                        ['id' => 'apple', 'label' => 'Apple'],
                        [
                            'label' => 'Berries',
                            'items' => [
                                ['id' => 'cherry', 'label' => 'Cherry'],
                                [
                                    'label' => 'Wild',
                                    'items' => [['id' => 'blueberry', 'label' => 'Blueberry']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $items,
            'Nested groups should keep their depth.'
        );
    }

    public function testPreferredChoicesComeFirstAndLaterDuplicatesAreDropped(): void
    {
        $items = $this->map(
            false,
            [new ChoiceView(null, 'apple', 'Apple')],
            [
                new ChoiceGroupView('Only duplicates', [new ChoiceView(null, 'apple', 'Apple')]),
                new ChoiceGroupView('Mixed', [
                    new ChoiceView(null, 'apple', 'Apple'),
                    new ChoiceView(null, 'pear', 'Pear'),
                ]),
            ]
        );

        self::assertSame(
            [
                ['id' => 'apple', 'label' => 'Apple'],
                [
                    'label' => 'Mixed',
                    'items' => [['id' => 'pear', 'label' => 'Pear']],
                ],
            ],
            $items,
            'The preferred choice should come first, its duplicates should be dropped and a group left empty by that should disappear.'
        );
    }

    public function testGroupLikeObjectsAreAcceptedAndUnlabelledGroupsAreFlattened(): void
    {
        $groupLike = static fn (?string $label, array $choices): object => new class($label, $choices) {
            /**
             * @param array<int, object> $choices
             */
            public function __construct(
                public ?string $label,
                public array $choices
            ) {}
        };

        $items = $this->map(
            false,
            [],
            [
                $groupLike('Brand', [new ChoiceView(null, 'site-1', 'Site 1')]),
                $groupLike(null, [
                    new ChoiceView(null, 'site-2', 'Site 2'),
                    $groupLike('Nested', [new ChoiceView(null, 'site-3', 'Site 3')]),
                ]),
            ]
        );

        self::assertSame(
            [
                [
                    'label' => 'Brand',
                    'items' => [['id' => 'site-1', 'label' => 'Site 1']],
                ],
                ['id' => 'site-2', 'label' => 'Site 2'],
                [
                    'label' => 'Nested',
                    'items' => [['id' => 'site-3', 'label' => 'Site 3']],
                ],
            ],
            $items,
            'Any object with label and choices should act as a group; a group without a label should contribute its entries to the surrounding level.'
        );
    }

    public function testArrayChoicesAndGroupsAreAccepted(): void
    {
        $items = $this->map(
            false,
            [['value' => 'g', 'label' => 'Gram']],
            [
                ['value' => 1, 'label' => 'One'],
                [
                    'label' => 'Units',
                    'choices' => [
                        ['value' => 'kg', 'label' => 'Kilogram'],
                        new ChoiceView(null, 'g', 'Gram'),
                    ],
                ],
            ]
        );

        self::assertSame(
            [
                ['id' => 'g', 'label' => 'Gram'],
                ['id' => '1', 'label' => 'One'],
                [
                    'label' => 'Units',
                    'items' => [['id' => 'kg', 'label' => 'Kilogram']],
                ],
            ],
            $items,
            'Templates pass plain arrays with "value" and "label" (and "choices" for a group), which must map like the view objects.'
        );
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function provideInvalidChoices(): iterable
    {
        yield 'object without value and label' => [new stdClass()];
        yield 'array without label' => [['value' => 'x']];
        yield 'scalar' => ['x'];
        yield 'group with non-iterable choices' => [['label' => 'Broken', 'choices' => 'x']];
    }

    #[DataProvider('provideInvalidChoices')]
    public function testInvalidChoicesAreRejected(mixed $choice): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->map(false, [], [$choice]);
    }

    public function testLabelsAreTranslatedUnlessTheDomainIsFalse(): void
    {
        $translatableLabel = self::createStub(TranslatableInterface::class);
        $translatableLabel->method('trans')->willReturn('Translatable');
        $choices = [
            new ChoiceGroupView('group.label', [
                new ChoiceView(null, 'plain', 'plain.label', [], ['%name%' => 'X']),
                new ChoiceView(null, 'translatable', $translatableLabel),
                new ChoiceView(null, 'unlabelled', false),
            ]),
        ];

        self::assertSame(
            [
                [
                    'label' => 'group.label',
                    'items' => [
                        ['id' => 'plain', 'label' => 'plain.label'],
                        ['id' => 'translatable', 'label' => 'Translatable'],
                        ['id' => 'unlabelled', 'label' => ''],
                    ],
                ],
            ],
            $this->map(false, [], $choices),
            'With a false domain, string labels should stay untranslated.'
        );
        self::assertSame(
            [
                [
                    'label' => 'group.label@messages',
                    'items' => [
                        ['id' => 'plain', 'label' => 'plain.label@messagesX'],
                        ['id' => 'translatable', 'label' => 'Translatable'],
                        ['id' => 'unlabelled', 'label' => ''],
                    ],
                ],
            ],
            $this->map('messages', [], $choices),
            'With a domain, string labels should be translated with their parameters.'
        );
        self::assertSame(
            [
                [
                    'label' => 'group.label@',
                    'items' => [
                        ['id' => 'plain', 'label' => 'plain.label@X'],
                        ['id' => 'translatable', 'label' => 'Translatable'],
                        ['id' => 'unlabelled', 'label' => ''],
                    ],
                ],
            ],
            $this->map(null, [], $choices),
            'With a null domain, string labels should be translated with the translator\'s default domain.'
        );
    }

    /**
     * @param iterable<mixed> $preferredChoices
     * @param iterable<mixed> $choices
     *
     * @return array<int, array<string, mixed>>
     */
    private function map(
        string | false | null $translationDomain,
        iterable $preferredChoices,
        iterable $choices
    ): array {
        $mapper = new DropdownItemsMapper($this->translator, $this->propertyAccessor, $translationDomain);

        return $mapper->map($preferredChoices, $choices);
    }
}
