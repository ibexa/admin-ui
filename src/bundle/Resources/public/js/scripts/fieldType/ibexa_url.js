(function (global, doc, ibexa) {
    const SELECTOR_FIELD = '.ibexa-field-edit--ibexa_url';
    const SELECTOR_FIELD_LINK = '.ibexa-data-source__field--link';
    const SELECTOR_LINK_INPUT = `${SELECTOR_FIELD_LINK} .ibexa-data-source__input`;
    const SELECTOR_LABEL = '.ibexa-data-source__label';
    const SELECTOR_ERROR_NODE = '.ibexa-data-source__field--link .ibexa-form-error';

    class IbexaUrlValidator extends ibexa.BaseFieldValidator {
        getValidatorName() {
            return 'IbexaUrlValidator';
        }

        validateUrl(event) {
            const result = {
                isError: false,
                errorMessage: null,
            };
            const input = event.currentTarget;
            const urlValue = input.value.trim();
            const isRequired = input.required;
            const isEmpty = !urlValue;
            const label = input.closest(SELECTOR_FIELD_LINK).querySelector(SELECTOR_LABEL).innerHTML;

            if (isRequired && isEmpty) {
                result.isError = true;
                result.errorMessage = ibexa.errors.emptyField.replace('{fieldName}', label);
            }

            if (!isEmpty) {
                const isUrlValid = ibexa.errors.urlRegexp.test(urlValue);

                if (!isUrlValid) {
                    result.isError = true;
                    result.errorMessage = ibexa.errors.invalidUrl;
                }
            }

            return result;
        }
    }

    const validator = new IbexaUrlValidator({
        classInvalid: 'is-invalid',
        fieldSelector: SELECTOR_FIELD,
        eventsMap: [
            {
                selector: `${SELECTOR_FIELD} ${SELECTOR_LINK_INPUT}`,
                eventName: 'blur',
                callback: 'validateUrl',
                invalidStateSelectors: [SELECTOR_LINK_INPUT, `${SELECTOR_FIELD_LINK} ${SELECTOR_LABEL}`],
                errorNodeSelectors: [SELECTOR_ERROR_NODE],
            },
        ],
    });

    validator.init();

    ibexa.addConfig('fieldTypeValidators', [validator], true);
})(window, window.document, window.ibexa);
