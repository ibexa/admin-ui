<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Behat\Mink\Session;
use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Element\ElementInterface;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

/**
 * Drives the inline "quick edit" affordance rendered by admin.location.quick.field.edit.js on
 * top of a `.ibexa-content-field` row from content_view_fields.html.twig.
 *
 * The confirm/cancel buttons the editor row renders carry no data attribute of their own (only
 * the draft-conflict modal buttons do, see {@see QuickEditDraftConflictModal}), so they are
 * targeted by their translated aria-label, the same text a screen reader would announce.
 */
final class QuickEditField extends Component
{
    public function __construct(
        readonly Session $session
    ) {
        parent::__construct($session);
    }

    public function openViaDoubleClick(string $fieldLabel): void
    {
        $fieldRow = $this->getFieldRow($fieldLabel);
        $this->getSession()->getDriver()->doubleClick($fieldRow->getXPath());
    }

    public function isQuickEditable(string $fieldLabel): bool
    {
        return $this->getFieldRow($fieldLabel)->hasAttribute('data-quick-edit');
    }

    public function isEditorOpenFor(string $fieldLabel): bool
    {
        return $this->getFieldRow($fieldLabel)->findAll($this->getLocator('editorRow'))->any();
    }

    public function getOpenEditorCount(): int
    {
        return $this->getHTMLPage()->findAll($this->getLocator('editorRow'))->count();
    }

    public function setInputValue(string $fieldLabel, string $value): void
    {
        $this->getEditorInput($fieldLabel)->setValue($value);
    }

    public function toggleCheckbox(string $fieldLabel): void
    {
        $this->getEditorInput($fieldLabel)->click();
    }

    public function clickConfirm(string $fieldLabel): void
    {
        $this->getFieldRow($fieldLabel)->find($this->getLocator('confirmButton'))->click();
    }

    public function clickCancel(string $fieldLabel): void
    {
        $this->getFieldRow($fieldLabel)->find($this->getLocator('cancelButton'))->click();
    }

    /**
     * Sends a raw Escape keydown/keyup pair at the currently open input, the same way a user
     * pressing Escape while typing would. There is no cross-driver "named key" abstraction in
     * Mink's DriverInterface, so this relies on 27, the DOM keyCode for Escape.
     */
    public function pressEscape(string $fieldLabel): void
    {
        $xpath = $this->getEditorInput($fieldLabel)->getXPath();
        $driver = $this->getSession()->getDriver();
        $driver->keyDown($xpath, 27);
        $driver->keyUp($xpath, 27);
    }

    public function clickOutside(): void
    {
        $this->getHTMLPage()->find($this->getLocator('outsideArea'))->click();
    }

    private function getEditorInput(string $fieldLabel): ElementInterface
    {
        return $this->getFieldRow($fieldLabel)->find($this->getLocator('editorInput'));
    }

    private function getFieldRow(string $fieldLabel): ElementInterface
    {
        $position = $this->getFieldPosition($fieldLabel);

        return $this->getHTMLPage()->find(
            new VisibleCSSLocator('field', sprintf($this->getLocator('nthFieldContainer')->getSelector(), $position))
        );
    }

    private function getFieldPosition(string $fieldLabel): int
    {
        $fields = $this->getHTMLPage()->findAll($this->getLocator('fieldName'))->assert()->hasElements();

        $position = 1;
        foreach ($fields as $field) {
            if ($field->getText() === $fieldLabel) {
                return $position;
            }

            ++$position;
        }

        return $position;
    }

    public function verifyIsLoaded(): void
    {
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('nthFieldContainer', 'div.ibexa-content-field:nth-of-type(%s)'),
            new VisibleCSSLocator('fieldName', '.ibexa-content-field__name'),
            new VisibleCSSLocator('editorRow', '.ibexa-quick-edit'),
            new VisibleCSSLocator('editorInput', '.ibexa-quick-edit__input'),
            new VisibleCSSLocator('confirmButton', '.ibexa-quick-edit__actions [aria-label="Save and publish"]'),
            new VisibleCSSLocator('cancelButton', '.ibexa-quick-edit__actions [aria-label="Discard changes"]'),
            new VisibleCSSLocator('outsideArea', '.ibexa-raw-content-title__text'),
        ];
    }
}
