(function (global, doc, ibexa) {
    const SELECTOR_FIELD = '.ibexa-field-edit--ibexa_time';
    const SELECTOR_INPUT = '.ibexa-data-source__input:not(.flatpickr-input)';
    const SELECTOR_FLATPICKR_INPUT = '.flatpickr-input';
    const SELECTOR_ERROR_NODE = '.ibexa-data-source';
    const EVENT_VALUE_CHANGED = 'change';

    class IbexaTimeValidator extends ibexa.BaseFieldValidator {
        /**
         * Validates the input
         *
         * @method validateInput
         * @param {Event} event
         * @returns {Object}
         * @memberof IbexaTimeValidator
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

    const validator = new IbexaTimeValidator({
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

    const timeFields = doc.querySelectorAll(SELECTOR_FIELD);
    const updateInputValue = (sourceInput, timestamps, { dates }) => {
        const event = new CustomEvent(EVENT_VALUE_CHANGED);

        if (!dates.length) {
            sourceInput.value = '';
            sourceInput.dispatchEvent(event);

            return;
        }

        const date = new Date(dates[0]);
        sourceInput.value = date.getHours() * 3600 + date.getMinutes() * 60 + date.getSeconds();

        sourceInput.dispatchEvent(event);
    };
    const initFlatPickr = (field) => {
        const sourceInput = field.querySelector(SELECTOR_INPUT);
        const enableSeconds = sourceInput.dataset.seconds === '1';
        let defaultDate = null;

        if (sourceInput.value) {
            const value = parseInt(sourceInput.value, 10);
            const date = new Date();

            date.setHours(Math.floor(value / 3600));
            date.setMinutes(Math.floor((value % 3600) / 60));
            date.setSeconds(Math.floor((value % 3600) % 60));

            defaultDate = date;
        }

        const dateTimePickerWidget = new ibexa.core.DateTimePicker({
            container: field,
            onChange: updateInputValue.bind(null, sourceInput),
            flatpickrConfig: {
                noCalendar: true,
                formatDate: (date) => ibexa.helpers.timezone.formatFullDateTime(date, null, ibexa.adminUiConfig.dateFormat.fullTime),
                enableSeconds,
                defaultDate,
            },
        });

        dateTimePickerWidget.init();

        if (sourceInput.hasAttribute('required')) {
            dateTimePickerWidget.inputField.setAttribute('required', true);
        }
    };

    timeFields.forEach(initFlatPickr);
})(window, window.document, window.ibexa);
