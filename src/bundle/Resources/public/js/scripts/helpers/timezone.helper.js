(function (global, doc, ibexa, moment) {
    const userPreferredTimezone = ibexa.adminUiConfig.timezone;
    const userPreferredFullDateTimeFormat = ibexa.adminUiConfig.dateFormat.fullDateTime;
    const userPreferredShortDateTimeFormat = ibexa.adminUiConfig.dateFormat.shortDateTime;

    const convertDateToTimezone = (date, timezone = userPreferredTimezone, forceSameTime = false) => {
        return moment(date).tz(timezone, forceSameTime);
    };
    const formatDate = (date, timezone = null, format) => {
        if (timezone) {
            date = convertDateToTimezone(date, timezone);
        }

        return moment(date).formatICU(format);
    };
    const formatFullDateTime = (date, timezone = userPreferredTimezone, format = userPreferredFullDateTimeFormat) => {
        return formatDate(date, timezone, format);
    };
    const formatShortDateTime = (date, timezone = userPreferredTimezone, format = userPreferredShortDateTimeFormat) => {
        return formatDate(date, timezone, format);
    };

    ibexa.addConfig('helpers.timezone', {
        convertDateToTimezone,
        formatFullDateTime,
        formatShortDateTime,
    });
})(window, window.document, window.ibexa, window.moment);
