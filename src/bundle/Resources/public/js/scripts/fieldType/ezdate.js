(function (global, doc, ibexa) {
    const SELECTOR_FIELD = '.ibexa-field-edit--ezdate';
    const SELECTOR_INPUT = '.ibexa-data-source__input:not(.flatpickr-input)';
    const SELECTOR_FLATPICKR_INPUT = '.flatpickr-input';
    const EVENT_VALUE_CHANGED = 'change';
    const SELECTOR_ERROR_NODE = '.ibexa-form-error';

    class EzDateValidator extends ibexa.BaseFieldValidator {
        /**
         * Validates the input
         *
         * @method validateInput
         * @param {Event} event
         * @returns {Object}
         * @memberof EzDateValidator
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

    const validator = new EzDateValidator({
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

    const dateFields = doc.querySelectorAll(SELECTOR_FIELD);
    const updateInputValue = (sourceInput, timestamps, { dates }) => {
        const event = new CustomEvent(EVENT_VALUE_CHANGED);

        if (!dates.length) {
            sourceInput.value = '';
            sourceInput.dispatchEvent(event);

            return;
        }

        let date = new Date(dates[0]);

        date = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));

        sourceInput.value = Math.floor(date.valueOf() / 1000);
        sourceInput.dispatchEvent(event);
    };
    const initFlatPickr = (field) => {
        const sourceInput = field.querySelector(SELECTOR_INPUT);
        let defaultDate = null;

        if (sourceInput.value) {
            defaultDate = new Date(sourceInput.value * 1000);

            const { actionType } = sourceInput.dataset;

            if (actionType === 'create') {
                defaultDate.setTime(new Date().getTime());
            } else if (actionType === 'edit') {
                defaultDate = new Date(defaultDate.getUTCFullYear(), defaultDate.getUTCMonth(), defaultDate.getUTCDate(), 0, 0, 0, 0);
            }

            updateInputValue(sourceInput, [], { dates: [defaultDate] });
        }

        const dateTimePickerWidget = new ibexa.core.DateTimePicker({
            container: field,
            onChange: updateInputValue.bind(null, sourceInput),
            flatpickrConfig: {
                formatDate: (date) => ibexa.helpers.timezone.formatFullDateTime(date, null, ibexa.adminUiConfig.dateFormat.fullDate),
                enableTime: false,
                defaultDate: defaultDate,
            },
        });

        dateTimePickerWidget.init();

        if (sourceInput.hasAttribute('required')) {
            dateTimePickerWidget.inputField.setAttribute('required', true);
        }
    };

    dateFields.forEach(initFlatPickr);
})(window, window.document, window.ibexa);
