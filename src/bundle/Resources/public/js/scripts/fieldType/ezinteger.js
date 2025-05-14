(function (global, doc, ibexa) {
    const SELECTOR_FIELD = '.ibexa-field-edit--ezinteger';
    const SELECTOR_ERROR_NODE = `${SELECTOR_FIELD} .ibexa-form-error`;

    class IbexaIntegerValidator extends ibexa.BaseFieldValidator {
        /**
         * Validates the input
         *
         * @method validateInteger
         * @param {Event} event
         * @returns {Object}
         * @memberof IbexaIntegerValidator
         */
        validateInteger(event) {
            const isRequired = event.target.required;
            const value = +event.target.value;
            const isEmpty = !event.target.value && event.target.value !== '0';
            const isInteger = Number.isInteger(value);
            const isLess = value < parseInt(event.target.getAttribute('min'), 10);
            const isGreater = value > parseInt(event.target.getAttribute('max'), 10);
            const isError = (isEmpty && isRequired) || (!isEmpty && (!isInteger || isLess || isGreater));
            const label = event.target.closest(SELECTOR_FIELD).querySelector('.ibexa-field-edit__label').innerHTML;
            const result = { isError };

            if (isEmpty) {
                result.errorMessage = ibexa.errors.emptyField.replace('{fieldName}', label);
            } else if (!isInteger) {
                result.errorMessage = ibexa.errors.isNotInteger.replace('{fieldName}', label);
            } else if (isLess) {
                result.errorMessage = ibexa.errors.isLess
                    .replace('{fieldName}', label)
                    .replace('{minValue}', event.target.getAttribute('min'));
            } else if (isGreater) {
                result.errorMessage = ibexa.errors.isGreater
                    .replace('{fieldName}', label)
                    .replace('{maxValue}', event.target.getAttribute('max'));
            }

            return result;
        }
    }

    const validator = new IbexaIntegerValidator({
        classInvalid: 'is-invalid',
        fieldSelector: SELECTOR_FIELD,
        eventsMap: [
            {
                selector: '.ibexa-field-edit--ezinteger input',
                eventName: 'blur',
                callback: 'validateInteger',
                errorNodeSelectors: [SELECTOR_ERROR_NODE],
            },
        ],
    });

    validator.init();

    ibexa.addConfig('fieldTypeValidators', [validator], true);
})(window, window.document, window.ibexa);
