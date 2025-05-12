(function (global, doc, ibexa) {
    const SELECTOR_FIELD = '.ibexa-field-edit--ezbinaryfile';

    class IbexaBinaryFilePreviewField extends ibexa.BasePreviewField {
        /**
         * Loads dropped file preview
         *
         * @param {Event} event
         */
        loadDroppedFilePreview(event) {
            const preview = this.fieldContainer.querySelector('.ibexa-field-edit__preview');
            const nameContainer = preview.querySelector('.ibexa-field-edit-preview__file-name');
            const sizeContainer = preview.querySelector('.ibexa-field-edit-preview__file-size');
            const files = [].slice.call(event.target.files);
            const fileSize = this.formatFileSize(files[0].size);
            const { escapeHTML } = ibexa.helpers.text;
            const fileName = escapeHTML(files[0].name);

            nameContainer.innerHTML = fileName;
            nameContainer.title = fileName;
            sizeContainer.innerHTML = fileSize;
            sizeContainer.title = fileSize;

            preview.querySelector('.ibexa-field-edit-preview__action--preview').href = URL.createObjectURL(files[0]);
        }
    }

    doc.querySelectorAll(SELECTOR_FIELD).forEach((fieldContainer) => {
        const validator = new ibexa.BaseFileFieldValidator({
            classInvalid: 'is-invalid',
            fieldContainer,
            eventsMap: [
                {
                    selector: `input[type="file"]`,
                    eventName: 'change',
                    callback: 'validateInput',
                    errorNodeSelectors: ['.ibexa-form-error'],
                },
                {
                    isValueValidator: false,
                    selector: `input[type="file"]`,
                    eventName: 'ibexa-invalid-file-size',
                    callback: 'showFileSizeError',
                    errorNodeSelectors: ['.ibexa-form-error'],
                },
                {
                    isValueValidator: false,
                    selector: `input[type="file"]`,
                    eventName: 'ibexa-invalid-file-type',
                    callback: 'showFileTypeError',
                    errorNodeSelectors: ['.ibexa-form-error'],
                },
            ],
        });
        const previewField = new IbexaBinaryFilePreviewField({
            validator,
            fieldContainer,
        });

        previewField.init();

        ibexa.addConfig('fieldTypeValidators', [validator], true);
    });
})(window, window.document, window.ibexa);
