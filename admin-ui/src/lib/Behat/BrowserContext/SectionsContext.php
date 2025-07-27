<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\BrowserContext;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Ibexa\AdminUi\Behat\Page\SectionPage;
use Ibexa\AdminUi\Behat\Page\SectionsPage;
use PHPUnit\Framework\Assert;

class SectionsContext implements Context
{
    private SectionPage $sectionPage;

    private SectionsPage $sectionsPage;

    public function __construct(SectionPage $sectionPage, SectionsPage $sectionsPage)
    {
        $this->sectionPage = $sectionPage;
        $this->sectionsPage = $sectionsPage;
    }

    /**
     * @When I create a new Section
     */
    public function createNewSection(): void
    {
        $this->sectionsPage->createNew();
    }

    /**
     * @When  I delete the section
     */
    public function deleteSection(): void
    {
        $this->sectionPage->delete();
    }

    /**
     * @Then content items list in section :sectionName contains items
     */
    public function sectionContainsProperContentItems(string $sectionName, TableNode $contentItems): void
    {
        $this->sectionPage->setExpectedSectionName($sectionName);

        foreach ($contentItems->getHash() as $contentItem) {
            $expectedName = $contentItem['Name'];
            $expectedContentType = $contentItem['content type'];
            $expectedPath = $contentItem['Path'];
            Assert::assertTrue(
                $this->sectionPage->hasAssignedItem(
                    ['Name' => $expectedName, 'Content type' => $expectedContentType, 'Path' => $expectedPath]
                )
            );
        }
    }

    /**
     * @Given there's no :sectionName on Sections list
     */
    public function thereSNoSectionOnSectionList(string $sectionName): void
    {
        Assert::assertFalse($this->sectionsPage->isSectionOnTheList($sectionName));
    }

    /**
     * @Given I start assigning to :sectionName from Sections page
     */
    public function iAssignContentItems(string $sectionName): void
    {
        $this->sectionsPage->assignContentItems($sectionName);
    }

    /**
     * @Given I start assigning to :sectionName Section
     */
    public function assignContentItems(): void
    {
        $this->sectionPage->assignContentItems();
    }

    /**
     * @Given I delete Section :sectionName
     */
    public function deleteSectionNamed(string $sectionName): void
    {
        $this->sectionsPage->deleteSection($sectionName);
    }

    /**
     * @Given there's a :sectionName on Sections list
     */
    public function thereASectionOnSectionList(string $sectionName): void
    {
        Assert::assertTrue($this->sectionsPage->isSectionOnTheList($sectionName));
    }

    /**
     * @Given the :sectionName on Sections list has no assigned Content Items
     */
    public function sectionOnSectionListHasNoAssignedContentItems(string $sectionName): void
    {
        Assert::assertEquals(0, $this->sectionsPage->getAssignedContentItemsCount($sectionName));
    }

    /**
     * @Given the :sectionName has no assigned Content Items
     */
    public function sectionHasNoAssignedContentItems(string $sectionName): void
    {
        $this->sectionPage->setExpectedSectionName($sectionName);
        Assert::assertEquals(0, $this->sectionPage->hasAssignedItems());
    }

    /**
     * @Then I should be on :sectionName Section page
     */
    public function iShouldBeOnSectionPage(string $sectionName): void
    {
        $this->sectionPage->setExpectedSectionName($sectionName);
        $this->sectionPage->verifyIsLoaded();
    }

    /**
     * @Then Content items list in is empty for Section
     */
    public function contentListIsEmty(): void
    {
        Assert::assertTrue($this->sectionPage->isContentListEmpty());
    }

    /**
     * @Then I start editing :sectionName from Sections list
     */
    public function editSectionFromSectionsList(string $sectionName): void
    {
        $this->sectionsPage->editSection($sectionName);
    }

    /**
     * @Then Section has proper attributes
     */
    public function sectionHasProperAttributes(TableNode $sectionData): void
    {
        $expectedSectionName = $sectionData->getHash()[0]['Name'];
        $expectedSectionIdentifier = $sectionData->getHash()[0]['Identifier'];

        Assert::assertTrue(
            $this->sectionPage->hasProperties(['Name' => $expectedSectionName, 'Identifier' => $expectedSectionIdentifier])
        );
    }

    /**
     * @Then I open :sectionName Section page in admin SiteAccess
     */
    public function openSectionPage(string $sectionName): void
    {
        $this->sectionPage->setExpectedSectionName($sectionName);
        $this->sectionPage->open('admin');
        $this->sectionPage->verifyIsLoaded();
    }

    /**
     * @Then I start editing the Section
     */
    public function editSection(): void
    {
        $this->sectionPage->edit();
    }

    /**
     * @Then the :sectionName on Sections list has assigned Content Items
     */
    public function sectionHasAssignedContentItems(string $sectionName): void
    {
        Assert::assertGreaterThan(0, $this->sectionsPage->getAssignedContentItemsCount($sectionName));
    }

    /**
     * @Then Section :sectionName cannot be selected
     */
    public function sectionCannotBeSelected(string $sectionName): void
    {
        Assert::assertFalse($this->sectionsPage->canBeSelected($sectionName));
    }
}
