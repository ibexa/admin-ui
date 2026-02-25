(function (global, doc, ibexa) {
    const SELECTOR_FIELD = '.ibexa-field-edit--eztext';
    const SELECTOR_LABEL = '.ibexa-field-edit__label';

    class EzTextValidator extends ibexa.BaseFieldValidator {
        /**
         * Validates the textarea field value
         *
         * @method validateInput
         * @param {Event} event
         * @returns {Object}
         * @memberof EzTextValidator
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

    const validator = new EzTextValidator({
        classInvalid: 'is-invalid',
        fieldSelector: SELECTOR_FIELD,
        eventsMap: [
            {
                selector: '.ibexa-field-edit--eztext textarea',
                eventName: 'blur',
                callback: 'validateInput',
                invalidStateSelectors: [SELECTOR_LABEL],
                errorNodeSelectors: ['.ibexa-form-error'],
            },
        ],
    });

    validator.init();

    ibexa.addConfig('fieldTypeValidators', [validator], true);
})(window, window.document, window.ibexa);
