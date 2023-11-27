import { getAdminUiConfig, getMoment } from './context.helper';

const convertDateToTimezone = (date, timezone = getAdminUiConfig().timezone, forceSameTime = false) => {
    const moment = getMoment();
    
    return moment(date).tz(timezone, forceSameTime);
};
const formatDate = (date, timezone = null, format) => {
    if (timezone) {
        date = convertDateToTimezone(date, timezone);
    }

    const moment = getMoment();

    return moment(date).formatICU(format);
};
const formatFullDateTime = (date, timezone = getAdminUiConfig().timezone, format = getAdminUiConfig().dateFormat.fullDateTime) => {
    return formatDate(date, timezone, format);
};
const formatShortDateTime = (date, timezone = getAdminUiConfig().timezone, format = getAdminUiConfig().dateFormat.shortDateTime) => {
    return formatDate(date, timezone, format);
};
const getBrowserTimezone = () => {
    return Intl.DateTimeFormat().resolvedOptions().timeZone;
};

export { convertDateToTimezone, formatFullDateTime, formatShortDateTime, getBrowserTimezone };
