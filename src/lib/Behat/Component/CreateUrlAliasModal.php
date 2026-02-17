<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\AdminUi\Behat\Component;

use Behat\Mink\Session;
use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

final class CreateUrlAliasModal extends Component
{
    private IbexaDropdown $ibexaDropdown;

    public function __construct(Session $session, IbexaDropdown $ibexaDropdown)
    {
        parent::__construct($session);
        $this->ibexaDropdown = $ibexaDropdown;
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()->setTimeout(3)->find($this->getLocator('title'))->assert()->textEquals('Create a new URL alias');
    }

    public function createNewUrlAlias(string $path, string $languageName, bool $redirect): void
    {
        $this->verifyIsLoaded();

        $page = $this->getHTMLPage();
        $page->find($this->getLocator('pathInput'))->setValue($path);

        $page->find($this->getLocator('languageDropdown'))->click();
        $this->ibexaDropdown->verifyIsLoaded();
        $this->ibexaDropdown->selectOption($languageName);

        $this->setRedirectToggle($redirect);
        $this->ensurePathInputIsFilled($path);

        $page->setTimeout(5)->find($this->getLocator('createButton'))->click();
    }

    private function setRedirectToggle(bool $shouldBeChecked): void
    {
        $toggle = $this->getHTMLPage()->find($this->getLocator('redirectToggle'));
        $isChecked = $toggle->hasClass('ibexa-toggle--is-checked');
        if ($shouldBeChecked !== $isChecked) {
            $this->getHTMLPage()->find($this->getLocator('redirectToggle'))->click();
        }
    }

    private function ensurePathInputIsFilled(string $path): void
    {
        $maxAttempts = 3;

        while (!$this->createButtonIsEnabled() && $maxAttempts-- > 0) {
            $this->getHTMLPage()->setTimeout(2)->find($this->getLocator('pathInput'))->setValue($path);
        }
        if (!$this->createButtonIsEnabled()) {
            throw new \Exception('Create button disabled after retries - path input invalid');
        }
    }

    private function createButtonIsEnabled(): bool
    {
        return !$this->getHTMLPage()->find($this->getLocator('createButton'))->hasAttribute('disabled');
    }
    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('title', '#ibexa-modal--custom-url-alias .modal-title'),
            new VisibleCSSLocator('createButton', '#custom_url_add_add'),
            new VisibleCSSLocator('pathInput', '#custom_url_add_path'),
            new VisibleCSSLocator('languageDropdown', '.ibexa-custom-url-from__item .ibexa-dropdown__selection-info'),
            new VisibleCSSLocator('redirectToggle', '.ibexa-custom-url-from__item .ibexa-toggle'),
        ];
    }
}
