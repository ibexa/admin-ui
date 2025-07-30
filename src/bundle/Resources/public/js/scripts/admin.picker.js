(function (global, doc, ibexa) {
    const SELECTOR_PICKER = '.ibexa-picker';
    const SELECTOR_PICKER_INPUT = '.ibexa-date-time-picker__input';
    const SELECTOR_FORM_INPUT = '.ibexa-picker__form-input';
    const SECTION_ADJUSTMENT = 24;
    const PICKER_ADJUSTMENT = 2;
    const pickers = doc.querySelectorAll(SELECTOR_PICKER);
    const { formatShortDateTime, convertDateToTimezone, getBrowserTimezone } = ibexa.helpers.timezone;
    const userTimezone = ibexa.adminUiConfig.timezone;
    const pickerConfig = {
        enableTime: true,
        time_24hr: true,
        onOpen: (selectedDates, dateStr, instance) => {
            instance.scrollHandler = () => {
                if (instance.isOpen) {
                    const { calendarContainer, input } = instance;
                    const rect = input.getBoundingClientRect();
                    const pickerHeight = calendarContainer.offsetHeight;
                    const spaceBelow = global.innerHeight - (rect.bottom + SECTION_ADJUSTMENT);

                    if (pickerHeight > spaceBelow) {
                        calendarContainer.style.top = `${rect.top + global.scrollY - pickerHeight - PICKER_ADJUSTMENT}px`;
                        calendarContainer.classList.remove('arrowTop');
                        calendarContainer.classList.add('arrowBottom');
                    } else {
                        calendarContainer.style.top = `${rect.bottom + global.scrollY + PICKER_ADJUSTMENT}px`;
                        calendarContainer.classList.remove('arrowBottom');
                        calendarContainer.classList.add('arrowTop');
                    }
                }
            };

            global.addEventListener('scroll', instance.scrollHandler, true);
            doc.addEventListener('scroll', instance.scrollHandler, true);

            instance.scrollHandler();
        },
        onClose: (selectedDates, dateStr, instance) => {
            global.removeEventListener('scroll', instance.scrollHandler, true);
            doc.removeEventListener('scroll', instance.scrollHandler, true);
        },
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
        const enableSeconds = formInput.dataset.seconds === '1';
        let defaultDate;

        if (formInput.value) {
            const date = new Date(formInput.value * 1000);
            const dateWithUserTimezone = convertDateToTimezone(date, userTimezone);
            const localTimezone = getBrowserTimezone();
            const convertedDate = convertDateToTimezone(dateWithUserTimezone, localTimezone, true).format();

            defaultDate = convertedDate;
        }

        const dateTimePickerWidget = new ibexa.core.DateTimePicker({
            container: field,
            onChange: updateInputValue.bind(null, formInput),
            flatpickrConfig: {
                ...pickerConfig,
                defaultDate,
                enableSeconds,
                ...customConfig,
            },
        });

        dateTimePickerWidget.init();
    };

    pickers.forEach(initFlatPickr);
})(window, window.document, window.ibexa);
