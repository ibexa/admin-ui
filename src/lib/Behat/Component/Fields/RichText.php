<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component\Fields;

use Ibexa\Behat\Browser\Element\Condition\ElementExistsCondition;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Element\ElementInterface;
use Ibexa\Behat\Browser\Element\Mapper\ElementTextMapper;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use PHPUnit\Framework\Assert;

class RichText extends FieldTypeComponent
{
    private const STYLE_MAPPING = [
        'Paragraph' => 'p',
        'Heading 1' => 'h1',
        'Heading 2' => 'h2',
        'Heading 3' => 'h3',
        'Heading 4' => 'h4',
        'Heading 5' => 'h5',
        'Heading 6' => 'h6',
    ];

    private function getFieldInput(): ElementInterface
    {
        return $this->getHTMLPage()->find($this->parentLocator)->find($this->getLocator('fieldInput'));
    }

    private function focusFieldInput(): void
    {
        $this->getDevToolsDriver()->execute('Emulation.setFocusEmulationEnabled', ['enabled' => true]);
        $this->getFieldInput()->click();
    }

    public function setValue(array $parameters): void
    {
        $text = $parameters['value'];
        $maxIterations = 3;
        $counter = 0;
        while ($this->getValue()[0] !== $text && $counter < $maxIterations) {
            $this->executeCommand('selectAll');
            $this->executeCommand('delete');
            $this->getFieldInput()->setValue($parameters['value']);
            usleep(100 * 1000); // 100 ms
            ++$counter;
        }
    }

    public function getValue(): array
    {
        return [$this->getFieldInput()->getText()];
    }

    public function changeStyle(string $style): void
    {
        $this->focusFieldInput();
        $this->getHTMLPage()
            ->findAll($this->getLocator('toolbarElement'))
            ->getByCriterion(new ElementTextCriterion('Paragraph'))
            ->click();
        $this->getHTMLPage()
            ->setTimeout(3)
            ->waitUntilCondition(
                new ElementExistsCondition($this->getHTMLPage(), $this->getLocator('styleDropdown'))
            );
        $this->getHTMLPage()
            ->findAll($this->getLocator('styleDropdownItem'))
            ->getByCriterion(new ElementTextCriterion($style))
            ->click();
    }

    public function insertNewLine(): void
    {
        $this->executeCommand('enter');
    }

    public function insertLine($value, $style = ''): void
    {
        $this->getFieldInput()->setValue($value);

        if ($style === '') {
            return;
        }
        $this->changeStyle($style);

        $styleHTMLTag = self::STYLE_MAPPING[$style];

        Assert::assertStringContainsString(
            sprintf('%s</%s>', $value, $styleHTMLTag),
            $this->getHTMLPage()->find($this->parentLocator)->find(new VisibleCSSLocator('style', $styleHTMLTag))->getOuterHtml()
        );
    }

    public function addUnorderedList(array $listElements): void
    {
        $this->focusFieldInput();
        $this->executeCommand('bulletedList');

        foreach ($listElements as $listElement) {
            $this->insertLine($listElement);

            if ($listElement !== end($listElements)) {
                $this->insertNewLine();
            }
        }

        $actualListElements = $this->getHTMLPage()
            ->find($this->parentLocator)
            ->findAll($this->getLocator('unorderedListElement'))
            ->mapBy(new ElementTextMapper());

        Assert::assertEquals($listElements, $actualListElements);

        $this->insertNewLine();
        $this->executeCommand('outdentList');
    }

    public function clickEmbedInlineButton(): void
    {
        $this->clickElementsToolbarButton('Embed inline');
    }

    public function clickEmbedButton(): void
    {
        $this->clickElementsToolbarButton('Embed');
    }

    public function equalsEmbedInlineItem($itemName): bool
    {
        return $itemName === $this->getHTMLPage()->find($this->getLocator('embedInlineTitle'))->getText();
    }

    public function equalsEmbedItem($itemName): bool
    {
        return $itemName === $this->getHTMLPage()->find($this->getLocator('embedTitle'))->getText();
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('fieldInput', '.ck-editor__editable'),
            new VisibleCSSLocator('additionalToolbar', '.ck-dropdown__panel-visible .ck-toolbar'),
            new VisibleCSSLocator('toolbarElement', '.ck-button'),
            new VisibleCSSLocator('toolbarDropdown', '.ck-dropdown'),
            new VisibleCSSLocator('styleDropdownItem', '.ck-list__item'),
            new VisibleCSSLocator('styleDropdown', '.ck-heading-dropdown .ck-dropdown__panel-visible'),
            new VisibleCSSLocator('unorderedListElement', '.ibexa-data-source__richtext ul li'),
            new VisibleCSSLocator('embedInlineTitle', '.ibexa-embed-inline .ibexa-embed-content__title'),
            new VisibleCSSLocator('embedTitle', '.ibexa-embed .ibexa-embed-content__title'),
        ];
    }

    public function getFieldTypeIdentifier(): string
    {
        return 'ibexa_richtext';
    }

    private function executeCommand(string $commandName): void
    {
        $script = sprintf(
            "document.querySelector('%s %s').ckeditorInstance.execute('%s')",
            $this->parentLocator->getSelector(),
            $this->getLocator('fieldInput')->getSelector(),
            $commandName
        );
        $this->getSession()->executeScript($script);
    }

    protected function clickElementsToolbarButton(string $buttonText): void
    {
        $script = sprintf(
            "Array.from(document.querySelectorAll('%s')).filter(e => e.textContent =='%s')[0].click()",
            $this->getLocator('toolbarElement')->getSelector(),
            $buttonText
        );

        $this->getSession()->executeScript($script);
    }
}
