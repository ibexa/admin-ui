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
use Ibexa\Behat\API\Facade\ContentFacade;
use Ibexa\Behat\Browser\Element\Condition\ElementHasTextCondition;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextCriterion;
use Ibexa\Behat\Browser\Element\Criterion\ElementTextFragmentCriterion;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;
use Ibexa\Behat\Browser\Page\Page;
use Ibexa\Behat\Browser\Routing\Router;
use InvalidArgumentException;
use PHPUnit\Framework\Assert;
use RuntimeException;

class ContentUpdateItemPage extends Page
{
    private ?string $pageTitle = null;

    private string $languageCode;

    private string $contentTypeIdentifier;

    private string $locationPath;

    /**
     * @param \Ibexa\AdminUi\Behat\Component\Fields\FieldTypeComponent[] $fieldTypeComponents
     */
    public function __construct(
        readonly Session $session,
        readonly Router $router,
        private readonly ContentActionsMenu $contentActionsMenu,
        protected readonly iterable $fieldTypeComponents,
        private readonly Notification $notification,
        private readonly ContentFacade $contentFacade
    ) {
        parent::__construct($session, $router);
    }

    public function verifyIsLoaded(): void
    {
        if ($this->pageTitle !== null) {
            Assert::assertEquals(
                $this->pageTitle,
                $this->getHTMLPage()
                    ->setTimeout(20)
                    ->find($this->getLocator('pageTitle'))->getText()
            );
        }
        $this->getHTMLPage()
            ->setTimeout(20)
            ->find($this->getLocator('formElement'))
            ->assert()->isVisible();
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

    /**
     * @param array<string, mixed> $value
     */
    public function fillFieldWithValue(string $label, array $value, ?int $fieldPosition = null): void
    {
        $this->getField($label, $fieldPosition)->setValue($value);
    }

    public function verifyValidationMessage(string $fieldName, string $expectedMessage): void
    {
        $this->getField($fieldName)->verifyValidationMessage($expectedMessage);
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('pageTitle', '.ibexa-edit-header__title'),
            new VisibleCSSLocator('formElement', 'form.ibexa-form, .ibexa-edit-content'),
            new VisibleCSSLocator('nthField', 'div.ibexa-field-edit:nth-of-type(%s)'),
            new VisibleCSSLocator('nthFieldWithSection', '[data-id="%s"] div.ibexa-field-edit:nth-of-type(%s)'),
            new VisibleCSSLocator('fieldGroupNthField', '[data-id="%s"] div.ibexa-field-edit:nth-of-type(%s)'),
            new VisibleCSSLocator('noneditableFieldClass', 'ibexa-field-edit--eznoneditable'),
            new VisibleCSSLocator('fieldOfType', '.ibexa-field-edit--%s'),
            new VisibleCSSLocator('navigationTabs', '.ibexa-anchor-navigation-menu__sections-item-btn'),
            new VisibleCSSLocator('navigationGroups', 'li.nav-item'),
            new VisibleCSSLocator('autosaveIsOnInfo', '.ibexa-autosave__status--on'),
            new VisibleCSSLocator('autosaveSavedInfo', '.ibexa-autosave__status--saved'),
            new VisibleCSSLocator('autosaveIsOffInfo', '.ibexa-autosave__status--off'),
            new VisibleCSSLocator('section', '[data-id="%1$s"] .ibexa-field-edit .ibexa-field-edit__label, [data-id="%1$s"] .ibexa-field-edit--eznoneditable .ibexa-label'),
            new VisibleCSSLocator('fieldLabel', ' .ibexa-field-edit .ibexa-field-edit__label, .ibexa-field-edit--eznoneditable .ibexa-label'),
        ];
    }

    protected function getRoute(): string
    {
        return sprintf(
            '/content/create/proxy/%s/%s/%d',
            $this->contentTypeIdentifier,
            $this->languageCode,
            $this->contentFacade
                ->getContentByLocationURL($this->locationPath)
                ->getContentInfo()
                ->getMainLocationId()
        );
    }

    public function setExpectedContentDraftData(
        string $contentTypeIdentifier,
        string $languageCode,
        string $locationPath
    ): void {
        $this->contentTypeIdentifier = $contentTypeIdentifier;
        $this->languageCode = $languageCode;
        $this->locationPath = $locationPath;
    }

    public function getField(string $fieldName, ?int $fieldPosition = null): FieldTypeComponent
    {
        if ($fieldPosition === null) {
            $fieldPosition = $this->getFieldPosition($fieldName);
        }

        $activeSections = $this->getHTMLPage()
            ->setTimeout(0)
            ->findAll(new VisibleCSSLocator('activeSection', '.ibexa-anchor-navigation-menu__sections-item-btn--active'));
        $fieldLocator = $activeSections->any() ?
            new VisibleCSSLocator(
                'nthFieldWithSection',
                sprintf(
                    $this->getLocator('nthFieldWithSection')->getSelector(),
                    $activeSections->single()->getAttribute('data-target-id'),
                    $fieldPosition
                )
            ) :
            new VisibleCSSLocator('', sprintf($this->getLocator('nthField')->getSelector(), $fieldPosition));

        $fieldTypeIdentifier = $this->getFieldtypeIdentifier($fieldLocator, $fieldName);

        foreach ($this->fieldTypeComponents as $fieldTypeComponent) {
            if ($fieldTypeComponent->getFieldTypeIdentifier() === $fieldTypeIdentifier) {
                $fieldTypeComponent->setParentLocator($fieldLocator);

                return $fieldTypeComponent;
            }
        }

        throw new InvalidArgumentException(
            sprintf('Could not handle field %s with field type identifier %s', $fieldName, $fieldTypeIdentifier)
        );
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

    /**
     * @param array<string, mixed> $fieldData
     */
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
            return strtolower(str_replace(' ', '_', $fieldName));
        }

        $fieldClass = $this->getHTMLPage()->find($fieldLocator)->getAttribute('class');
        $pattern = '/ibexa-field-edit--[ez|ibexa][a-z_]*/';

        preg_match($pattern, $fieldClass, $matches);

        if (empty($matches)) {
            throw new RuntimeException(sprintf(
                'Cannot match results for pattern: "%s" and subject: "%s".',
                $pattern,
                $fieldClass
            ));
        }

        $matchedResults = explode('--', $matches[0]);

        return $matchedResults[1];
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

    public function switchToFieldTab(string $tabName): void
    {
        $this->getHTMLPage()->setTimeout(3)
            ->findAll($this->getLocator('navigationGroups'))
            ->getByCriterion(new ElementTextCriterion($tabName))
            ->click();
        $this->getHTMLPage()
            ->setTimeout(10)
            ->waitUntilCondition(new ElementHasTextCondition($this->getHTMLPage(), new VisibleCSSLocator('activeSection', '.ibexa-tabs__tab--active'), $tabName));
    }

    public function verifyFieldCannotBeEditedDueToLimitation(string $fieldName): void
    {
        $activeSections = $this->getHTMLPage()->findAll(new VisibleCSSLocator('activeSection', '.ibexa-tabs__tab--active'));
        $fieldLocator = new VisibleCSSLocator('', sprintf($this
            ->getLocator('fieldGroupNthField')->getSelector(), $activeSections->single()->find(new VisibleCSSLocator('innerLink', 'a'))->getAttribute('href'), $this->getFieldPosition($fieldName)));
        $this->getHTMLPage()->find($fieldLocator)->assert()->hasClass('ibexa-field-edit--disabled');
    }

    public function verifyAutosaveNotificationIsDisplayed(): void
    {
        $this->getHTMLPage()
            ->find($this->getLocator('autosaveIsOnInfo'))
            ->assert()->textContains('Autosave is on, draft created');
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
            ->filterBy(new ElementTextFragmentCriterion('Autosave is on, draft saved'))->any();
    }

    public function verifyAutosaveIsOffNotificationIsDisplayed(): void
    {
        $this->getHTMLPage()
            ->find($this->getLocator('autosaveIsOffInfo'))
            ->assert()->textContains('Autosave is off, draft not created');
    }
}
