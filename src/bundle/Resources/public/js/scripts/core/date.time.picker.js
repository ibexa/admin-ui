import { getAdminUiConfig, getFlatpickr } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { convertDateToTimezone, formatShortDateTime } from '../helpers/timezone.helper';
import { setInstance } from '../helpers/object.instances';

const { ibexa } = window;

const SECTION_ADJUSTMENT = 24;
const PICKER_ADJUSTMENT = 2;
const DEFAULT_CONFIG = {
    enableTime: true,
    time_24hr: true,
    formatDate: (date) => formatShortDateTime(date, null),
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

        window.addEventListener('scroll', instance.scrollHandler, true);
        document.addEventListener('scroll', instance.scrollHandler, true);

        instance.scrollHandler();
    },
    onClose: (selectedDates, dateStr, instance) => {
        window.removeEventListener('scroll', instance.scrollHandler, true);
        document.removeEventListener('scroll', instance.scrollHandler, true);
    },
};

class DateTimePicker {
    constructor(config) {
        this.container = config.container;
        this.fieldWrapper = this.container.querySelector('.ibexa-date-time-picker');
        this.inputField = this.fieldWrapper.querySelector('.ibexa-date-time-picker__input');
        this.actionsWrapper = this.fieldWrapper.querySelector('.ibexa-input-text-wrapper__actions');
        this.calendarBtn = this.actionsWrapper.querySelector('.ibexa-input-text-wrapper__action-btn--calendar');
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

        setInstance(this.fieldWrapper, this);
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
            const { timezone } = getAdminUiConfig();
            const selectedDateWithUserTimezone = convertDateToTimezone(date, timezone, true);
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

            if (flatpickrDate === undefined) {
                return;
            }

            if (isMinute) {
                flatpickrDate.setMinutes(value);
            } else {
                flatpickrDate.setHours(value);
            }

            if (this.flatpickrInstance.config.minDate?.getTime() > flatpickrDate.getTime()) {
                return;
            }

            this.flatpickrInstance.setDate(flatpickrDate, true);
        }
    }

    init() {
        const flatpickr = getFlatpickr();
        this.flatpickrConfig.static = this.inputField.dataset.isStatic === 'true';
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
