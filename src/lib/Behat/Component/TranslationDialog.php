<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Behat\Mink\Session;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

class TranslationDialog extends Dialog
{
    private IbexaDropdown $ibexaDropdown;

    public function __construct(Session $session, IbexaDropdown $ibexaDropdown)
    {
        parent::__construct($session);
        $this->ibexaDropdown = $ibexaDropdown;
    }

    public function selectNewTranslation(string $languageName): void
    {
        $this->getHTMLPage()->find($this->getLocator('expandNewTranslationDropdown'))->click();
        $this->ibexaDropdown->verifyIsLoaded();
        $this->ibexaDropdown->selectOption($languageName);
    }

    public function selectBaseTranslation(string $languageName): void
    {
        $this->getHTMLPage()->find($this->getLocator('expandBaseTranslationDropdown'))->click();
        $this->ibexaDropdown->verifyIsLoaded();
        $this->ibexaDropdown->selectOption($languageName);
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()->setTimeout(3)
            ->find($this->getLocator('addTranslationPopupModalTitle'))
            ->assert()->textEquals('Create a new translation');
    }

    protected function specifyLocators(): array
    {
        return array_merge(parent::specifyLocators(), [
            new VisibleCSSLocator('expandNewTranslationDropdown', '#add-translation-modal [for="add-translation_language"] + .ibexa-dropdown .ibexa-dropdown__selection-info'),
            new VisibleCSSLocator('expandBaseTranslationDropdown', '#add-translation-modal [for="add-translation_base_language"] + .ibexa-dropdown .ibexa-dropdown__selection-info'),
            new VisibleCSSLocator('addTranslationPopupModalTitle', '#add-translation-modal .modal-title'),
        ]);
    }
}
