<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Behat\Component;

use Behat\Mink\Session;
use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

final class CreateUrlAliasForm extends Component
{
    private \Ibexa\AdminUi\Behat\Component\IbexaDropdown $ibexaDropdown;

    public function __construct(Session $session, IbexaDropdown $ibexaDropdown)
    {
        parent::__construct($session);
        $this->ibexaDropdown = $ibexaDropdown;
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()->find($this->getLocator('title'))->assert()->textEquals('Create a new URL alias');
    }

    public function createNewUrlAlias(string $path, string $languageName, bool $isRedirecting): void
    {
        $this->getHTMLPage()->find($this->getLocator('pathInput'))->setValue($path);
        $this->getHTMLPage()->find($this->getLocator('languageDropdown'))->click();
        $this->ibexaDropdown->verifyIsLoaded();
        $this->ibexaDropdown->selectOption($languageName);

        $this->getHTMLPage()->find($this->getLocator('createButton'))->click();
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('title', '#ibexa-modal--custom-url-alias .modal-title'),
            new VisibleCSSLocator('createButton', '#custom_url_add_add'),
            new VisibleCSSLocator('pathInput', '#custom_url_add_path'),
            new VisibleCSSLocator('languageDropdown', '.ibexa-custom-url-from__item .ibexa-dropdown'),
        ];
    }
}
