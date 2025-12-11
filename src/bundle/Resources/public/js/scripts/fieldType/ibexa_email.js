(function (global, doc, ibexa) {
    const SELECTOR_FIELD = '.ibexa-field-edit--ibexa_email';
    const SELECTOR_ERROR_NODE = '.ibexa-form-error';

    class IbexaEmailValidator extends ibexa.BaseFieldValidator {
        /**
         * Validates the input
         *
         * @method validateInput
         * @param {Event} event
         * @returns {Object}
         * @memberof IbexaEmailValidator
         */
        validateInput(event) {
            const input = event.currentTarget;
            const isRequired = input.required;
            const isEmpty = !input.value.trim();
            const isValid = ibexa.errors.emailRegexp.test(input.value);
            const isError = (isRequired && isEmpty) || (!isEmpty && !isValid);
            const label = input.closest(SELECTOR_FIELD).querySelector('.ibexa-field-edit__label').innerHTML;
            const result = { isError };

            if (isRequired && isEmpty) {
                result.errorMessage = ibexa.errors.emptyField.replace('{fieldName}', label);
            } else if (!isEmpty && !isValid) {
                result.errorMessage = ibexa.errors.invalidEmail;
            }

            return result;
        }
    }

    const validator = new IbexaEmailValidator({
        classInvalid: 'is-invalid',
        fieldSelector: SELECTOR_FIELD,
        eventsMap: [
            {
                selector: '.ibexa-field-edit--ibexa_email input',
                eventName: 'blur',
                callback: 'validateInput',
                errorNodeSelectors: [SELECTOR_ERROR_NODE],
            },
        ],
    });

    validator.init();

    ibexa.addConfig('fieldTypeValidators', [validator], true);
})(window, window.document, window.ibexa);
