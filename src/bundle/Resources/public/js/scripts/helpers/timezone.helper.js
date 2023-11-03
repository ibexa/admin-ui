import { getContext as getHelpersContext } from './helpers.service';

const getMomentInstance = () => {
    const config = getHelpersContext();

    return window.moment ?? config.moment;
};

const convertDateToTimezone = (date, timezone = getHelpersContext().timezone, forceSameTime = false) => {
    const moment = getMomentInstance();

    return moment(date).tz(timezone, forceSameTime);
};
const formatDate = (date, timezone = null, format) => {
    if (timezone) {
        date = convertDateToTimezone(date, timezone);
    }

    const moment = getMomentInstance();

    return moment(date).formatICU(format);
};
const formatFullDateTime = (date, timezone = getHelpersContext().timezone, format = getHelpersContext().dateFormat.fullDateTime) => {
    return formatDate(date, timezone, format);
};
const formatShortDateTime = (date, timezone = getHelpersContext().timezone, format = getHelpersContext().dateFormat.shortDateTime) => {
    return formatDate(date, timezone, format);
};
const getBrowserTimezone = () => {
    return Intl.DateTimeFormat().resolvedOptions().timeZone;
};

export { convertDateToTimezone, formatFullDateTime, formatShortDateTime, getBrowserTimezone };
