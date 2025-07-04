(function (global, doc, ibexa, Translator) {
    const SELECTOR_FIELD = '.ibexa-field-edit--ibexa_image';
    const SELECTOR_INPUT_FILE = 'input[type="file"]';
    const SELECTOR_ALT_WRAPPER = '.ibexa-field-edit-preview__image-alt';
    const SELECTOR_INPUT_ALT = '.ibexa-field-edit-preview__image-alt .ibexa-data-source__input';
    const EVENT_CANCEL_ERROR = 'ibexa-cancel-errors';

    class IbexaImageFilePreviewField extends ibexa.BasePreviewField {
        /**
         * Gets a temporary image URL
         *
         * @method getImageUrl
         * @param {File} file
         * @param {Function} callback the callback returns a retrieved file's temporary URL
         */
        getImageUrl(file, callback) {
            const reader = new FileReader();

            reader.onload = (event) => callback(event.target.result);
            reader.readAsDataURL(file);
        }

        /**
         * Loads dropped file preview.
         * It should redefined in each class that extends this one.
         *
         * @method loadDroppedFilePreview
         * @param {Event} event
         */
        loadDroppedFilePreview(event) {
            const preview = this.fieldContainer.querySelector('.ibexa-field-edit__preview');
            const imageNode = preview.querySelector('.ibexa-field-edit-preview__media');
            const nameContainer = preview.querySelector('.ibexa-field-edit-preview__file-name');
            const sizeContainer = preview.querySelector('.ibexa-field-edit-preview__file-size');
            const files = [].slice.call(event.target.files);
            const fileSize = this.formatFileSize(files[0].size);
            const { escapeHTML } = ibexa.helpers.text;
            const fileName = escapeHTML(files[0].name);

            this.getImageUrl(files[0], (url) => {
                const image = new Image();

                image.onload = function () {
                    const { width } = image;
                    const { height } = image;
                    const widthNode = preview.querySelector('.ibexa-field-edit-preview__dimension--width');
                    const heightNode = preview.querySelector('.ibexa-field-edit-preview__dimension--height');

                    widthNode.innerHTML = Translator.trans(
                        /* @Desc("W:%width% px") */ 'ibexa_image.dimensions.width',
                        { width },
                        'ibexa_fieldtypes_edit',
                    );
                    heightNode.innerHTML = Translator.trans(
                        /* @Desc("H:%height% px") */ 'ibexa_image.dimensions.height',
                        { height },
                        'ibexa_fieldtypes_edit',
                    );
                };

                image.src = url;
                imageNode.setAttribute('src', url);
            });

            nameContainer.innerHTML = fileName;
            nameContainer.title = fileName;
            sizeContainer.innerHTML = fileSize;
            sizeContainer.title = fileSize;

            preview.querySelector('.ibexa-field-edit-preview__action--preview').href = URL.createObjectURL(files[0]);
            this.fieldContainer.querySelector(SELECTOR_INPUT_ALT).dispatchEvent(new CustomEvent(EVENT_CANCEL_ERROR));
        }

        resetInputField() {
            super.resetInputField();

            this.fieldContainer.querySelector('.ibexa-field-edit-preview__image-alt .ibexa-data-source__input').value = '';
        }
    }

    class IbexaImageFieldValidator extends ibexa.BaseFileFieldValidator {
        /**
         * Validates the alternative text input
         *
         * @method validateAltInput
         * @param {Event} event
         * @returns {Object}
         * @memberof IbexaStringValidator
         */
        validateAltInput(event) {
            const fileField = this.fieldContainer.querySelector(SELECTOR_INPUT_FILE);
            const dataContainer = this.fieldContainer.querySelector('.ibexa-field-edit__data');
            const isFileFieldEmpty = fileField.files && !fileField.files.length && dataContainer && !dataContainer.hasAttribute('hidden');
            const { isRequired } = event.target.dataset;
            const alreadyIsError = this.fieldContainer.classList.contains(this.classInvalid);
            const isEmpty = !event.target.value;
            const isError = alreadyIsError || (isEmpty && isRequired && !isFileFieldEmpty);
            const label = event.target.closest(SELECTOR_ALT_WRAPPER).querySelector('.ibexa-data-source__label').innerHTML;
            const result = { isError };

            if (isEmpty) {
                result.errorMessage = ibexa.errors.emptyField.replace('{fieldName}', label);
            }

            return result;
        }
    }

    doc.querySelectorAll(SELECTOR_FIELD).forEach((fieldContainer) => {
        const validator = new IbexaImageFieldValidator({
            classInvalid: 'is-invalid',
            fieldContainer,
            eventsMap: [
                {
                    selector: `${SELECTOR_INPUT_FILE}`,
                    eventName: 'change',
                    callback: 'validateInput',
                    errorNodeSelectors: ['.ibexa-form-error'],
                },
                {
                    selector: SELECTOR_INPUT_ALT,
                    eventName: 'blur',
                    callback: 'validateAltInput',
                    invalidStateSelectors: ['.ibexa-data-source__field--alternativeText'],
                    errorNodeSelectors: [`${SELECTOR_ALT_WRAPPER} .ibexa-form-error`],
                },
                {
                    isValueValidator: false,
                    selector: `${SELECTOR_INPUT_FILE}`,
                    eventName: 'ibexa-invalid-file-size',
                    callback: 'showFileSizeError',
                    errorNodeSelectors: ['.ibexa-form-error'],
                },
                {
                    isValueValidator: false,
                    selector: `${SELECTOR_INPUT_FILE}`,
                    eventName: 'ibexa-invalid-file-type',
                    callback: 'showFileTypeError',
                    errorNodeSelectors: ['.ibexa-form-error'],
                },
                {
                    isValueValidator: false,
                    selector: SELECTOR_INPUT_ALT,
                    eventName: EVENT_CANCEL_ERROR,
                    callback: 'cancelErrors',
                    invalidStateSelectors: ['.ibexa-data-source__field--alternativeText'],
                    errorNodeSelectors: [`${SELECTOR_ALT_WRAPPER} .ibexa-form-error`],
                },
            ],
        });

        const inputFileFieldContainer = fieldContainer.querySelector(SELECTOR_INPUT_FILE);
        const { allowedFileTypes = [] } = inputFileFieldContainer.dataset;
        const previewField = new IbexaImageFilePreviewField({
            validator,
            fieldContainer,
            fileTypeAccept: inputFileFieldContainer.accept,
            allowedFileTypes,
        });

        previewField.init();

        ibexa.addConfig('fieldTypeValidators', [validator], true);
    });
})(window, window.document, window.ibexa, window.Translator);
