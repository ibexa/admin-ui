(function (global, doc, ibexa) {
    const SELECTOR_FIELD = '.ibexa-field-edit--ezdatetime';
    const SELECTOR_INPUT = '.ibexa-data-source__input[data-seconds]';
    const SELECTOR_FLATPICKR_INPUT = '.flatpickr-input';
    const EVENT_VALUE_CHANGED = 'change';
    const SELECTOR_ERROR_NODE = '.ibexa-data-source';
    const { convertDateToTimezone } = ibexa.helpers.timezone;

    class EzDateTimeValidator extends ibexa.BaseFieldValidator {
        /**
         * Validates the input
         *
         * @method validateInput
         * @param {Event} event
         * @returns {Object}
         * @memberof EzDateTimeValidator
         */
        validateInput(event) {
            const target = event.currentTarget;
            const isRequired = target.required;
            const isEmpty = !target.value.trim().length;
            const label = event.target.closest(this.fieldSelector).querySelector('.ibexa-field-edit__label').innerHTML;
            let isError = false;
            let errorMessage = '';

            if (isRequired && isEmpty) {
                isError = true;
                errorMessage = ibexa.errors.emptyField.replace('{fieldName}', label);
            }

            return {
                isError,
                errorMessage,
            };
        }
    }

    const validator = new EzDateTimeValidator({
        classInvalid: 'is-invalid',
        fieldSelector: SELECTOR_FIELD,
        eventsMap: [
            {
                selector: `${SELECTOR_FIELD} ${SELECTOR_INPUT}`,
                eventName: EVENT_VALUE_CHANGED,
                callback: 'validateInput',
                errorNodeSelectors: [SELECTOR_ERROR_NODE],
                invalidStateSelectors: [SELECTOR_FLATPICKR_INPUT],
            },
            {
                selector: `${SELECTOR_FIELD} ${SELECTOR_FLATPICKR_INPUT}`,
                eventName: 'blur',
                callback: 'validateInput',
                errorNodeSelectors: [SELECTOR_ERROR_NODE],
                invalidStateSelectors: [SELECTOR_FLATPICKR_INPUT],
            },
        ],
    });

    validator.init();

    ibexa.addConfig('fieldTypeValidators', [validator], true);

    const datetimeFields = doc.querySelectorAll(SELECTOR_FIELD);
    const updateInputValue = (sourceInput, [timestamp]) => {
        sourceInput.value = timestamp ?? '';
        sourceInput.dispatchEvent(new CustomEvent(EVENT_VALUE_CHANGED));
    };
    const initFlatPickr = (field) => {
        const sourceInput = field.querySelector(SELECTOR_INPUT);
        const secondsEnabled = sourceInput.dataset.seconds === '1';
        let defaultDate = null;

        if (sourceInput.value) {
            const defaultDateWithUserTimezone = convertDateToTimezone(sourceInput.value * 1000);
            const browserTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

            defaultDate = new Date(convertDateToTimezone(defaultDateWithUserTimezone, browserTimezone, true));
        }

        const dateTimePickerWidget = new ibexa.core.DateTimePicker({
            container: field,
            onChange: updateInputValue.bind(null, sourceInput),
            flatpickrConfig: {
                enableSeconds: secondsEnabled,
                defaultDate: defaultDate,
            },
        });

        dateTimePickerWidget.init();

        if (sourceInput.hasAttribute('required')) {
            dateTimePickerWidget.inputField.setAttribute('required', true);
        }
    };

    datetimeFields.forEach(initFlatPickr);
})(window, window.document, window.ibexa);
