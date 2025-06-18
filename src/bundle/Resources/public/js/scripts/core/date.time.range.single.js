import { formatShortDateTime } from '../helpers/timezone.helper';
import { setInstance } from '../helpers/object.instances';

const { ibexa, document } = window;

const SECONDS_IN_DAY = 86400;

class DateTimeRangeSingle {
    constructor(config) {
        this.container = config.container;
        this.dateTimePickerInputWrapper = this.container.querySelector('.ibexa-date-time-range-single__date-time-picker-input-wrapper');

        const { periodSelector, endSelector } = this.container.dataset;
        this.periodInput = document.querySelector(periodSelector);
        this.endInput = document.querySelector(endSelector);

        const customDateConfig = config.dateConfig || {};
        this.dateConfig = {
            mode: 'range',
            locale: {
                rangeSeparator: ' - ',
            },
            formatDate: (date) => formatShortDateTime(date, null, ibexa.adminUiConfig.dateFormat.shortDate),
            ...customDateConfig,
        };

        this.setSelectedDateRange = this.setSelectedDateRange.bind(this);

        setInstance(this.container, this);
    }

    getUnixTimestampUTC(dateObject) {
        let date = new Date(Date.UTC(dateObject.getFullYear(), dateObject.getMonth(), dateObject.getDate()));
        date = Math.floor(date.getTime() / 1000);

        return date;
    }

    setDates(dates) {
        if (dates.length === 2) {
            const startDate = this.getUnixTimestampUTC(dates[0]);
            const endDate = this.getUnixTimestampUTC(dates[1]);
            const days = (endDate - startDate) / SECONDS_IN_DAY;

            this.periodInput.value = `P0Y0M${days}D`;
            this.periodInput.dispatchEvent(new Event('change'));
            this.periodInput.dispatchEvent(new Event('input'));

            this.endInput.value = endDate;
            this.endInput.dispatchEvent(new Event('change'));
            this.endInput.dispatchEvent(new Event('input'));
        } else if (dates.length === 0) {
            this.periodInput.value = '';
            this.periodInput.dispatchEvent(new Event('change'));
            this.periodInput.dispatchEvent(new Event('input'));

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
        const defaultDate = start && end ? [start, end] : [];

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
