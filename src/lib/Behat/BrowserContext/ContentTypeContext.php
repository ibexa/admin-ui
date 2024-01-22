<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\BrowserContext;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Ibexa\AdminUi\Behat\Page\ContentTypeGroupPage;
use Ibexa\AdminUi\Behat\Page\ContentTypeGroupsPage;
use Ibexa\AdminUi\Behat\Page\ContentTypePage;
use Ibexa\AdminUi\Behat\Page\ContentTypeUpdatePage;
use PHPUnit\Framework\Assert;

class ContentTypeContext implements Context
{
    /** @var \Ibexa\AdminUi\Behat\Page\ContentTypePage */
    private $contentTypePage;

    /** @var \Ibexa\AdminUi\Behat\Page\ContentTypeUpdatePage */
    private $contentTypeUpdatePage;

    /** @var \Ibexa\AdminUi\Behat\Page\ContentTypeGroupPage */
    private $contentTypeGroupPage;

    /** @var \Ibexa\AdminUi\Behat\Page\ContentTypeGroupsPage */
    private $contentTypeGroupsPage;

    public function __construct(
        ContentTypePage $contentTypePage,
        ContentTypeUpdatePage $contentTypeUpdatePage,
        ContentTypeGroupPage $contentTypeGroupPage,
        ContentTypeGroupsPage $contentTypeGroupsPage
    ) {
        $this->contentTypePage = $contentTypePage;
        $this->contentTypeUpdatePage = $contentTypeUpdatePage;
        $this->contentTypeGroupPage = $contentTypeGroupPage;
        $this->contentTypeGroupsPage = $contentTypeGroupsPage;
    }

    /**
     * @Then content type has proper Global properties
     */
    public function contentTypeHasProperGlobalProperties(TableNode $table): void
    {
        foreach ($table->getHash() as $row) {
            Assert::assertTrue($this->contentTypePage->hasProperty($row['label'], $row['value']));
        }
    }

    /**
     * @When I create a new content type
     */
    public function createNewContentType(): void
    {
        $this->contentTypeGroupPage->createNew();
    }

    /**
     * @When I create a new content type group
     */
    public function createNewContentTypeGroup(): void
    {
        $this->contentTypeGroupsPage->createNew();
    }

    /**
     * @Then content type :contentTypeName has proper fields
     */
    public function contentTypeHasProperFields(TableNode $table): void
    {
        foreach ($table->getHash() as $row) {
            Assert::assertTrue($this->contentTypePage->hasFieldType(
                ['Name' => $row['fieldName'], 'Type' => $row['fieldType']]
            ));
        }
    }

    /**
     * @Given there's no :contentTypeName on content types list
     */
    public function thereSNoOnContentTypesList($contentTypeName)
    {
        Assert::assertFalse($this->contentTypeGroupPage->isContentTypeOnTheList($contentTypeName));
    }

    /**
     * @Given there's no :contentTypeGroupName content type group on content type groups list
     */
    public function thereSNoOnContentTypesGroupList($contentTypeGroupName)
    {
        Assert::assertFalse($this->contentTypeGroupsPage->isContentTypeGroupOnTheList($contentTypeGroupName));
    }

    /**
     * @Given there's a :contentTypeGroupName content type group on content type groups list
     */
    public function thereSAOnContentTypesGroupList($contentTypeGroupName)
    {
        Assert::assertTrue($this->contentTypeGroupsPage->isContentTypeGroupOnTheList($contentTypeGroupName));
    }

    /**
     * @Given there's a :contentTypeName on content types list
     */
    public function thereAContentTypeOnContentTypesList($contentTypeName)
    {
        Assert::assertTrue($this->contentTypeGroupPage->isContentTypeOnTheList($contentTypeName));
    }

    /**
     * @When I select :categoryName category to content type definition
     */
    public function iSelectCategory(string $categoryName): void
    {
        $this->contentTypeUpdatePage->verifyIsLoaded();
        $this->contentTypeUpdatePage->clickAddButton();
        $this->contentTypeUpdatePage->selectContentTypeCategory($categoryName);
    }

    /**
     * @When I add field :fieldName to content type definition
     */
    public function iAddField(string $fieldName): void
    {
        $this->contentTypeUpdatePage->addFieldDefinition($fieldName);
    }

    /**
     * @When I set :field to :value for :fieldName field
     */
    public function iSetFieldDefinitionData(string $label, string $value, string $fieldName): void
    {
        $this->contentTypeUpdatePage->fillFieldDefinitionFieldWithValue($fieldName, $label, $value);
    }

    /**
     * @When I start editing content type :contentTypeName
     */
    public function iStartEditingItem(string $contentTypeName): void
    {
        $this->contentTypeGroupPage->edit($contentTypeName);
    }

    /**
     * @When I start editing content type group :contentTypeGroupName
     */
    public function iStartEditingContentTypeGroup(string $contentTypeGroupName): void
    {
        $this->contentTypeGroupsPage->edit($contentTypeGroupName);
    }

    /**
     * @When I delete :contentTypeName content type
     */
    public function iDeleteContentType(string $contentTypeName)
    {
        $this->contentTypeGroupPage->delete($contentTypeName);
    }

    /**
     * @When I delete :contentTypeGroupName from content type groups
     */
    public function iDeleteContentTypeGroup(string $contentTypeGroupName)
    {
        $this->contentTypeGroupsPage->delete($contentTypeGroupName);
    }

    /**
     * @Given I'm on content type Page for :contentTypeGroup group
     */
    public function iMOnContentTypePageFor(string $contentTypeGroup)
    {
        $this->contentTypeGroupPage->setExpectedContentTypeGroupName($contentTypeGroup);
        $this->contentTypeGroupPage->open('admin');
        $this->contentTypeGroupPage->verifyIsLoaded();
    }

    /**
     * @Then I should be on content type group page for :contentTypeGroup group
     */
    public function iShouldBeOnContentTypeGroupPage($contentTypeGroup)
    {
        $this->contentTypeGroupPage->setExpectedContentTypeGroupName($contentTypeGroup);
        $this->contentTypeGroupPage->verifyIsLoaded();
    }

    /**
     * @Then I should be on content type page for :contentTypeName
     */
    public function iShouldBeOnContentTypePage(string $contentTypeName)
    {
        $this->contentTypePage->setExpectedContentTypeName($contentTypeName);
        $this->contentTypePage->verifyIsLoaded();
    }

    /**
     * @Then there're no content types for that group
     */
    public function thereAreNoContentTypes()
    {
        Assert::assertFalse($this->contentTypeGroupPage->hasContentTypes());
    }

    /**
     * @Then there's an empty :contentTypeGroupName content type group on content type groups list
     */
    public function thereIsAnEmptyContentTypeGroup(string $contentTypeGroupName)
    {
        Assert::assertFalse($this->contentTypeGroupPage->hasAssignedContentItems($contentTypeGroupName));
    }

    /**
     * @Then there's non-empty :contentTypeGroupName content type group on content type groups list
     */
    public function thereIsANonEmptyContentTypeGroup(string $contentTypeGroupName)
    {
        Assert::assertTrue($this->contentTypeGroupPage->hasAssignedContentItems($contentTypeGroupName));
    }

    /**
     * @Then content type group :contentTypeGroupName cannot be selected
     */
    public function contentTypeGroupCannotBeSelected(string $contentTypeGroupName)
    {
        Assert::assertFalse($this->contentTypeGroupsPage->canBeSelected($contentTypeGroupName));
    }

    /**
     * @Given I check :blockName block in ezlandingpage field blocks section
     */
    public function iCheckBlockInField($blockName)
    {
        $this->contentTypeUpdatePage->verifyIsLoaded();
        $this->contentTypeUpdatePage->expandLastFieldDefinition('fieldDefinitionOpenContainerEdit');
        $this->contentTypeUpdatePage->expandDefaultBlocksOption();
        $this->contentTypeUpdatePage->selectBlock($blockName);
    }

    /**
     * @Given I select :viewMode editor launch mode in ezlandingpage field options
     */
    public function iCheckEditorLaunchModeOption(string $viewMode): void
    {
        $this->contentTypeUpdatePage->verifyIsLoaded();
        $this->contentTypeUpdatePage->expandLastFieldDefinition();
        $this->contentTypeUpdatePage->selectEditorLaunchMode($viewMode);
    }
}
