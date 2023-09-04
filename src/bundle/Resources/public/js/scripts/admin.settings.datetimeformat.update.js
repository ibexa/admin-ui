(function (global, doc, moment) {
    const SELECTOR_SHORT_DATE_FORMAT = '#user_setting_update_short_datetime_format_value_date_format';
    const SELECTOR_SHORT_TIME_FORMAT = '#user_setting_update_short_datetime_format_value_time_format';
    const SELECTOR_FULL_DATE_FORMAT = '#user_setting_update_full_datetime_format_value_date_format';
    const SELECTOR_FULL_TIME_FORMAT = '#user_setting_update_full_datetime_format_value_time_format';
    const SELECTOR_VALUE_PREVIEW = '.ibexa-datetime-format-preview-value';
    const dateFormatSelect = doc.querySelector(SELECTOR_SHORT_DATE_FORMAT);
    const timeFormatSelect = doc.querySelector(SELECTOR_SHORT_TIME_FORMAT);
    const fullDateFormatSelect = doc.querySelector(SELECTOR_FULL_DATE_FORMAT);
    const fullTimeFormatSelect = doc.querySelector(SELECTOR_FULL_TIME_FORMAT);
    const updateDateTimeFormatPreview = (valuePreview, dateFormat, timeFormat) => {
        valuePreview.innerHTML = moment().formatICU(`${dateFormat} ${timeFormat}`);
    };

    if (dateFormatSelect) {
        const valuePreview = dateFormatSelect.closest('#user_setting_update_short_datetime_format').querySelector(SELECTOR_VALUE_PREVIEW);

        dateFormatSelect.addEventListener('change', () => {
            updateDateTimeFormatPreview(valuePreview, dateFormatSelect.value, timeFormatSelect.value);
        });
        timeFormatSelect.addEventListener('change', () => {
            updateDateTimeFormatPreview(valuePreview, dateFormatSelect.value, timeFormatSelect.value);
        });

        updateDateTimeFormatPreview(valuePreview, dateFormatSelect.value, timeFormatSelect.value);
    }

    if (fullDateFormatSelect) {
        const valuePreview = fullDateFormatSelect
            .closest('#user_setting_update_full_datetime_format')
            .querySelector(SELECTOR_VALUE_PREVIEW);

        fullDateFormatSelect.addEventListener('change', () => {
            updateDateTimeFormatPreview(valuePreview, fullDateFormatSelect.value, fullTimeFormatSelect.value);
        });
        fullTimeFormatSelect.addEventListener('change', () => {
            updateDateTimeFormatPreview(valuePreview, fullDateFormatSelect.value, fullTimeFormatSelect.value);
        });

        updateDateTimeFormatPreview(valuePreview, fullDateFormatSelect.value, fullTimeFormatSelect.value);
    }
})(window, window.document, window.moment);
