(function(global, doc, ibexa, flatpickr) {
    const { convertDateToTimezone, formatShortDateTime } = ibexa.helpers.timezone;
    const userTimezone = ibexa.adminUiConfig.timezone;

    const DEFAULT_CONFIG = {
        enableTime: true,
        time_24hr: true,
        formatDate: (date) => formatShortDateTime(date, null),

    }
    class DateAndTime {
        constructor(config) {
            this.container = config.container;
            this.fieldWrapper = this.container.querySelector('.ibexa-date-time-picker');
            this.inputField = this.fieldWrapper.querySelector('.ibexa-date-time-picker__input');
            this.customOnChange = config.onChange;
            console.log(this.fieldWrapper.classList);
            this.flatpickrConfig = {
                ...DEFAULT_CONFIG,
                inline: this.fieldWrapper.classList.contains('ibexa-date-time-picker--inline-flatpickr'),
                onChange: this.onChange,
                ...(config.flatpickrConfig ?? {})
            }

            this.init = this.init.bind(this);
            this.onChange = this.onChange.bind(this);
            this.onInputBtn = this.onInputBtn.bind(this);
        }

        onChange = (dates) => {
            const isDateSelected = !!dates[0];

            if (!isDateSelected) {
                this.inputField.dataset.timestamp = '';

                this.customOnChange(this.inputField, '');

                return;
            }

            const selectedDate = dates[0];
            const selectedDateWithUserTimezone = convertDateToTimezone(selectedDate, userTimezone, true);
            const timestamp = Math.floor(selectedDateWithUserTimezone.valueOf() / 1000);

            this.inputField.dataset.timestamp = timestamp;

            this.customOnChange(this.inputField, timestamp);
        };

        onInputBtn(event) {
            if (event.target.value === '' && this.inputField.dataset.timestamp !== '') {
                this.flatpickrInstance.setDate(null, true);
            }
        }

        init() {
            this.flatpickrInstance = flatpickr(this.inputField, this.flatpickrConfig);

            this.inputField.addEventListener('input', this.onInputBtn, false);
        }
    }

    ibexa.addConfig('core.DateAndTime', DateAndTime);
})(window, window.document, window.ibexa, window.flatpickr);
