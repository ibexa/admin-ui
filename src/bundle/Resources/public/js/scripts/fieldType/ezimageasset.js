(function (global, doc, ibexa, React, ReactDOM, Translator, Routing) {
    const SELECTOR_FIELD = '.ibexa-field-edit--ezimageasset';
    const SELECTOR_INPUT_FILE = 'input[type="file"]';
    const SELECTOR_INPUT_DESTINATION_CONTENT_ID = '.ibexa-data-source__destination-content-id';
    const token = doc.querySelector('meta[name="CSRF-Token"]').content;
    const { showErrorNotification } = ibexa.helpers.notification;
    const { showSuccessNotification } = ibexa.helpers.notification;
    const { getJsonFromResponse } = ibexa.helpers.request;
    const { imageAssetMapping } = ibexa.adminUiConfig;

    class EzImageAssetPreviewField extends ibexa.BasePreviewField {
        constructor(props) {
            super(props);

            this.showPreviewEventName = 'ibexa-image-asset:show-preview';
        }
        /**
         * Creates a new Image Asset
         *
         * @method createAsset
         * @param {File} file
         * @param {String} languageCode
         */
        createAsset(file, languageCode) {
            const assetCreateUri = Routing.generate('ibexa.asset.upload_image');
            const form = new FormData();

            form.append('languageCode', languageCode);
            form.append('file', file);

            const options = {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-Token': token,
                },
                body: form,
                mode: 'same-origin',
                credentials: 'same-origin',
            };

            this.toggleLoading(true);

            fetch(assetCreateUri, options)
                .then(getJsonFromResponse)
                .then(ibexa.helpers.request.handleRequest)
                .then(this.onAssetCreateSuccess.bind(this))
                .catch(this.onAssetCreateFailure.bind(this));
        }

        /**
         * Handle a successfully created Image Asset
         *
         * @method onAssetCreateSuccess
         * @param {Object} response
         */
        onAssetCreateSuccess(response) {
            const { destinationContent } = response;

            this.updateData(destinationContent.id, destinationContent.name, destinationContent.locationId, response.value);
            this.toggleLoading(false);

            showSuccessNotification(
                Translator.trans(
                    /* @Desc("The image has been published and can now be reused") */ 'ezimageasset.create.message.success',
                    {},
                    'ibexa_fieldtypes_edit',
                ),
            );
        }

        /**
         * Handle a failure while creating Image Asset
         *
         * @method onAssetCreateFailure
         */
        onAssetCreateFailure(error) {
            const message = Translator.trans(
                /* @Desc("Error while creating Image Asset: %error%") */ 'ezimageasset.create.message.error',
                { error: error.message },
                'ibexa_fieldtypes_edit',
            );

            this.toggleLoading(false);
            showErrorNotification(message);
        }

        /**
         * Loads selected Image Asset
         *
         * @method loadAsset
         * @param {Object} response
         */
        loadAsset(response) {
            const imageField = response.ContentInfo.Content.CurrentVersion.Version.Fields.field.find((field) => {
                return field.fieldDefinitionIdentifier === imageAssetMapping['contentFieldIdentifier'];
            });

            this.updateData(
                response.ContentInfo.Content._id,
                response.ContentInfo.Content.TranslatedName,
                response.id,
                imageField.fieldValue,
            );
        }

        /**
         * Toggle visibility of the loading spinner
         *
         * @method toggleLoading
         * @param {boolean} show
         */
        toggleLoading(show) {
            this.fieldContainer.classList.toggle('ibexa-field-edit--is-preview-loading', show);
        }

        /**
         * Updates Image Asset preview data
         *
         * @method updateData
         * @param {Number} destinationContentId
         * @param {String} destinationContentName
         * @param {Number} destinationLocationId
         * @param {Object} image
         */
        updateData(destinationContentId, destinationContentName, destinationLocationId, image) {
            const preview = this.fieldContainer.querySelector('.ibexa-field-edit__preview');
            const previewVisual = preview.querySelector('.ibexa-field-edit-preview__visual');
            const previewImg = preview.querySelector('.ibexa-field-edit-preview__media');
            const previewAlt = preview.querySelector('.ibexa-field-edit-preview__image-alt input');
            const previewActionPreview = preview.querySelector('.ibexa-field-edit-preview__action--preview');
            const assetNameContainer = preview.querySelector('.ibexa-field-edit-preview__file-name');
            const destinationLocationUrl = Routing.generate('ibexa.content.view', {
                contentId: destinationContentId,
                locationId: destinationLocationId,
            });
            const additionalData = Array.isArray(image.additionalData) ? '{}' : JSON.stringify(image.additionalData);

            previewVisual.setAttribute('data-additional-data', additionalData);
            previewImg.setAttribute('src', image ? image.uri : '//:0');
            previewImg.classList.toggle('d-none', image === null);
            previewAlt.value = image.alternativeText;
            previewActionPreview.setAttribute('href', destinationLocationUrl);
            assetNameContainer.innerHTML = destinationContentName;
            assetNameContainer.setAttribute('href', destinationLocationUrl);

            this.inputDestinationContentId.value = destinationContentId;
            this.inputField.value = '';
            this.showPreview();
        }

        /**
         * Open UDW to select an existing Image Asset
         *
         * @method openUDW
         * @param {Event} event
         */
        openUDW(event) {
            event.preventDefault();

            const udwContainer = doc.getElementById('react-udw');
            const udwRoot = ReactDOM.createRoot(udwContainer);
            const config = JSON.parse(event.currentTarget.dataset.udwConfig);
            const title = Translator.trans(/*@Desc("Select Image Asset")*/ 'ezimageasset.title', {}, 'ibexa_universal_discovery_widget');
            const closeUDW = () => udwRoot.unmount();
            const onCancel = closeUDW;
            const onConfirm = (items) => {
                closeUDW();
                this.loadAsset(items[0]);
            };

            udwRoot.render(
                React.createElement(ibexa.modules.UniversalDiscovery, {
                    onConfirm,
                    onCancel,
                    title,
                    ...config,
                }),
            );
        }

        /**
         * Checks if file size is an allowed limit
         *
         * @method handleInputChange
         * @param {Event} event
         */
        handleInputChange(event) {
            const [file] = event.currentTarget.files;
            const { languageCode } = event.currentTarget.dataset;
            const isFileSizeLimited = this.maxFileSize > 0;
            const maxFileSizeExceeded = isFileSizeLimited && file.size > this.maxFileSize;

            if (maxFileSizeExceeded) {
                this.resetInputField();
                return;
            }

            this.fieldContainer.querySelector('.ibexa-field-edit__option--remove-media').checked = false;

            this.createAsset(file, languageCode);
        }

        /**
         * Resets input field state
         *
         * @method resetInputField
         */
        resetInputField() {
            super.resetInputField();

            this.inputDestinationContentId.value = '';
        }

        /**
         * Initializes the preview
         *
         * @method init
         */
        init() {
            super.init();

            this.btnSelect = this.fieldContainer.querySelector('.ibexa-data-source__btn-select');
            this.btnSelect.addEventListener('click', this.openUDW.bind(this), false);
            this.inputDestinationContentId = this.fieldContainer.querySelector(SELECTOR_INPUT_DESTINATION_CONTENT_ID);
        }
    }

    doc.querySelectorAll(SELECTOR_FIELD).forEach((fieldContainer) => {
        const validator = new ibexa.BaseFileFieldValidator({
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
            ],
        });

        const inputFileFieldContainer = fieldContainer.querySelector(SELECTOR_INPUT_FILE);
        const { allowedFileTypes = [] } = inputFileFieldContainer.dataset;
        const previewField = new EzImageAssetPreviewField({
            validator,
            fieldContainer,
            fileTypeAccept: inputFileFieldContainer.accept,
            allowedFileTypes,
        });

        previewField.init();

        ibexa.addConfig('fieldTypeValidators', [validator], true);
    });
})(window, window.document, window.ibexa, window.React, window.ReactDOM, window.Translator, window.Routing);
