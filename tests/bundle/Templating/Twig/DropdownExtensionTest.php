<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\Templating\Twig;

use Ibexa\Bundle\AdminUi\Templating\Twig\DropdownExtension;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Form\ChoiceList\View\ChoiceGroupView;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DropdownExtensionTest extends TestCase
{
    private DropdownExtension $extension;

    protected function setUp(): void
    {
        $translator = self::createStub(TranslatorInterface::class);
        $translator
            ->method('trans')
            ->willReturnCallback(
                static fn (string $id, array $parameters, ?string $domain): string => sprintf('%s@%s%s', $id, $domain, implode('', $parameters))
            );

        $this->extension = new DropdownExtension($translator, PropertyAccess::createPropertyAccessor());
    }

    public function testFlatChoicesBecomeItems(): void
    {
        $items = $this->extension->getItems(
            ['translation_domain' => false],
            [new ChoiceView(null, '1', 'One'), new ChoiceView(null, '2', 'Two')],
            []
        );

        self::assertSame(
            [
                ['id' => '1', 'label' => 'One'],
                ['id' => '2', 'label' => 'Two'],
            ],
            $items
        );
    }

    public function testNestedGroupsKeepTheirDepth(): void
    {
        $items = $this->extension->getItems(
            ['translation_domain' => false],
            [
                new ChoiceView(null, 'loose', 'Loose'),
                new ChoiceGroupView('Fruits', [
                    new ChoiceView(null, 'apple', 'Apple'),
                    new ChoiceGroupView('Berries', [
                        new ChoiceView(null, 'cherry', 'Cherry'),
                        new ChoiceGroupView('Wild', [new ChoiceView(null, 'blueberry', 'Blueberry')]),
                    ]),
                ]),
            ],
            []
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
            $items
        );
    }

    public function testPreferredChoicesComeFirstAndLaterDuplicatesAreDropped(): void
    {
        $items = $this->extension->getItems(
            ['translation_domain' => false],
            [
                new ChoiceGroupView('Only duplicates', [new ChoiceView(null, 'apple', 'Apple')]),
                new ChoiceGroupView('Mixed', [
                    new ChoiceView(null, 'apple', 'Apple'),
                    new ChoiceView(null, 'pear', 'Pear'),
                ]),
            ],
            [new ChoiceView(null, 'apple', 'Apple')]
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

        $items = $this->extension->getItems(
            ['translation_domain' => false],
            [
                $groupLike('Brand', [new ChoiceView(null, 'site-1', 'Site 1')]),
                $groupLike(null, [
                    new ChoiceView(null, 'site-2', 'Site 2'),
                    $groupLike('Nested', [new ChoiceView(null, 'site-3', 'Site 3')]),
                ]),
            ],
            []
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
        $items = $this->extension->getItems(
            ['translation_domain' => false],
            [
                ['value' => 1, 'label' => 'One'],
                [
                    'label' => 'Units',
                    'choices' => [
                        ['value' => 'kg', 'label' => 'Kilogram'],
                        new ChoiceView(null, 'g', 'Gram'),
                    ],
                ],
            ],
            [['value' => 'g', 'label' => 'Gram']]
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
    public static function provideInvalidTranslationDomains(): iterable
    {
        yield 'integer' => [123];
        yield 'true' => [true];
        yield 'array' => [['messages']];
    }

    #[DataProvider('provideInvalidTranslationDomains')]
    public function testInvalidTranslationDomainInTheContextIsRejected(mixed $translationDomain): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->extension->getItems(['translation_domain' => $translationDomain], [new ChoiceView(null, '1', 'One')], []);
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

        $this->extension->getItems(['translation_domain' => false], [$choice], []);
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
            $this->extension->getItems(['translation_domain' => false], $choices, []),
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
            $this->extension->getItems(['translation_domain' => 'messages'], $choices, []),
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
            $this->extension->getItems(['translation_domain' => null], $choices, []),
            'With a null domain, string labels should be translated with the translator\'s default domain.'
        );
    }
}
