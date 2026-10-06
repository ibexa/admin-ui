<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\AdminUi\Templating\Twig;

use Ibexa\Bundle\AdminUi\Templating\Twig\DropdownExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\ChoiceList\View\ChoiceGroupView;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DropdownExtensionTest extends TestCase
{
    private DropdownExtension $extension;

    protected function setUp(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->method('trans')
            ->willReturnCallback(
                static fn (string $id, array $parameters, ?string $domain): string => sprintf('%s@%s%s', $id, $domain, implode('', $parameters))
            );

        $this->extension = new DropdownExtension($translator);
    }

    public function testFlatChoicesBecomeItems(): void
    {
        $items = $this->extension->getItems(
            [new ChoiceView(null, '1', 'One'), new ChoiceView(null, '2', 'Two')],
            [],
            false
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
            [],
            false
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
            [
                new ChoiceGroupView('Only duplicates', [new ChoiceView(null, 'apple', 'Apple')]),
                new ChoiceGroupView('Mixed', [
                    new ChoiceView(null, 'apple', 'Apple'),
                    new ChoiceView(null, 'pear', 'Pear'),
                ]),
            ],
            [new ChoiceView(null, 'apple', 'Apple')],
            false
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

    public function testLabelsAreTranslatedOnlyWithADomain(): void
    {
        $translatableLabel = $this->createMock(TranslatableInterface::class);
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
            $this->extension->getItems($choices, [], false),
            'Without a domain, string labels should stay untranslated.'
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
            $this->extension->getItems($choices, [], 'messages'),
            'With a domain, string labels should be translated with their parameters.'
        );
    }
}
