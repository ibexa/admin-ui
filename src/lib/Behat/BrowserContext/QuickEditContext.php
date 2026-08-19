<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\BrowserContext;

use Behat\Behat\Context\Context;
use Ibexa\AdminUi\Behat\Component\QuickEditDraftConflictModal;
use Ibexa\AdminUi\Behat\Component\QuickEditField;
use Ibexa\AdminUi\Behat\Component\VersionsList;
use Ibexa\AdminUi\Behat\Page\ContentViewPage;
use Webmozart\Assert\Assert;

/**
 * Steps driving the inline "quick edit" affordance on the Content view's Fields tab.
 *
 * These steps are new: the interaction they exercise (double-click to open, Escape/outside
 * click/button to cancel, a draft-conflict confirmation modal of its own) has no prior coverage
 * in this suite, and Mink's DriverInterface has no cross-driver "press a named key" or "double
 * click" step already wired up elsewhere in admin-ui to build on.
 */
final readonly class QuickEditContext implements Context
{
    public function __construct(
        private QuickEditField $quickEditField,
        private QuickEditDraftConflictModal $quickEditDraftConflictModal,
        private VersionsList $versionsList,
        private ContentViewPage $contentViewPage
    ) {
    }

    /**
     * @When I double-click the :fieldLabel field
     */
    public function iDoubleClickTheField(string $fieldLabel): void
    {
        $this->quickEditField->openViaDoubleClick($fieldLabel);
    }

    /**
     * @When I set the quick-edit input for :fieldLabel to :value
     */
    public function iSetTheQuickEditInputTo(string $fieldLabel, string $value): void
    {
        $this->quickEditField->setInputValue($fieldLabel, $value);
    }

    /**
     * @When I toggle the quick-edit checkbox for :fieldLabel
     */
    public function iToggleTheQuickEditCheckboxFor(string $fieldLabel): void
    {
        $this->quickEditField->toggleCheckbox($fieldLabel);
    }

    /**
     * @When I click the quick-edit confirm button for :fieldLabel
     */
    public function iClickTheQuickEditConfirmButtonFor(string $fieldLabel): void
    {
        $this->quickEditField->clickConfirm($fieldLabel);
    }

    /**
     * @When I click the quick-edit cancel button for :fieldLabel
     */
    public function iClickTheQuickEditCancelButtonFor(string $fieldLabel): void
    {
        $this->quickEditField->clickCancel($fieldLabel);
    }

    /**
     * @When I press the Escape key while quick-editing :fieldLabel
     */
    public function iPressEscapeWhileQuickEditing(string $fieldLabel): void
    {
        $this->quickEditField->pressEscape($fieldLabel);
    }

    /**
     * @When I click outside the quick-edit editor
     */
    public function iClickOutsideTheQuickEditEditor(): void
    {
        $this->quickEditField->clickOutside();
    }

    /**
     * @When I confirm the quick-edit draft conflict
     */
    public function iConfirmTheQuickEditDraftConflict(): void
    {
        $this->quickEditDraftConflictModal->verifyIsLoaded();
        $this->quickEditDraftConflictModal->confirm();
    }

    /**
     * @When I dismiss the quick-edit draft conflict
     */
    public function iDismissTheQuickEditDraftConflict(): void
    {
        $this->quickEditDraftConflictModal->verifyIsLoaded();
        $this->quickEditDraftConflictModal->dismiss();
    }

    /**
     * @Then the quick-edit draft conflict should appear
     */
    public function theQuickEditDraftConflictShouldAppear(): void
    {
        $this->quickEditDraftConflictModal->verifyIsLoaded();
    }

    /**
     * @Then there should be :count open quick-edit editors
     */
    public function thereShouldBeOpenQuickEditEditors(int $count): void
    {
        Assert::same($this->quickEditField->getOpenEditorCount(), $count);
    }

    /**
     * @Then the :fieldLabel field should still be open for quick edit
     */
    public function theFieldShouldStillBeOpenForQuickEdit(string $fieldLabel): void
    {
        Assert::true($this->quickEditField->isEditorOpenFor($fieldLabel));
    }

    /**
     * @Then the :fieldLabel field should not offer quick edit
     */
    public function theFieldShouldNotOfferQuickEdit(string $fieldLabel): void
    {
        Assert::false($this->quickEditField->isQuickEditable($fieldLabel));
    }

    /**
     * @Then there should be :count draft versions for the content item
     */
    public function thereShouldBeDraftVersionsForTheContentItem(int $count): void
    {
        $this->contentViewPage->switchToTab('Versions');
        Assert::same($this->versionsList->getDraftVersionCount(), $count);
    }
}
