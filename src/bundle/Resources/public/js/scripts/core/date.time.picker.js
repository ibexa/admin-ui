import { getContext, getFlatpickr } from '../helpers/helpers.service';
import { convertDateToTimezone, formatShortDateTime } from '../helpers/timezone.helper';
import { setInstance } from '../helpers/object.instances';

const { ibexa } = window;

const DEFAULT_CONFIG = {
    enableTime: true,
    time_24hr: true,
    formatDate: (date) => formatShortDateTime(date, null),
};

class DateTimePicker {
    constructor(config) {
        this.container = config.container;
        this.fieldWrapper = this.container.querySelector('.ibexa-date-time-picker');
        this.inputField = this.fieldWrapper.querySelector('.ibexa-date-time-picker__input');
        this.actionsWrapper = this.fieldWrapper.querySelector('.ibexa-input-text-wrapper__actions');
        this.calendarBtn = this.actionsWrapper.querySelector('.ibexa-input-text-wrapper__action-btn--calendar');
        this.clearBtn = this.fieldWrapper.querySelector('.ibexa-input-text-wrapper__action-btn--clear');
        this.customOnChange = config.onChange;

        this.init = this.init.bind(this);
        this.onChange = this.onChange.bind(this);
        this.onInput = this.onInput.bind(this);
        this.clear = this.clear.bind(this);

        this.flatpickrConfig = {
            ...DEFAULT_CONFIG,
            inline: this.fieldWrapper.classList.contains('ibexa-date-time-picker--inline-datetime-popup'),
            onChange: this.onChange,
            ignoredFocusElements: [this.actionsWrapper],
            ...(config.flatpickrConfig ?? {}),
        };

        setInstance(this.container, this);
    }

    clear() {
        this.flatpickrInstance.clear();
    }

    onChange(dates) {
        const isDateSelected = !!dates[0];
        const otherArguments = { inputField: this.inputField, dates };

        if (!isDateSelected) {
            this.inputField.dataset.timestamp = '';

            this.customOnChange([''], otherArguments);

            return;
        }

        const timestamps = dates.map((date) => {
            const selectedDateWithUserTimezone = convertDateToTimezone(date, getContext().timezone, true);
            const timestamp = Math.floor(selectedDateWithUserTimezone.valueOf() / 1000);

            return timestamp;
        });

        [this.inputField.dataset.timestamp] = timestamps;

        this.customOnChange(timestamps, otherArguments);
    }

    onInput(event) {
        event.preventDefault();

        if (event.target.value === '' && this.inputField.dataset.timestamp !== '') {
            this.clear();
        }
    }

    onKeyUp(isMinute, event) {
        const inputValue = event.target.value;

        if (inputValue.length === 0) {
            return;
        }

        const value = parseInt(inputValue, 10);

        if (typeof value === 'number' && value >= 0) {
            const flatpickrDate = this.flatpickrInstance.selectedDates[0];

            if (isMinute) {
                flatpickrDate.setMinutes(value);
            } else {
                flatpickrDate.setHours(value);
            }

            if (this.flatpickrConfig.minDate.getTime() > flatpickrDate.getTime()) {
                return;
            }

            this.flatpickrInstance.setDate(flatpickrDate, true);
        }
    }

    init() {
        const flatpickr = getFlatpickr();
        this.flatpickrInstance = flatpickr(this.inputField, this.flatpickrConfig);

        this.inputField.addEventListener('input', this.onInput, false);
        this.calendarBtn.addEventListener(
            'click',
            () => {
                this.flatpickrInstance.open();
            },
            false,
        );

        if (this.flatpickrInstance.config.enableTime) {
            this.flatpickrInstance.minuteElement.addEventListener('keyup', this.onKeyUp.bind(this, true), false);
            this.flatpickrInstance.hourElement.addEventListener('keyup', this.onKeyUp.bind(this, false), false);
        }
    }
}

ibexa?.addConfig('core.DateTimePicker', DateTimePicker);

export { DateTimePicker };
