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
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DropdownExtensionTest extends TestCase
{
    private DropdownExtension $extension;

    protected function setUp(): void
    {
        $translator = self::createStub(TranslatorInterface::class);
        $translator
            ->method('trans')
            ->willReturnCallback(static fn (string $id, array $parameters, ?string $domain): string => sprintf('%s@%s', $id, $domain));

        $this->extension = new DropdownExtension($translator, PropertyAccess::createPropertyAccessor());
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function provideTranslationDomainContexts(): iterable
    {
        yield 'string domain' => [['translation_domain' => 'messages'], 'label@messages'];
        yield 'false domain' => [['translation_domain' => false], 'label'];
        yield 'null domain' => [['translation_domain' => null], 'label@'];
        yield 'no domain' => [[], 'label@'];
    }

    /**
     * @param array<string, mixed> $context
     */
    #[DataProvider('provideTranslationDomainContexts')]
    public function testLabelsAreTranslatedWithTheDomainFromTheContext(
        array $context,
        string $expectedLabel
    ): void {
        $items = $this->extension->getItems($context, [new ChoiceView(null, '1', 'label')], []);

        self::assertSame([['id' => '1', 'label' => $expectedLabel]], $items, 'The label should follow the domain found in the context.');
    }

    public function testPreferredChoicesComeFirst(): void
    {
        $items = $this->extension->getItems(
            ['translation_domain' => false],
            [new ChoiceView(null, '1', 'One'), new ChoiceView(null, '2', 'Two')],
            [new ChoiceView(null, '2', 'Two')]
        );

        self::assertSame(
            [
                ['id' => '2', 'label' => 'Two'],
                ['id' => '1', 'label' => 'One'],
            ],
            $items,
            'Preferred choices should come first and not repeat.'
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
}
