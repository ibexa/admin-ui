<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Page;

use Behat\Mink\Session;
use Exception;
use Ibexa\AdminUi\Behat\Component\ContentActionsMenu;
use Ibexa\AdminUi\Behat\Component\IbexaDropdown;
use Ibexa\AdminUi\Behat\Component\UniversalDiscoveryWidget;
use Ibexa\Behat\Browser\Element\Action\MouseOverAndClick;
use Ibexa\Behat\Browser\Element\Criterion\ChildElementTextCriterion;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Behat\Browser\Routing\Router;

class RoleUpdatePage extends AdminUpdateItemPage
{
    private UniversalDiscoveryWidget $universalDiscoveryWidget;

    private IbexaDropdown $ibexaDropdown;

    public function __construct(Session $session, Router $router, ContentActionsMenu $contentActionsMenu, UniversalDiscoveryWidget $universalDiscoveryWidget, IbexaDropdown $ibexaDropdown)
    {
        parent::__construct($session, $router, $contentActionsMenu);
        $this->universalDiscoveryWidget = $universalDiscoveryWidget;
        $this->ibexaDropdown = $ibexaDropdown;
        $this->locators->replace(new VisibleCSSLocator('button', '.ibexa-edit-content__container button'));
    }

    public function selectLimitationValues(string $selectName, array $values): void
    {
        try {
            $currentlySelectedElementsCount = $this->getHTMLPage()
                ->findAll($this->getLocator('limitationField'))
                ->getByCriterion(new ChildElementTextCriterion($this->getLocator('labelSelector'), $selectName))
                ->findAll($this->getLocator('limitationDropdownOptionRemove'))
                ->count()
            ;

            for ($i = 0; $i < $currentlySelectedElementsCount; ++$i) {
                $this->getHTMLPage()
                    ->findAll($this->getLocator('limitationField'))
                    ->getByCriterion(new ChildElementTextCriterion($this->getLocator('labelSelector'), $selectName))
                    ->find($this->getLocator('limitationDropdownOptionRemove'))
                    ->click();
            }
        } catch (Exception $e) {
            // no need to remove current selection
        }

        $this->getHTMLPage()
            ->findAll($this->getLocator('limitationField'))
            ->getByCriterion(new ChildElementTextCriterion($this->getLocator('labelSelector'), $selectName))
            ->find($this->getLocator('limitationDropdown'))
            ->click();

        $this->ibexaDropdown->verifyIsLoaded();

        foreach ($values as $value) {
            $this->ibexaDropdown->selectOption($value);
        }

        $this->getHTMLPage()
            ->findAll($this->getLocator('limitationField'))
            ->getByCriterion(new ChildElementTextCriterion($this->getLocator('labelSelector'), $selectName))
            ->find($this->getLocator('limitationDropdown'))
            ->click();
    }

    public function specifyLocators(): array
    {
        return array_merge(
            parent::specifyLocators(),
            [
                new VisibleCSSLocator('limitationField', '.ibexa-update-policy__action-wrapper'),
                new VisibleCSSLocator('limitationDropdown', '.ibexa-dropdown__selection-info'),
                new VisibleCSSLocator('limitationDropdownOption', '.ibexa-dropdown-popover .ibexa-dropdown__items .ibexa-dropdown__item'),
                new VisibleCSSLocator('limitationDropdownOptionRemove', '.ibexa-dropdown__remove-selection'),
                new VisibleCSSLocator('labelSelector', '.ibexa-label'),
                new VisibleCSSLocator('policyAssignmentSelect', '#role_assignment_create_limitation_type_section'),
                new VisibleCSSLocator('ibexaDropdownSelectionInfo', 'div.ibexa-dropdown__wrapper > ul.ibexa-dropdown__selection-info'),
                new VisibleCSSLocator('newPolicySelectList', '#policy_create_policy'),
            ]
        );
    }

    public function assign(array $itemPaths, string $itemType): void
    {
        $itemTypeToLabelMapping = [
            'users' => 'Select Users',
            'groups' => 'Select User Groups',
        ];

        $this->clickButton($itemTypeToLabelMapping[$itemType]);
        $this->universalDiscoveryWidget->verifyIsLoaded();

        foreach ($itemPaths as $itemPath) {
            $this->universalDiscoveryWidget->selectContent($itemPath);
        }

        $this->universalDiscoveryWidget->confirm();
    }

    public function assignSectionLimitation(string $limitationName): void
    {
        $this->verifyIsLoaded();
        $this->switchToTab('Limitations');
        $this->fillFieldWithValue('Sections', true);
        $this->getHTMLPage()->find($this->getLocator('policyAssignmentSelect'))->click();
        $this->getHTMLPage()->find($this->getLocator('ibexaDropdownSelectionInfo'))->click();
        $this->ibexaDropdown->selectOption($limitationName);
    }

    public function selectLimitationForAssignment(string $itemPath): void
    {
        $this->verifyIsLoaded();
        $this->switchToTab('Limitations');
        $this->fillFieldWithValue('Subtree', 'true');
        $this->clickButton('Select path');
        $this->universalDiscoveryWidget->verifyIsLoaded();
        $this->universalDiscoveryWidget->selectContent($itemPath);
        $this->universalDiscoveryWidget->confirm();
    }

    public function selectSubtreeLimitationForPolicy(string $itemPath): void
    {
        $buttons = $this->getHTMLPage()
            ->findAll($this->getLocator('button'))
            ->filterBy(new ElementTextCriterion('Select Locations'))
            ->toArray();
        $buttons[1]->execute(new MouseOverAndClick());

        $this->universalDiscoveryWidget->verifyIsLoaded();
        $this->universalDiscoveryWidget->selectContent($itemPath);
        $this->universalDiscoveryWidget->confirm();
    }

    public function selectPolicy(string $policyName): void
    {
        $this->getHTMLPage()->find($this->getLocator('ibexaDropdownSelectionInfo'))->click();
        $this->ibexaDropdown->verifyIsLoaded();
        $this->ibexaDropdown->selectOption($policyName);
    }
}
