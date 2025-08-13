<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component\Fields;

use Ibexa\Behat\Browser\Element\ElementInterface;
use Ibexa\Behat\Browser\Locator\CSSLocatorBuilder;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use PHPUnit\Framework\Assert;

final class Keywords extends FieldTypeComponent
{
    private string $setKeywordsValueScript = <<<SCRIPT
        const SELECTOR_TAGGIFY = '.ibexa-data-source__taggify';
        const taggifyContainer = document.querySelector(SELECTOR_TAGGIFY);
        const keywordInput = taggifyContainer.closest('.ibexa-data-source').querySelector('.ibexa-data-source__input-wrapper .ibexa-data-source__input.form-control');
        class KeywordTaggify extends window.ibexa.core.Taggify {
            afterTagsUpdate() {
                const tags = [...this.tags];
                const tagsInputValue = tags.join();
        
                if (keywordInput.value !== tagsInputValue) {
                    keywordInput.value = tagsInputValue;
                    keywordInput.dispatchEvent(new Event('change'));
                }
            }
        }
        const taggify = new KeywordTaggify({
            container: taggifyContainer,
        });
        
        const tags = [%s];
        var list = tags.map(function (item) {
            return {name: item, value: item};
        });
        
        taggify.addTags(list);
    SCRIPT;

    public function setValue(array $parameters): void
    {
        $parsedValue = implode(',', array_map(
            static function (string $element): string {
                return sprintf('"%s"', trim($element));
            },
            explode(',', $parameters['value'])
        ));

        $this->getSession()->getDriver()->executeScript(sprintf($this->setKeywordsValueScript, $parsedValue));
    }

    public function verifyValueInItemView(array $values): void
    {
        $expectedValues = $this->parseValueString($values['value']);

        $keywordItemLocator = CSSLocatorBuilder::base($this->parentLocator)
            ->withDescendant($this->getLocator('keywordItem'))
            ->build();

        $actualValues = $this->getHTMLPage()
            ->findAll($keywordItemLocator)
            ->map(static function (ElementInterface $element): string {
                return $element->getText();
            });
        sort($actualValues);

        Assert::assertEquals($expectedValues, $actualValues);
    }

    /**
     * @return string[]
     */
    private function parseValueString(string $value): array
    {
        $parsedValues = [];

        foreach (explode(',', $value) as $singleValue) {
            $parsedValues[] = trim($singleValue);
        }

        sort($parsedValues);

        return $parsedValues;
    }

    public function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('fieldInput', 'input'),
            new VisibleCSSLocator('keywordItem', '.ibexa-keyword__item'),
        ];
    }

    public function getFieldTypeIdentifier(): string
    {
        return 'ibexa_keyword';
    }
}
