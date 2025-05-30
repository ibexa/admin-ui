<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\BrowserContext;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Ibexa\AdminUi\Behat\Component\DraftConflictDialog;
use Ibexa\AdminUi\Behat\Page\ContentViewPage;
use Ibexa\Behat\Core\Behat\ArgumentParser;
use PHPUnit\Framework\Assert;

final class ContentViewContext implements Context
{
    private ArgumentParser $argumentParser;

    private ContentViewPage $contentViewPage;

    private DraftConflictDialog $draftConflictDialog;

    public function __construct(
        ArgumentParser $argumentParser,
        ContentViewPage $contentViewPage,
        DraftConflictDialog $draftConflictDialog
    ) {
        $this->argumentParser = $argumentParser;
        $this->contentViewPage = $contentViewPage;
        $this->draftConflictDialog = $draftConflictDialog;
    }

    /**
     * @Given I start creating a new Content :contentType
     * @Given I start creating a new Content :contentType in :language language
     */
    public function startCreatingContent(string $contentType, string $language = null): void
    {
        $this->contentViewPage->startCreatingContent($contentType, $language);
    }

    /**
     * @Given I am using the DXP with Focus mode disabled
     */
    public function disableFocusMode(): void
    {
        $this->contentViewPage->setFocusMode(false);
    }

    /**
     * @Given I am using the DXP in Focus mode
     */
    public function enableFocusMode(): void
    {
        $this->contentViewPage->setFocusMode(true);
    }

    /**
     * @Given I switch to :tab tab in Content structure
     */
    public function switchTab(string $tabName): void
    {
        $this->contentViewPage->switchToTab($tabName);
    }

    /**
     * @Given I add a new Location under :newLocationPath
     */
    public function iAddNewLocation(string $newLocationPath): void
    {
        $newLocationPath = $this->argumentParser->replaceRootKeyword($newLocationPath);
        $this->contentViewPage->addLocation($newLocationPath);
    }

    /**
     * @Given I add new translation :language without base translation
     * @Given I add new translation :language basing on :base translation
     */
    public function iAddNewTranslation(string $language, string $base = 'none'): void
    {
        $this->contentViewPage->addTranslation($language, $base);
    }

    /**
     * @Given I choose :language preview in Content View
     */
    public function iChoosePreview(string $language): void
    {
        $this->contentViewPage->choosePreview($language);
    }

    /**
     * @Given I start creating a new User
     * @Given I start creating a new User using :contentTypeName content type
     */
    public function startCreatingUser(string $contentTypeName = 'User'): void
    {
        $this->contentViewPage->startCreatingUser($contentTypeName);
    }

    /**
     * @Given I start editing the current content
     * @Given I start editing the current content in :language language
     */
    public function startEditingContent(string $language = null): void
    {
        $this->contentViewPage->editContent($language);
    }

    /**
     * @Then there's a :itemName :itemType on Subitems list
     */
    public function verifyThereIsItemInSubItemList(string $itemName, string $itemType): void
    {
        $this->contentViewPage->verifyIsLoaded();
        Assert::assertTrue($this->contentViewPage->isChildElementPresent(['Name' => $itemName, 'Content type' => $itemType]));
    }

    /**
     * @Then there's no :itemName :itemType on Subitems list
     */
    public function verifyThereIsNoItemInSubItemListInRoot(string $itemName, string $itemType): void
    {
        $this->contentViewPage->verifyIsLoaded();
        Assert::assertFalse($this->contentViewPage->isChildElementPresent(['Name' => $itemName, 'Content type' => $itemType]));
    }

    /**
     * @Then content attributes equal
     */
    public function contentAttributesEqual(TableNode $parameters): void
    {
        foreach ($parameters->getHash() as $fieldData) {
            $fieldLabel = $fieldData['label'];
            $fieldTypeIdentifier = $fieldData['fieldTypeIdentifier'] ?? null;
            $expectedFieldValues = $fieldData;
            $this->contentViewPage->verifyFieldHasValues($fieldLabel, $expectedFieldValues, $fieldTypeIdentifier);
        }
    }

    /**
     * @When I start creating new draft from draft conflict modal
     */
    public function startCreatingNewDraftFromDraftConflictModal(): void
    {
        $this->draftConflictDialog->verifyIsLoaded();
        $this->draftConflictDialog->createNewDraft();
    }

    /**
     * @When I start editing draft with version number :versionNumber from draft conflict modal
     */
    public function startEditingDraftFromDraftConflictModal(string $versionNumber): void
    {
        $this->draftConflictDialog->verifyIsLoaded();
        $this->draftConflictDialog->edit($versionNumber);
    }

    /**
     * @When I send content to trash
     */
    public function iSendContentToTrash(): void
    {
        $this->contentViewPage->sendToTrash();
    }
}
