<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component\Fields;

use Ibexa\Behat\Browser\Locator\CSSLocatorBuilder;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Webmozart\Assert\Assert;

final class URL extends FieldTypeComponent
{
    public function setValue(array $parameters): void
    {
        $this->setSpecificFieldValue('url', $parameters['url']);
        $this->setSpecificFieldValue('text', $parameters['text']);
    }

    public function setSpecificFieldValue(string $fieldName, string $value): void
    {
        $fieldSelector = CSSLocatorBuilder::base($this->parentLocator)
            ->withDescendant($this->getLocator($fieldName))
            ->build();

        $this->getHTMLPage()->find($fieldSelector)->setValue($value);
    }

    public function getValue(): array
    {
        return [
            'url' => $this->getSpecificFieldValue('url'),
            'text' => $this->getSpecificFieldValue('text'),
        ];
    }

    public function getSpecificFieldValue(string $fieldName): string
    {
        $fieldSelector = CSSLocatorBuilder::base($this->parentLocator)
            ->withDescendant($this->getLocator($fieldName))
            ->build();

        return $this->getHTMLPage()->find($fieldSelector)->getValue();
    }

    public function verifyValueInEditView(array $values): void
    {
        Assert::eq(
            $this->getValue()['url'],
            $values['url'],
            sprintf('Field %s has wrong value', $values['label'])
        );
        Assert::eq(
            $this->getValue()['text'],
            $values['text'],
            sprintf('Field %s has wrong value', $values['label'])
        );
    }

    public function verifyValueInItemView(array $values): void
    {
        Assert::eq(
            $this->getHTMLPage()->find($this->parentLocator)->getText(),
            $values['text'],
            'Field has wrong value'
        );

        $urlSelector = CSSLocatorBuilder::base($this->parentLocator)
            ->withDescendant(new VisibleCSSLocator('', 'a'))
            ->build();

        Assert::eq(
            $this->getHTMLPage()->find($urlSelector)->getAttribute('href'),
            $values['url'],
            'Field has wrong value'
        );
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('url', '#ezplatform_content_forms_content_edit_fieldsData_ibexa_url_value_link'),
            new VisibleCSSLocator('text', '#ezplatform_content_forms_content_edit_fieldsData_ibexa_url_value_text'),
        ];
    }

    public function getFieldTypeIdentifier(): string
    {
        return 'ibexa_url';
    }
}
