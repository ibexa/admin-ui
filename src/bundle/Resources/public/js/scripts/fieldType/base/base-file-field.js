(function (global, doc, ibexa) {
    const SELECTOR_FIELD_LABEL = '.ibexa-field-edit__label-wrapper .ibexa-field-edit__label';

    class BaseFileFieldValidator extends ibexa.BaseFieldValidator {
        /**
         * Validates the input
         *
         * @method validateInput
         * @param {Event} event
         * @returns {Object}
         */
        validateInput(event) {
            const input = event.currentTarget;
            const dataContainer = this.fieldContainer.querySelector('.ibexa-field-edit__data');
            const label = this.fieldContainer.querySelector(SELECTOR_FIELD_LABEL).innerHTML;
            const isRequired = input.required || this.fieldContainer.classList.contains('ibexa-field-edit--required');
            const dataMaxSize = +input.dataset.maxFileSize;
            const maxFileSize = parseInt(dataMaxSize, 10);
            const { allowedFileTypes= [] } = input.dataset;
            const isEmpty = input.files && !input.files.length && dataContainer && !dataContainer.hasAttribute('hidden');
            let result = { isError: false };

            if (isRequired && isEmpty) {
                result = {
                    isError: true,
                    errorMessage: ibexa.errors.emptyField.replace('{fieldName}', label),
                };
            }

            const file = input.files[0];

            if (!isEmpty && maxFileSize > 0 && file && file.size > maxFileSize) {
                result = this.validateFileSize();
            }

            if (!isEmpty && allowedFileTypes.length > 0 && file && !allowedFileTypes.includes(file.type)) {
                result = this.showFileTypeError();
            }

            return result;
        }

        validateFileSize() {
            return this.showFileSizeError();
        }

        /**
         * Displays an error message: file size exceeds maximum value
         *
         * @method showFileSizeNotice
         * @returns {Object}
         */
        showFileSizeError() {
            const label = this.fieldContainer.querySelector(SELECTOR_FIELD_LABEL).innerHTML;
            const result = {
                isError: true,
                errorMessage: ibexa.errors.invalidFileSize.replace('{fieldName}', label),
            };

            return result;
        }

        showFileTypeError() {
            const label = this.fieldContainer.querySelector(SELECTOR_FIELD_LABEL).innerHTML;
            const result = {
                isError: true,
                errorMessage: ibexa.errors.invalidFileType.replace('{fieldName}', label),
            };

            return result;
        }
    }

    ibexa.addConfig('BaseFileFieldValidator', BaseFileFieldValidator);
})(window, window.document, window.ibexa);
