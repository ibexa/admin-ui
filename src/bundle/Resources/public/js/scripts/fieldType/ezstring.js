(function (global, doc, ibexa) {
    const SELECTOR_FIELD = '.ibexa-field-edit--ezstring';
    const SELECTOR_SOURCE_INPUT = '.ibexa-data-source__input';

    class EzStringValidator extends ibexa.BaseFieldValidator {
        /**
         * Validates the input
         *
         * @method validateInput
         * @param {Event} event
         * @returns {Object}
         * @memberof EzStringValidator
         */
        validateInput(event) {
            const isRequired = event.target.required;
            const isEmpty = !event.target.value;
            const isTooShort = event.target.value.length < parseInt(event.target.dataset.min, 10);
            const isTooLong = event.target.value.length > parseInt(event.target.dataset.max, 10);
            const isError = (isEmpty && isRequired) || (!isEmpty && (isTooShort || isTooLong));
            const label = event.target.closest(SELECTOR_FIELD).querySelector('.ibexa-field-edit__label').innerHTML;
            const result = { isError };

            if (isEmpty) {
                result.errorMessage = ibexa.errors.emptyField.replace('{fieldName}', label);
            } else if (isTooShort) {
                result.errorMessage = ibexa.errors.tooShort.replace('{fieldName}', label).replace('{minLength}', event.target.dataset.min);
            } else if (isTooLong) {
                result.errorMessage = ibexa.errors.tooLong.replace('{fieldName}', label).replace('{maxLength}', event.target.dataset.max);
            }

            return result;
        }
    }

    const validator = new EzStringValidator({
        classInvalid: 'is-invalid',
        fieldSelector: SELECTOR_FIELD,
        eventsMap: [
            {
                selector: '.ibexa-field-edit--ezstring input',
                eventName: 'blur',
                callback: 'validateInput',
                errorNodeSelectors: ['.ibexa-form-error'],
                invalidStateSelectors: [SELECTOR_SOURCE_INPUT],
            },
        ],
    });

    validator.init();

    ibexa.addConfig('fieldTypeValidators', [validator], true);
})(window, window.document, window.ibexa);
