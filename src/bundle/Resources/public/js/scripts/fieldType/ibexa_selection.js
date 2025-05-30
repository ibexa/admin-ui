(function (global, doc, ibexa) {
    const SELECTOR_FIELD = '.ibexa-field-edit--ibexa_selection';
    const SELECTOR_SELECTED = '.ibexa-dropdown__selection-info';
    const SELECTOR_ERROR_NODE = '.ibexa-form-error';
    const EVENT_VALUE_CHANGED = 'change';

    class IbexaSelectionValidator extends ibexa.BaseFieldValidator {
        /**
         * Validates the textarea field value
         *
         * @method validateInput
         * @param {Event} event
         * @returns {Object}
         * @memberof IbexaSelectionValidator
         */
        validateInput(event) {
            const fieldContainer = event.currentTarget.closest(SELECTOR_FIELD);
            const hasSelectedOptions = !!fieldContainer.querySelector('.ibexa-data-source__input').value;
            const isRequired = fieldContainer.classList.contains('ibexa-field-edit--required');
            const isError = isRequired && !hasSelectedOptions;
            const label = fieldContainer.querySelector('.ibexa-field-edit__label').innerHTML;
            const errorMessage = ibexa.errors.emptyField.replace('{fieldName}', label);

            return {
                isError,
                errorMessage,
            };
        }
    }

    const validator = new IbexaSelectionValidator({
        classInvalid: 'is-invalid',
        fieldSelector: SELECTOR_FIELD,
        eventsMap: [
            {
                selector: `${SELECTOR_FIELD} .ibexa-data-source__input--selection`,
                eventName: EVENT_VALUE_CHANGED,
                callback: 'validateInput',
                errorNodeSelectors: [SELECTOR_ERROR_NODE],
                invalidStateSelectors: [SELECTOR_SELECTED],
            },
        ],
    });

    validator.init();

    ibexa.addConfig('fieldTypeValidators', [validator], true);
})(window, window.document, window.ibexa);
