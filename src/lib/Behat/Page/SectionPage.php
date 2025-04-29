<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Page;

use Behat\Mink\Session;
use Ibexa\AdminUi\Behat\Component\Dialog;
use Ibexa\AdminUi\Behat\Component\Table\TableBuilder;
use Ibexa\AdminUi\Behat\Component\Table\TableInterface;
use Ibexa\Behat\Browser\Element\Condition\ElementExistsCondition;
use Ibexa\Behat\Browser\Element\Criterion\ChildElementTextCriterion;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Behat\Browser\Page\Page;
use Ibexa\Behat\Browser\Routing\Router;
use Ibexa\Contracts\Core\Repository\Repository;

class SectionPage extends Page
{
    private ?string $expectedSectionName = null;

    private int $expectedSectionId;

    private TableInterface $contentItemsTable;

    private Dialog $dialog;

    private Repository $repository;

    public function __construct(
        Session $session,
        Router $router,
        TableBuilder $tableBuilder,
        Dialog $dialog,
        Repository $repository
    ) {
        parent::__construct($session, $router);
        $this->contentItemsTable = $tableBuilder->newTable()->withParentLocator($this->getLocator('contentItemsTable'))->build();
        $this->dialog = $dialog;
        $this->repository = $repository;
    }

    public function isContentListEmpty(): bool
    {
        return $this->contentItemsTable->isEmpty();
    }

    public function hasProperties(array $sectionProperties): bool
    {
        foreach ($sectionProperties as $label => $value) {
            $isExpectedValuePresent = $this->getHTMLPage()
                    ->findAll($this->getLocator('sectionPropertiesItem'))
                    ->getByCriterion(new ChildElementTextCriterion($this->getLocator('sectionPropertiesLabel'), $label))
                    ->find($this->getLocator('sectionPropertiesValue'))
                    ->getText() === $value;

            if (!$isExpectedValuePresent) {
                return false;
            }
        }

        return true;
    }

    public function hasAssignedItem(array $elementData): bool
    {
        return $this->contentItemsTable->hasElement($elementData);
    }

    public function edit(): void
    {
        $this->getHTMLPage()
            ->findAll($this->getLocator('button'))
            ->getByCriterion(new ElementTextCriterion('Edit'))
            ->click();
    }

    public function assignContentItems(): void
    {
        $this->getHTMLPage()->find($this->getLocator('assignButton'))->click();
    }

    public function hasAssignedItems(): bool
    {
        return !$this->contentItemsTable->isEmpty();
    }

    public function delete(): void
    {
        $this->getHTMLPage()
            ->findAll($this->getLocator('button'))
            ->getByCriterion(new ElementTextCriterion('Delete'))
            ->click();
        $this->dialog->verifyIsLoaded();
        $this->dialog->confirm();
    }

    protected function getRoute(): string
    {
        return sprintf(
            '/section/view/%d',
            $this->expectedSectionId
        );
    }

    public function setExpectedSectionName(string $sectionName): void
    {
        $this->expectedSectionName = $sectionName;

        $sections = $this->repository->sudo(static function (Repository $repository): iterable {
            return $repository->getSectionService()->loadSections();
        });

        foreach ($sections as $section) {
            if ($section->name === $sectionName) {
                $this->expectedSectionId = $section->id;

                return;
            }
        }
    }

    public function verifyIsLoaded(): void
    {
        $this->getHTMLPage()
            ->setTimeout(3)
            ->waitUntilCondition(new ElementExistsCondition($this->getHTMLPage(), $this->getLocator('contentItemsTable')))
            ->find($this->getLocator('pageTitle'))
            ->assert()->textEquals($this->expectedSectionName);
    }

    public function getName(): string
    {
        return 'Section';
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('pageTitle', '.ibexa-page-title h1'),
            new VisibleCSSLocator('contentItemsTable', '.ibexa-main-container__content-column .ibexa-table'),
            new VisibleCSSLocator('assignButton', '#section_content_assign_locations_select_content'),
            new VisibleCSSLocator('sectionInfoTable', '.ibexa-container .ibexa-table'),
            new VisibleCSSLocator('button', '.ibexa-btn'),
            new VisibleCSSLocator('sectionPropertiesItem', '.ibexa-details__item'),
            new VisibleCSSLocator('sectionPropertiesLabel', '.ibexa-details__item-label'),
            new VisibleCSSLocator('sectionPropertiesValue', '.ibexa-details__item-content'),
        ];
    }
}
