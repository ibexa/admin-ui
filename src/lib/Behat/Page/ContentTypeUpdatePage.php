<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Page;

use Ibexa\Behat\Browser\Element\Condition\ElementExistsCondition;
use Ibexa\Behat\Browser\Element\Condition\ElementNotExistsCondition;
use Ibexa\Behat\Browser\Element\Condition\ElementsCountCondition;
use Ibexa\Behat\Browser\Element\Condition\ElementTransitionHasEndedCondition;
use Ibexa\Behat\Browser\Element\Criterion\ElementAttributeCriterion;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Element\Mapper\ElementTextMapper;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

class ContentTypeUpdatePage extends AdminUpdateItemPage
{
    public function fillFieldDefinitionFieldWithValue(string $fieldName, string $label, string $value)
    {
        $this->expandLastFieldDefinition();
        $this->getHTMLPage()
            ->find($this->getLocator('fieldDefinitionOpenContainer'))
            ->findAll($this->getLocator('field'))->getByCriterion(new ElementTextCriterion($label))
            ->find($this->getLocator('fieldInput'))
            ->setValue($value);
    }

    public function expandLastFieldDefinition(): void
    {
        $fieldDefinitionLocator = new VisibleCSSLocator(
            'lastFieldDefinition',
            'div.ibexa-collapse__body-content div.ibexa-collapse--field-definition'
        );

        $this->getHTMLPage()->setTimeout(10)->waitUntil(function () use ($fieldDefinitionLocator): bool {
            $fieldDefinition = $this->getHTMLPage()->findAll($fieldDefinitionLocator)->last();
            $fieldDefinition->click();
            $this->getHTMLPage()->setTimeout(3)->waitUntilCondition(
                new ElementNotExistsCondition(
                    $fieldDefinition,
                    new VisibleCSSLocator('isCollapsed', 'button.collapsed')
                )
            );

            return true;
        }, 'Error expanding the last Field definition');

        $lastFieldDefinition = $this->getHTMLPage()->findAll($fieldDefinitionLocator)->last();
        $this->getHTMLPage()->setTimeout(10)->waitUntilCondition(new ElementTransitionHasEndedCondition($lastFieldDefinition, new VisibleCSSLocator('transition', 'div')));
    }

    public function specifyLocators(): array
    {
        return array_merge(parent::specifyLocators(), [
            new VisibleCSSLocator('fieldDefinition', '.ibexa-collapse--field-definition'),
            new VisibleCSSLocator('field', '.form-group'),
            new VisibleCSSLocator('contentTypeAddButton', '.ibexa-content-type-edit__add-field-definitions-group-btn'),
            new VisibleCSSLocator('contentTypeCategoryList', ' div.ibexa-content-type-edit__add-field-definitions-group > ul > li:nth-child(n):not(.ibexa-popup-menu__item-action--disabled)'),
            new VisibleCSSLocator('availableFieldLabelList', '.ibexa-available-field-types__list > li:not(.ibexa-available-field-type--hidden)'),
            new VisibleCSSLocator('workspace', '.ibexa-collapse__body-content'),
            new VisibleCSSLocator('fieldDefinitionToggle', '.ibexa-collapse:nth-last-child(2) > div.ibexa-collapse__header > button:last-child:not([data-bs-target="#content_collapse"])'),
            new VisibleCSSLocator('selectLaunchEditorMode', '.form-check .ibexa-input--radio'),
            new VisibleCSSLocator('fieldDefinitionOpenContainer', '[data-collapsed="false"] .ibexa-content-type-edit__field-definition-content'),
            new VisibleCSSLocator('selectBlocksDropdown', '.ibexa-page-select-items__toggler'),
            new VisibleCSSLocator('fieldDefinitionSearch', '.ibexa-available-field-types__sidebar-filter'),
        ]);
    }

    public function addFieldDefinition(string $fieldName)
    {
        $currentFieldDefinitionCount = $this->getHTMLPage()->findAll($this->getLocator('fieldDefinition'))->count();
        $this->getHTMLPage()->find($this->getLocator('fieldDefinitionSearch'))->setValue($fieldName);
        $fieldPosition = array_search($fieldName, $this->getHTMLPage()->findAll($this->getLocator('availableFieldLabelList'))->mapBy(new ElementTextMapper()), true) + 1;
        $fieldSelector = new VisibleCSSLocator(
            'field',
            sprintf(
                '.ibexa-available-field-types__list > li:not(.ibexa-available-field-type--hidden) div.ibexa-available-field-type__content:nth-of-type(%d)',
                $fieldPosition
            )
        );

        $this->getHTMLPage()->setTimeout(10)->waitUntil(function () use ($fieldSelector): bool {
            $this->getHTMLPage()->setTimeout(0)->find($fieldSelector)->mouseOver();
            $this->getHTMLPage()
                ->setTimeout(0)
                ->waitUntilCondition(
                    new ElementTransitionHasEndedCondition(
                        $this->getHTMLPage(),
                        $fieldSelector
                    )
                );

            return true;
        }, 'Error hovering over the Field Definition to add');

        $fieldScript = sprintf("document.querySelector('%s')", $fieldSelector->getSelector());
        $workspaceScript = sprintf("document.querySelector('%s')", $this->getLocator('workspace')->getSelector());
        $this->getHTMLPage()->dragAndDrop($fieldScript, $workspaceScript, $workspaceScript);

        $this->getHTMLPage()->setTimeout(3)->waitUntilCondition(new ElementsCountCondition($this->getHTMLPage(), $this->getLocator('fieldDefinition'), $currentFieldDefinitionCount + 1));

        usleep(1500000); //TODO: add proper wait condition
    }

    public function clickAddButton(): void
    {
        $this->getHTMLPage()->find($this->getLocator('contentTypeAddButton'))->mouseOver();
        usleep(100 * 5000); // 500ms
        $this->getHTMLPage()->find($this->getLocator('contentTypeAddButton'))->click();
        $this->getHTMLPage()
            ->setTimeout(3)
            ->waitUntilCondition(
                new ElementExistsCondition($this->getHTMLPage(), $this->getLocator('contentTypeCategoryList'))
            );
        $this->getHTMLPage()->find($this->getLocator('contentTypeCategoryList'))->mouseOver();
    }

    public function selectContentTypeCategory(string $categoryName): void
    {
        $categoryLocator = $this->getLocator('contentTypeCategoryList');
        $listElement = $this->getHTMLPage()
            ->findAll($categoryLocator)
            ->getByCriterion(new ElementTextCriterion($categoryName));
        $listElement->mouseOver();
        $listElement->click();
    }

    public function expandDefaultBlocksOption(): void
    {
        $dropdownLocator = $this->getLocator('selectBlocksDropdown');
        $this->getHTMLPage()
            ->setTimeout(3)
            ->waitUntilCondition(
                new ElementExistsCondition($this->getHTMLPage(), $dropdownLocator)
            );
        $this->getHTMLPage()
            ->findAll($dropdownLocator)->getByCriterion(new ElementTextCriterion('Select blocks'))->click();
        $this->getHTMLPage()
            ->findAll($dropdownLocator)->getByCriterion(new ElementTextCriterion('default'))->click();
    }

    public function selectBlock(string $blockName): void
    {
        $blockFindingScript = "document.querySelector('.ibexa-page-select-items__item .form-check .form-check-input[value=\'%s\']').click()";
        $scriptToExecute = sprintf($blockFindingScript, $blockName);
        $this->getSession()->executeScript($scriptToExecute);
    }

    public function verifyIsLoaded(): void
    {
        parent::verifyIsLoaded();
        $this->getHTMLPage()->find($this->getLocator('contentTypeAddButton'))->assert()->isVisible();
    }

    public function selectEditorLaunchMode(string $viewMode): void
    {
        $this->getHTMLPage()
             ->findAll($this->getLocator('selectLaunchEditorMode'))
             ->getByCriterion(new ElementAttributeCriterion('value', $viewMode))->click();
    }
}
