<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\BrowserContext;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Ibexa\AdminUi\Behat\Component\Fields\NonEditableField;
use Ibexa\AdminUi\Behat\Page\ContentUpdateItemPage;
use Ibexa\AdminUi\Behat\Page\UserUpdatePage;
use PHPUnit\Framework\Assert;

class ContentUpdateContext implements Context
{
    private ContentUpdateItemPage $contentUpdateItemPage;

    private UserUpdatePage $userUpdatePage;

    public function __construct(ContentUpdateItemPage $contentUpdateItemPage, UserUpdatePage $userUpdatePage)
    {
        $this->contentUpdateItemPage = $contentUpdateItemPage;
        $this->userUpdatePage = $userUpdatePage;
    }

    /**
     * @When I set content fields
     */
    public function iSetFields(TableNode $table): void
    {
        $this->contentUpdateItemPage->verifyIsLoaded();
        foreach ($table->getHash() as $row) {
            $values = $this->filterOutNonEmptyValues($row);
            $fieldPosition = array_key_exists('fieldPosition', $values) ? (int)$values['fieldPosition'] : null;
            $this->contentUpdateItemPage->fillFieldWithValue($row['label'], $values, $fieldPosition);
        }
    }

    /**
     * @When field :fieldName contains validation error :errorMessage
     */
    public function fieldContainsValidationError(string $fieldName, string $errorMessage): void
    {
        $this->contentUpdateItemPage->verifyValidationMessage($fieldName, $errorMessage);
    }

    /**
     * @Given the :fieldName field is noneditable
     */
    public function verifyFieldIsNotEditable(string $fieldName): void
    {
        $field = $this->contentUpdateItemPage->getField($fieldName);
        Assert::assertEquals(NonEditableField::EXPECTED_NON_EDITABLE_TEXT, $field->getValue()[0]);
    }

    /**
     * @When the :fieldName field cannot be edited due to limitation
     */
    public function fieldCannotBeEditedDueToLimitation(string $fieldName): void
    {
        $this->contentUpdateItemPage->verifyFieldCannotBeEditedDueToLimitation($fieldName);
    }

    /**
     * @When I set content fields for user
     */
    public function iSetFieldsForUser(TableNode $table): void
    {
        $this->userUpdatePage->verifyIsLoaded();
        foreach ($table->getHash() as $row) {
            $values = $this->filterOutNonEmptyValues($row);
            $this->userUpdatePage->fillFieldWithValue($row['label'], $values);
        }
    }

    /**
     * @When I select :contentPath from Image Asset Repository for :fieldName field
     */
    public function selectContentFromIARepository(string $contentPath, string $fieldName): void
    {
        $this->contentUpdateItemPage->getField($fieldName)->selectFromRepository($contentPath);
    }

    private function filterOutNonEmptyValues(array $parameters): array
    {
        $values = $parameters;
        unset($values['label']);

        return array_filter($values, static function ($element): bool { return !empty($element) || $element === 0;});
    }

    /**
     * @Then content fields are set
     */
    public function verifyFieldsAreSet(TableNode $table): void
    {
        foreach ($table->getHash() as $row) {
            $this->contentUpdateItemPage->verifyFieldHasValue($row['label'], $row);
        }
    }

    /**
     * @When I switch to :tabName field group
     */
    public function iSwitchToContentTab(string $tabName): void
    {
        $this->contentUpdateItemPage->verifyIsLoaded();
        $this->contentUpdateItemPage->switchToFieldGroup($tabName);
    }

    /**
     * @When I switch to :tabName field tab
     */
    public function iSwitchToContentGroup(string $tabName): void
    {
        $this->contentUpdateItemPage->verifyIsLoaded();
        $this->contentUpdateItemPage->switchToFieldTab($tabName);
    }

    /**
     * @When I wait for Content Item to be autosaved
     */
    public function iWaitForAutosaveNotification(): void
    {
        $this->contentUpdateItemPage->verifyIsLoaded();
        $this->contentUpdateItemPage->verifyAutosaveNotificationIsDisplayed();
        $this->contentUpdateItemPage->verifyAutosaveDraftIsSavedNotificationIsDisplayed();
    }

    /**
     * @When I check if "Autosave is off, draft not created" notification is displayed
     */
    public function iCheckAutosaveNotification(): void
    {
        $this->contentUpdateItemPage->verifyIsLoaded();
        $this->contentUpdateItemPage->verifyAutosaveIsOffNotificationIsDisplayed();
    }
}
