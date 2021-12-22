(function(global, doc, moment) {
    const SELECTOR_SHORT_DATE_FORMAT = '#user_setting_update_short_datetime_format_value_date_format';
    const SELECTOR_SHORT_TIME_FORMAT = '#user_setting_update_short_datetime_format_value_time_format';

    const SELECTOR_FULL_DATE_FORMAT = '#user_setting_update_full_datetime_format_value_date_format';
    const SELECTOR_FULL_TIME_FORMAT = '#user_setting_update_full_datetime_format_value_time_format';

    const SELECTOR_VALUE_PREVIEW = '.ibexa-datetime-format-preview-value';

    const dateFormatSelect = doc.querySelector(SELECTOR_SHORT_DATE_FORMAT);
    const timeFormatSelect = doc.querySelector(SELECTOR_SHORT_TIME_FORMAT);

    const fullDateFormatSelect = doc.querySelector(SELECTOR_FULL_DATE_FORMAT);
    const fullTimeFormatSelect = doc.querySelector(SELECTOR_FULL_TIME_FORMAT);

    const updateDateTimeFormatPreview = (valuePreview) => {
        valuePreview.innerHTML = moment().formatICU(`${dateFormatSelect.value} ${timeFormatSelect.value}`);
    };

    if (dateFormatSelect) {
        let valuePreview = dateFormatSelect.parentElement.parentElement.parentElement.parentElement.parentElement.querySelector(SELECTOR_VALUE_PREVIEW);

        dateFormatSelect.addEventListener('change', () => { updateDateTimeFormatPreview(valuePreview) });
        timeFormatSelect.addEventListener('change', () => { updateDateTimeFormatPreview(valuePreview) });
        updateDateTimeFormatPreview(valuePreview);
    }

    if (fullDateFormatSelect) {
        let valuePreview = fullDateFormatSelect.parentElement.parentElement.parentElement.parentElement.parentElement.querySelector(SELECTOR_VALUE_PREVIEW);

        fullDateFormatSelect.addEventListener('change', () => { updateDateTimeFormatPreview(valuePreview) });
        fullTimeFormatSelect.addEventListener('change', () => { updateDateTimeFormatPreview(valuePreview) });
        updateDateTimeFormatPreview(valuePreview);
    }

})(window, window.document, window.moment);
