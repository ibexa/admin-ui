(function (global, doc, ibexa, flatpickr) {
    const { convertDateToTimezone, formatShortDateTime } = ibexa.helpers.timezone;
    const userTimezone = ibexa.adminUiConfig.timezone;
    const DEFAULT_CONFIG = {
        enableTime: true,
        time_24hr: true,
        formatDate: (date) => formatShortDateTime(date, null),
    };
    class DateAndTime {
        constructor(config) {
            this.container = config.container;
            this.fieldWrapper = this.container.querySelector('.ibexa-date-time-picker');
            this.inputField = this.fieldWrapper.querySelector('.ibexa-date-time-picker__input');
            this.customOnChange = config.onChange;

            this.init = this.init.bind(this);
            this.onChange = this.onChange.bind(this);
            this.onInputBtn = this.onInputBtn.bind(this);
            this.clear = this.clear.bind(this);

            this.flatpickrConfig = {
                ...DEFAULT_CONFIG,
                inline: this.fieldWrapper.classList.contains('ibexa-date-time-picker--inline-flatpickr'),
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
            const restArgument = { inputField: this.inputField, flatpickrDates: dates };

            if (!isDateSelected) {
                this.inputField.dataset.timestamp = '';

                this.customOnChange([''], restArgument);

                return;
            }

            const timestamps = dates.map((date) => {
                const selectedDateWithUserTimezone = convertDateToTimezone(date, userTimezone, true);
                const timestamp = Math.floor(selectedDateWithUserTimezone.valueOf() / 1000);

                return timestamp;
            });

            [this.inputField.dataset.timestamp] = timestamps;

            this.customOnChange(timestamps, restArgument);
        }

        onInputBtn(event) {
            event.preventDefault();

            if (event.target.value === '' && this.inputField.dataset.timestamp !== '') {
                this.clear();
            }
        }

        init() {
            this.flatpickrInstance = flatpickr(this.inputField, this.flatpickrConfig);

            this.inputField.addEventListener('input', this.onInputBtn, false);
        }
    }

    ibexa.addConfig('core.DateAndTime', DateAndTime);
})(window, window.document, window.ibexa, window.flatpickr);
