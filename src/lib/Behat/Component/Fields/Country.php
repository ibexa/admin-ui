<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component\Fields;

use Behat\Mink\Session;
use Ibexa\AdminUi\Behat\Component\IbexaDropdown;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

class Country extends FieldTypeComponent
{
    private IbexaDropdown $dropdown;

    public function __construct(Session $session, IbexaDropdown $dropdown)
    {
        parent::__construct($session);
        $this->dropdown = $dropdown;
    }

    public function setValue(array $parameters): void
    {
        $this->getHTMLPage()->find($this->getLocator('dropdownSelector'))->click();
        $this->dropdown->verifyIsLoaded();
        $this->dropdown->selectOption($parameters['value']);
    }

    public function getFieldTypeIdentifier(): string
    {
        return 'ibexa_country';
    }

    public function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('fieldInput', 'select'),
            new VisibleCSSLocator('dropdownSelector', '.ibexa-dropdown__selection-info'),
        ];
    }
}
