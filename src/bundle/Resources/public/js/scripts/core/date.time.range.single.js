import { getAdminUiConfig } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { convertDateToTimezone, formatShortDateTime, getBrowserTimezone } from '../helpers/timezone.helper';
import { setInstance } from '../helpers/object.instances';

const { ibexa, document } = window;

class DateTimeRangeSingle {
    constructor(config) {
        this.container = config.container;
        this.dateTimePickerInputWrapper = this.container.querySelector('.ibexa-date-time-range-single__date-time-picker-input-wrapper');

        const { periodSelector, startSelector, endSelector } = this.container.dataset;
        this.periodInput = document.querySelector(periodSelector);
        this.startInput = document.querySelector(startSelector);
        this.endInput = document.querySelector(endSelector);

        const customDateConfig = config.dateConfig || {};
        this.dateConfig = {
            mode: 'range',
            locale: {
                rangeSeparator: ' - ',
            },
            formatDate: (date) => formatShortDateTime(date, null, ibexa.adminUiConfig.dateFormat.shortDateTime),
            ...customDateConfig,
        };

        this.setSelectedDateRange = this.setSelectedDateRange.bind(this);

        setInstance(this.container, this);
    }

    getUnixTimestampUTC(dateObject) {
        const { timezone } = getAdminUiConfig();
        const selectedDateWithUserTimezone = convertDateToTimezone(dateObject, timezone, true);
        const timestamp = Math.floor(selectedDateWithUserTimezone.valueOf() / 1000);

        return timestamp;
    }

    setDates(dates) {
        if (dates.length === 2) {
            const startDate = this.getUnixTimestampUTC(dates[0]);
            const endDate = this.getUnixTimestampUTC(dates[1]);

            this.periodInput.value = '';
            this.periodInput.dispatchEvent(new Event('change'));
            this.periodInput.dispatchEvent(new Event('input'));

            this.startInput.value = startDate;
            this.startInput.dispatchEvent(new Event('change'));
            this.startInput.dispatchEvent(new Event('input'));

            this.endInput.value = endDate;
            this.endInput.dispatchEvent(new Event('change'));
            this.endInput.dispatchEvent(new Event('input'));
        } else if (dates.length === 0) {
            this.startInput.value = '';
            this.startInput.dispatchEvent(new Event('change'));
            this.startInput.dispatchEvent(new Event('input'));

            this.endInput.value = '';
            this.endInput.dispatchEvent(new Event('change'));
            this.endInput.dispatchEvent(new Event('input'));
        }
    }

    clearDates() {
        this.dateTimePickerWidget.clear();
    }

    setSelectedDateRange(timestamps, { dates }) {
        this.setDates(dates);

        this.container.dispatchEvent(
            new CustomEvent('ibexa:date-time-range-single:change', {
                detail: {
                    timestamps,
                    dates,
                },
            }),
        );
    }

    toggleHidden(isHidden) {
        this.container.classList.toggle('ibexa-date-time-range-single--hidden', isHidden);
    }

    init() {
        const { start, end } = this.container.dataset;
        let defaultDate = [];

        if (start && end) {
            const defaultStartDateWithUserTimezone = convertDateToTimezone(start * 1000);
            const defaultEndDateWithUserTimezone = convertDateToTimezone(end * 1000);
            const browserTimezone = getBrowserTimezone();

            defaultDate = [
                new Date(convertDateToTimezone(defaultStartDateWithUserTimezone, browserTimezone, true)),
                new Date(convertDateToTimezone(defaultEndDateWithUserTimezone, browserTimezone, true)),
            ];
        }

        this.dateTimePickerWidget = new ibexa.core.DateTimePicker({
            container: this.dateTimePickerInputWrapper,
            onChange: this.setSelectedDateRange,
            flatpickrConfig: {
                ...this.dateConfig,
                defaultDate,
            },
        });

        this.dateTimePickerWidget.init();
    }
}

ibexa?.addConfig('core.DateTimeRangeSingle', DateTimeRangeSingle);

export { DateTimeRangeSingle as DateRangeSingle };
