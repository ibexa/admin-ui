(function (global, doc, ibexa, moment) {
    const SELECTOR_PICKER = '.ibexa-picker';
    const SELECTOR_PICKER_INPUT = '.ibexa-date-time-picker__input';
    const SELECTOR_FORM_INPUT = '.ibexa-picker__form-input';
    const pickers = doc.querySelectorAll(SELECTOR_PICKER);
    const { formatShortDateTime, convertDateToTimezone } = ibexa.helpers.timezone;
    const pickerConfig = {
        enableTime: true,
        time_24hr: true,
        formatDate: (date) => formatShortDateTime(date, null),
    };
    const updateInputValue = (formInput, timestamp) => {
        if (timestamp !== formInput.value) {
            formInput.value = timestamp ?? '';

            formInput.dispatchEvent(new Event('input'));
        }
    };
    const initFlatPickr = (field) => {
        const formInput = field.querySelector(SELECTOR_FORM_INPUT);
        const pickerInput = field.querySelector(SELECTOR_PICKER_INPUT);
        const customConfig = JSON.parse(pickerInput.dataset.flatpickrConfig || '{}');
        let defaultDate;

        if (formInput.value) {
            const date = new Date(formInput.value * 1000);
            const convertedDateToUTC = convertDateToTimezone(date, 'UTC');
            const localTimezone = moment.tz.guess();
            const convertedDate = convertDateToTimezone(convertedDateToUTC, localTimezone, true).format();

            defaultDate = convertedDate;
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
})(window, window.document, window.ibexa, window.moment);
