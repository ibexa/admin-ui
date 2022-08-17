<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Behat\Mink\Session;
use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Locator\CSSLocator;
use Ibexa\Behat\Browser\Locator\CSSLocatorBuilder;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Behat\Core\Debug\InteractiveDebuggerTrait;
use Traversable;

class ContentItemAdminPreview extends Component
{use InteractiveDebuggerTrait;
    /** @var \Ibexa\AdminUi\Behat\Component\Fields\FieldTypeComponentInterface[] */
    private $fieldTypeComponents;

    public function __construct(Session $session, Traversable $fieldTypeComponents)
    {
        parent::__construct($session);
        $this->fieldTypeComponents = iterator_to_array($fieldTypeComponents);
    }

    public function verifyFieldHasValues(string $fieldLabel, array $expectedValues, ?string $fieldTypeIdentifier)
    {
        $fieldPosition = $this->getFieldPosition($fieldLabel);
        $nthFieldLocator = new VisibleCSSLocator('', sprintf($this->getLocator('nthFieldContainer')->getSelector(), $fieldPosition, $fieldPosition));

        $fieldValueLocator = CSSLocatorBuilder::base($nthFieldLocator)->withDescendant($this->getLocator('fieldValue'))->build();
        $fieldTypeIdentifier = $fieldTypeIdentifier ?? $this->detectFieldTypeIdentifier($fieldValueLocator);

        foreach ($this->fieldTypeComponents as $fieldTypeComponent) {
            if ($fieldTypeComponent->getFieldTypeIdentifier() === $fieldTypeIdentifier) {
                $fieldTypeComponent->setParentLocator($fieldValueLocator);
                $fieldTypeComponent->verifyValueInItemView($expectedValues);

                return;
            }
        }
    }

    public function verifyIsLoaded(): void
    {
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('nthFieldContainer', 'div.ibexa-content-field:nth-of-type(%s), div.ibexa-pc-product-item-preview:nth-of-type(%s)'),
            new VisibleCSSLocator('fieldName', '.ibexa-content-field__name, .ibexa-pc-product-item-preview__label'),
            new VisibleCSSLocator('fieldValue', '.ibexa-content-field__value, .ibexa-pc-product-item-preview__value'),
            new VisibleCSSLocator('fieldValueContainer', ':first-child'),
        ];
    }

    private function getFieldPosition(string $fieldLabel): int
    {
        $fields = $this->getHTMLPage()->findAll($this->getLocator('fieldName'))->assert()->hasElements();

        $fieldPosition = 1;
        foreach ($fields as $field) {
            if ($field->getText() === $fieldLabel) {
                return $fieldPosition;
            }

            ++$fieldPosition;
        }
    }

    private function detectFieldTypeIdentifier(CSSLocator $fieldValueLocator)
    {
        $fieldClass = $this->getHTMLPage()
            ->find(CSSLocatorBuilder::base($fieldValueLocator)->withDescendant($this->getLocator('fieldValueContainer'))->build())
            ->getAttribute('class');

        if ('ibexa-scrollable-table-wrapper mb-0' === $fieldClass) {
            return 'ezuser';
        }

        if (false !== strpos($fieldClass, 'ibexa-table-header')) {
            return 'ezmatrix';
        }

        if ('' === $fieldClass) {
            return 'ezboolean';
        }

        $fieldTypeIdentifierRegex = '/ez|ibexa[a-z_]*-field/';
        preg_match($fieldTypeIdentifierRegex, $fieldClass, $matches);

        return explode('-', $matches[0])[0];
    }
}
