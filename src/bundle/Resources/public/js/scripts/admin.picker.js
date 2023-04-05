(function (global, doc, ibexa) {
    const SELECTOR_PICKER = '.ibexa-picker';
    const SELECTOR_PICKER_INPUT = '.ibexa-date-time-picker__input';
    const SELECTOR_FORM_INPUT = '.ibexa-picker__form-input';
    const pickers = doc.querySelectorAll(SELECTOR_PICKER);
    const { formatShortDateTime } = ibexa.helpers.timezone;
    const { shortDate: shortDateFormat } = ibexa.adminUiConfig.dateFormat;
    const pickerConfig = {
        enableTime: true,
        time_24hr: true,
        formatDate: (date) => formatShortDateTime(date, null),
    };
    const updateInputValue = (formInput, timestamp, { dates }) => {
        let inputValue = timestamp;

        if (formInput.type === 'date') {
            inputValue = formatShortDateTime(dates[0], null, 'YYYY-MM-DD');
        }

        if (inputValue !== formInput.value) {
            formInput.value = inputValue ?? '';

            formInput.dispatchEvent(new Event('input'));
        }
    };
    const initFlatPickr = (field) => {
        const formInput = field.querySelector(SELECTOR_FORM_INPUT);
        const pickerInput = field.querySelector(SELECTOR_PICKER_INPUT);
        const customConfig = JSON.parse(pickerInput.dataset.flatpickrConfig || '{}');
        let defaultDate;

        if (formInput.value) {
            if (formInput.type === 'date') {
                defaultDate = new Date(formInput.value);
            } else {
                defaultDate = new Date(formInput.value * 1000);
            }
        }

        if (!customConfig.enableTime) {
            customConfig.formatDate = (date) => formatShortDateTime(date, null, shortDateFormat);
        }

        const dateTimePickerWidget = new ibexa.core.DateTimePicker({
            container: field,
            onChange: updateInputValue.bind(null, formInput),
            flatpickrConfig: {
                ...pickerConfig,
                defaultDate,
                ...customConfig,
            },
        });

        dateTimePickerWidget.init();
    };

    pickers.forEach(initFlatPickr);
})(window, window.document, window.ibexa, window.flatpickr);
