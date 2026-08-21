<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\AdminUi\Behat\Component;

use Ibexa\Behat\Browser\Component\Component;
use Ibexa\Behat\Browser\Element\ElementInterface;
use Ibexa\Behat\Browser\Exception\ElementNotFoundException;
use Ibexa\Behat\Browser\Locator\VisibleCSSLocator;

/**
 * Drives the inline "quick edit" affordance rendered by admin.location.quick.field.edit.js on
 * top of a `.ibexa-content-field` row from content_view_fields.html.twig.
 *
 * Per the approved design, most field types save on Enter and discard on Escape/outside-click
 * and render no buttons at all - {@see pressEnter()}/{@see pressEscape()}/{@see clickOutside()}
 * are how those are driven. The explicit Save/Discard button pair
 * ({@see clickConfirm()}/{@see clickCancel()}) only renders for an editor that opts in
 * (`ibexa_text`, whose textarea treats a plain Enter as a newline); they carry no data attribute
 * of their own (only the draft-conflict modal buttons do, see
 * {@see QuickEditDraftConflictModal}), so they are targeted by their fixed CSS modifier class
 * rather than by their (translated, and no longer field-naming) visible text.
 */
final class QuickEditField extends Component
{
    public function openViaDoubleClick(string $fieldLabel): void
    {
        $fieldRow = $this->getFieldRow($fieldLabel);
        $this->getSession()->getDriver()->doubleClick($fieldRow->getXPath());
    }

    /**
     * Clicks the real `<button>` that is the field's accessible activation affordance
     * (content_view_fields.html.twig). A native `<button>` fires the same `click` event for a
     * mouse click and for a keyboard Enter/Space activation alike, so this one method - and the
     * `click()` it performs - exercises the exact code path a keyboard user's Enter/Space would,
     * without needing a separate, driver-specific "press a key on a focused element" step.
     */
    public function openViaTriggerButton(string $fieldLabel): void
    {
        $this->getFieldRow($fieldLabel)->find($this->getLocator('triggerButton'))->click();
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
     * Dispatches a real `keydown` event with `key: 'Escape'` at the currently open input, the
     * same event the application's own listener checks for.
     *
     * This does not go through DriverInterface::keyDown()/keyPress(): the two JS drivers this
     * suite's dependencies ship map a "press Escape" request to a native key event in mutually
     * incompatible, non-portable ways, and neither form produces `event.key === 'Escape'` on
     * both:
     *  - `behat/mink-selenium2-driver` only maps the DOM key name 'Escape' when given the exact
     *    string 'escape' (see Resources/syn.js: `keyboardEventKeys = { ..., escape: 'Escape',
     *    ... }`, consulted at `if (options.key && keyboardEventKeys[options.key]) { ... }`
     *    around line 2321-2323); `Selenium2Driver::charToOptions()` (line 256) turns an int
     *    argument into `chr($char)`, a raw control character, which is not a key in that table,
     *    so `keyDown($xpath, 27)` never becomes 'Escape' here. A string argument survives
     *    unchanged into `options.key`, so `keyDown($xpath, 'escape')` *would* work on this driver.
     *  - `dmore/chrome-mink-driver` requires the opposite: `triggerKeyboardEvent()`
     *    (ChromeDriver.php line 1533) only accepts a string argument that is exactly one
     *    character long (`mb_strlen($char) === 1`), otherwise it throws
     *    `DriverException("Invalid character '...'")); `keyDown($xpath, 'escape')` throws here.
     *    An int argument is looked up via `getKeycodeKeyValue()` (line 1463), where `27` does
     *    correctly resolve to `'Escape'` (line 1488-1489) - so `keyDown($xpath, 27)` works on
     *    this driver, the reverse of Selenium2Driver.
     * With no single call working on both, and no way to know from here which driver a given run
     * uses, this bypasses native key simulation entirely and dispatches the DOM event directly,
     * the same escape hatch {@see \Ibexa\AdminUi\Behat\Component\DateAndTimePopup} already uses
     * for a different driver limitation. `Session::executeScript()` is plain JS execution, so it
     * behaves identically regardless of which driver is active.
     */
    public function pressEscape(string $fieldLabel): void
    {
        $xpath = $this->getEditorInput($fieldLabel)->getXPath();
        $script = <<<'JS'
            (function (xpath) {
                var target = document.evaluate(
                    xpath,
                    document,
                    null,
                    XPathResult.FIRST_ORDERED_NODE_TYPE,
                    null
                ).singleNodeValue;

                if (target) {
                    target.dispatchEvent(new KeyboardEvent('keydown', {
                        key: 'Escape',
                        bubbles: true,
                        cancelable: true,
                    }));
                }
            })(%s);
            JS;

        $this->getSession()->executeScript(sprintf($script, json_encode($xpath)));
    }

    public function clickOutside(): void
    {
        $this->getHTMLPage()->find($this->getLocator('outsideArea'))->click();
    }

    /**
     * Dispatches a real `keydown` event with `key: 'Enter'` at the currently open input - the
     * save path for every field type except `ibexa_text` (see the class docblock), where a
     * plain Enter means "insert a newline" instead and {@see clickConfirm()} is used.
     *
     * Bypasses native key simulation the same way, and for the same cross-driver reasons, as
     * {@see pressEscape()} - see that method's docblock.
     */
    public function pressEnter(string $fieldLabel): void
    {
        $xpath = $this->getEditorInput($fieldLabel)->getXPath();
        $script = <<<'JS'
            (function (xpath) {
                var target = document.evaluate(
                    xpath,
                    document,
                    null,
                    XPathResult.FIRST_ORDERED_NODE_TYPE,
                    null
                ).singleNodeValue;

                if (target) {
                    target.dispatchEvent(new KeyboardEvent('keydown', {
                        key: 'Enter',
                        bubbles: true,
                        cancelable: true,
                    }));
                }
            })(%s);
            JS;

        $this->getSession()->executeScript(sprintf($script, json_encode($xpath)));
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

    /**
     * This method plus the `nthFieldContainer`/`fieldName` locators below are a verbatim copy of
     * {@see \Ibexa\AdminUi\Behat\Component\ContentItemAdminPreview::getFieldPosition()} (and its
     * locators). That method is `protected` on a component with an unrelated constructor
     * signature (it takes `iterable $fieldTypeComponents`, which this component has no use for),
     * and this component is `final`, so reuse would mean either widening that method's visibility
     * for a single external caller or making QuickEditField depend on field type components it
     * never uses - neither of which is a clean extraction, so the duplication is kept here with
     * this pointer back to the original instead.
     *
     * @throws \Ibexa\Behat\Browser\Exception\ElementNotFoundException if no field with the given
     *         label is found, e.g. because of a typo
     */
    private function getFieldPosition(string $fieldLabel): int
    {
        $fields = $this->getHTMLPage()->findAll($this->getLocator('fieldName'))->assert()->hasElements();
        $fieldLabels = [];

        $position = 1;
        foreach ($fields as $field) {
            $text = $field->getText();

            if ($text === $fieldLabel) {
                return $position;
            }

            $fieldLabels[] = $text;
            ++$position;
        }

        throw new ElementNotFoundException(sprintf(
            'Field with label "%s" not found. Available field labels: %s.',
            $fieldLabel,
            implode(', ', $fieldLabels)
        ));
    }

    public function verifyIsLoaded(): void
    {
        // The field rows this component drives are part of the Content view's server-rendered
        // markup (ContentViewPage owns verifying that page itself has loaded) - `data-quick-edit`
        // and the editor row either exist in the DOM as soon as the containing page has rendered,
        // or the feature is off for this field. There is no separate async load step of this
        // component's own for a check here to observe.
    }

    protected function specifyLocators(): array
    {
        return [
            new VisibleCSSLocator('nthFieldContainer', 'div.ibexa-content-field:nth-of-type(%s)'),
            new VisibleCSSLocator('fieldName', '.ibexa-content-field__name'),
            new VisibleCSSLocator('editorRow', '.ibexa-quick-edit'),
            new VisibleCSSLocator('editorInput', '.ibexa-quick-edit__input'),
            new VisibleCSSLocator('triggerButton', '.ibexa-quick-edit__hint-button'),
            new VisibleCSSLocator('confirmButton', '.ibexa-quick-edit__actions .ibexa-quick-edit__action--save'),
            new VisibleCSSLocator('cancelButton', '.ibexa-quick-edit__actions .ibexa-quick-edit__action--discard'),
            new VisibleCSSLocator('outsideArea', '.ibexa-raw-content-title__text'),
        ];
    }
}
