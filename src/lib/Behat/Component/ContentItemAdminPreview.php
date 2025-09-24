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
use RuntimeException;

class ContentItemAdminPreview extends Component
{
    /**
     * @param \Ibexa\AdminUi\Behat\Component\Fields\FieldTypeComponentInterface[] $fieldTypeComponents
     */
    public function __construct(
        Session $session,
        protected iterable $fieldTypeComponents
    ) {
        parent::__construct($session);
    }

    /**
     * @param array<string, mixed> $expectedValues
     *
     * @throws \RuntimeException
     */
    public function verifyFieldHasValues(string $fieldLabel, array $expectedValues, ?string $fieldTypeIdentifier): void
    {
        $fieldPosition = $this->getFieldPosition($fieldLabel);
        $nthFieldLocator = new VisibleCSSLocator('', sprintf($this->getLocator('nthFieldContainer')->getSelector(), $fieldPosition));

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
            new VisibleCSSLocator('nthFieldContainer', 'div.ibexa-content-field:nth-of-type(%s)'),
            new VisibleCSSLocator('fieldName', '.ibexa-content-field__name'),
            new VisibleCSSLocator('fieldValue', '.ibexa-content-field__value'),
            new VisibleCSSLocator('fieldValueContainer', ':first-child'),
        ];
    }

    protected function getFieldPosition(string $fieldLabel): int
    {
        $fields = $this->getHTMLPage()->findAll($this->getLocator('fieldName'))->assert()->hasElements();

        $fieldPosition = 1;
        foreach ($fields as $field) {
            if ($field->getText() === $fieldLabel) {
                return $fieldPosition;
            }

            ++$fieldPosition;
        }

        return $fieldPosition;
    }

    protected function detectFieldTypeIdentifier(CSSLocator $fieldValueLocator): string
    {
        $fieldClass = $this->getHTMLPage()
            ->find(CSSLocatorBuilder::base($fieldValueLocator)->withDescendant($this->getLocator('fieldValueContainer'))->build())
            ->getAttribute('class');

        if ('ibexa-scrollable-table-wrapper mb-0' === $fieldClass) {
            return 'ibexa_user';
        }

        if (false !== strpos($fieldClass, 'ibexa-table-header')) {
            return 'ibexa_matrix';
        }

        if ('' === $fieldClass) {
            return 'ibexa_boolean';
        }

        $fieldTypeIdentifierRegex = '/ez|ibexa[a-z_]*-field/';
        preg_match($fieldTypeIdentifierRegex, $fieldClass, $matches);

        if (empty($matches)) {
            throw new RuntimeException(sprintf(
                'Cannot match results for pattern: "%s" and subject: "%s".',
                $fieldTypeIdentifierRegex,
                $fieldClass
            ));
        }

        $matchedResults = explode('-', $matches[0]);

        return $matchedResults[0];
    }
}
