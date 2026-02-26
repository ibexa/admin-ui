(function (global, doc, ibexa) {
    const SELECTOR_FIELD = '.ibexa-field-edit--ezisbn';
    const SELECTOR_SOURCE_INPUT = '.ibexa-data-source__input';
    const SELECTOR_LABEL = '.ibexa-field-edit__label';

    class IbexaIsbnValidator extends ibexa.BaseFieldValidator {
        /**
         * Validates the input
         *
         * @method validateInput
         * @param {Event} event
         * @returns {Object}
         * @memberof IbexaIsbnValidator
         */
        validateInput(event) {
            const isError = event.target.required && !event.target.value.trim();
            const label = event.target.closest(SELECTOR_FIELD).querySelector(SELECTOR_LABEL).innerText;
            const errorMessage = ibexa.errors.emptyField.replace('{fieldName}', label);

            return {
                isError,
                errorMessage,
            };
        }
    }

    const validator = new IbexaIsbnValidator({
        classInvalid: 'is-invalid',
        fieldSelector: SELECTOR_FIELD,
        eventsMap: [
            {
                selector: `${SELECTOR_FIELD} input`,
                eventName: 'blur',
                callback: 'validateInput',
                errorNodeSelectors: ['.ibexa-form-error'],
                invalidStateSelectors: [SELECTOR_SOURCE_INPUT, SELECTOR_LABEL],
            },
        ],
    });

    validator.init();

    ibexa.addConfig('fieldTypeValidators', [validator], true);
})(window, window.document, window.ibexa);
