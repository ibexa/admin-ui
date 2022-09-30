<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Page;

use Behat\Mink\Session;
use Ibexa\AdminUi\Behat\Component\ContentActionsMenu;
use Ibexa\AdminUi\Behat\Component\Fields\FieldTypeComponent;
use Ibexa\AdminUi\Behat\Component\Notification;
use Ibexa\Behat\Browser\Element\Condition\ElementExistsCondition;
use Ibexa\Behat\Browser\Element\Condition\ElementHasTextCondition;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextFragmentCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Behat\Browser\Page\Page;
use Ibexa\Behat\Browser\Routing\Router;
use PHPUnit\Framework\Assert;
use Traversable;

class ContentUpdateItemPage extends Page
{
    /** @var \Ibexa\AdminUi\Behat\Component\ContentActionsMenu */
    private $contentActionsMenu;

    private $pageTitle;

    /** @var \Ibexa\AdminUi\Behat\Component\Fields\FieldTypeComponent[] */
    protected $fieldTypeComponents;

    /** @var \Ibexa\AdminUi\Behat\Component\Notification */
    private $notification;

    public function __construct(
        Session $session,
        Router $router,
        ContentActionsMenu $contentActionsMenu,
        iterable $fieldTypeComponents,
        Notification $notification
    ) {
        parent::__construct($session, $router);
        $this->contentActionsMenu = $contentActionsMenu;
        $this->fieldTypeComponents = $fieldTypeComponents;
        $this->notification = $notification;
    }

    public function verifyIsLoaded(): void
    {
        if ($this->pageTitle !== null) {
            Assert::assertEquals(
                $this->pageTitle,
                $this->getHTMLPage()
                    ->setTimeout(10)
                    ->find($this->getLocator('pageTitle'))->getText()
            );
        }
        $this->getHTMLPage()->setTimeout(10)->find($this->getLocator('formElement'))->assert()->isVisible();
        $this->contentActionsMenu->verifyIsLoaded();

        // close notification about new draft created successfully if it's still visible
        if ($this->notification->isVisible()) {
            $this->notification->verifyAlertSuccess();
            $this->notification->closeAlert();
        }
    }

    public function setExpectedPageTitle(string $title): void
    {
        $this->pageTitle = $title;
    }

    public function getName(): string
    {
        return 'Content Update';
    }

    public function fillFieldWithValue(string $label, array $value): void
    {
        $this->getField($label)->setValue($value);
    }

    public function close(): void
    {
        $this->getHTMLPage()->setTimeout(3)
            ->waitUntilCondition(new ElementExistsCondition($this->getHTMLPage(), $this->getLocator('closeButton')));
        $this->getHTMLPage()->find($this->getLocator('closeButton'))->click();
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('pageTitle', '.ibexa-edit-header__title'),
            new VisibleCSSLocator('formElement', 'form.ibexa-form, .ibexa-edit-content'),
            new VisibleCSSLocator('closeButton', '.ibexa-anchor-navigation-menu__close'),
            new VisibleCSSLocator('nthField', 'div.ibexa-field-edit:nth-of-type(%s)'),
            new VisibleCSSLocator('fieldGroupNthField', '[data-id="%s"] div.ibexa-field-edit:nth-of-type(%s)'),
            new VisibleCSSLocator('noneditableFieldClass', 'ibexa-field-edit--eznoneditable'),
            new VisibleCSSLocator('fieldOfType', '.ibexa-field-edit--%s'),
            new VisibleCSSLocator('navigationTabs', '.ibexa-anchor-navigation-menu__sections-item-btn'),
            new VisibleCSSLocator('autosaveIsOnInfo', '.ibexa-autosave__status-on'),
            new VisibleCSSLocator('autosaveSavedInfo', '.ibexa-autosave__status-saved'),
            new VisibleCSSLocator('autosaveIsOffInfo', '.ibexa-autosave__status-off'),
            new VisibleCSSLocator('section', '[data-id="%1$s"] .ibexa-field-edit .ibexa-field-edit__label, [data-id="%1$s"] .ibexa-field-edit--eznoneditable .ibexa-label'),
            new VisibleCSSLocator('fieldLabel', ' .ibexa-field-edit .ibexa-field-edit__label, .ibexa-field-edit--eznoneditable .ibexa-label'),
        ];
    }

    protected function getRoute(): string
    {
        throw new \Exception('This page cannot be opened on its own!');
    }

    public function getField(string $fieldName): FieldTypeComponent
    {
        $fieldLocator = new VisibleCSSLocator('', sprintf($this->getLocator('nthField')->getSelector(), $this->getFieldPosition($fieldName)));
        $fieldTypeIdentifier = $this->getFieldtypeIdentifier($fieldLocator, $fieldName);

        foreach ($this->fieldTypeComponents as $fieldTypeComponent) {
            if ($fieldTypeComponent->getFieldTypeIdentifier() === $fieldTypeIdentifier) {
                $fieldTypeComponent->setParentLocator($fieldLocator);

                return $fieldTypeComponent;
            }
        }
    }

    protected function getFieldPosition(string $fieldName): int
    {
        $activeSections = $this->getHTMLPage()
            ->setTimeout(0)
            ->findAll(new VisibleCSSLocator('activeSection', '.ibexa-anchor-navigation-menu__sections-item-btn--active'));
        $fieldLabelLocator = $activeSections->any() ?
            new VisibleCSSLocator(
                'fieldLabelWithCategories',
                sprintf(
                    $this->getLocator('section')->getSelector(),
                    $activeSections->single()->getAttribute('data-target-id')
                )
            ) :
            $this->getLocator('fieldLabel');

        $fieldElements = $this->getHTMLPage()->setTimeout(5)->findAll($fieldLabelLocator);

        $foundFields = [];
        foreach ($fieldElements as $fieldPosition => $fieldElement) {
            $fieldText = $fieldElement->getText();
            $foundFields[] = $fieldText;
            if ($fieldText === $fieldName) {
                // +1 because CSS is 1-indexed and arrays are 0-indexed
                return $fieldPosition + 1;
            }
        }

        Assert::fail(sprintf('Field %s not found. Found: %s', $fieldName, implode(',', $foundFields)));
    }

    public function verifyFieldHasValue(string $label, array $fieldData): void
    {
        $this->getField($label)->verifyValueInEditView($fieldData);
    }

    private function getFieldtypeIdentifier(VisibleCSSLocator $fieldLocator, string $fieldName): string
    {
        $isEditable = !$this->getHTMLPage()
            ->find($fieldLocator)
            ->hasClass($this->getLocator('noneditableFieldClass')->getSelector());

        if (!$isEditable) {
            return strtolower($fieldName);
        }

        $fieldClass = $this->getHTMLPage()->find($fieldLocator)->getAttribute('class');
        preg_match('/ibexa-field-edit--[ez|ibexa][a-z_]*/', $fieldClass, $matches);

        return explode('--', $matches[0])[1];
    }

    public function switchToFieldGroup(string $tabName): void
    {
        $this->getHTMLPage()->setTimeout(3)
            ->findAll($this->getLocator('navigationTabs'))
            ->getByCriterion(new ElementTextCriterion($tabName))
            ->click();
        $this->getHTMLPage()
            ->setTimeout(10)
            ->waitUntilCondition(new ElementHasTextCondition($this->getHTMLPage(), new VisibleCSSLocator('activeSection', '.ibexa-anchor-navigation-menu__sections-item-btn--active'), $tabName));
    }

    public function verifyFieldCannotBeEditedDueToLimitation(string $fieldName)
    {
        $activeSections = $this->getHTMLPage()->findAll(new VisibleCSSLocator('activeSection', '.ibexa-anchor-navigation-menu__sections-item-btn--active'));
        $fieldLocator = new VisibleCSSLocator('', sprintf($this
            ->getLocator('fieldGroupNthField')->getSelector(), $activeSections->single()->getAttribute('data-target-id'), $this->getFieldPosition($fieldName)));
        $this->getHTMLPage()->find($fieldLocator)->assert()->hasClass('ibexa-field-edit--disabled');
    }

    public function verifyAutosaveNotificationIsDisplayed(): void
    {
        $this->getHTMLPage()
            ->find($this->getLocator('autosaveIsOnInfo'))
            ->assert()->textContains('Autosave is on');
    }

    public function verifyAutosaveDraftIsSavedNotificationIsDisplayed(): void
    {
        $iteration_count = 30;

        while ($iteration_count > 0) {
            if ($this->isAutosaveDraftSavedNotificationVisible()) {
                return;
            }
            usleep(500000);
            --$iteration_count;
        }
        Assert::fail('Draft has not been autosaved for 15 seconds');
    }

    public function isAutosaveDraftSavedNotificationVisible(): bool
    {
        return $this->getHTMLPage()
            ->setTimeout(0)
            ->findAll($this->getLocator('autosaveSavedInfo'))
            ->filterBy(new ElementTextFragmentCriterion('Draft saved'))->any();
    }

    public function verifyAutosaveIsOffNotificationIsDisplayed(): void
    {
        $this->getHTMLPage()
            ->find($this->getLocator('autosaveIsOffInfo'))
            ->assert()->textContains('Autosave is off');
    }
}
