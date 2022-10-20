(function (global, doc, ibexa, flatpickr) {
    const { convertDateToTimezone, formatShortDateTime } = ibexa.helpers.timezone;
    const userTimezone = ibexa.adminUiConfig.timezone;
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
                ...(config.flatpickrConfig ?? {}),
            };

            ibexa.helpers.objectInstances.setInstance(this.container, this);
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
                const selectedDateWithUserTimezone = convertDateToTimezone(date, userTimezone, true);
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

        init() {
            this.flatpickrInstance = flatpickr(this.inputField, this.flatpickrConfig);

            this.inputField.addEventListener('input', this.onInput, false);
        }
    }

    ibexa.addConfig('core.DateTimePicker', DateTimePicker);
})(window, window.document, window.ibexa, window.flatpickr);
