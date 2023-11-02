(function (global, doc, ibexa, React, ReactDOM) {
    const SELECTOR_FIELD = '.ibexa-field-edit--ezrichtext';
    const SELECTOR_INPUT = '.ibexa-data-source__richtext';
    const SELECTOR_ERROR_NODE = '.ibexa-form-error';
    const selectContent = (config) => {
        const udwContainer = document.querySelector('#react-udw');
        const udwRoot = ReactDOM.createRoot(udwContainer);
        const confirmHandler = (items) => {
            if (typeof config.onConfirm === 'function') {
                config.onConfirm(items);
            }

            udwRoot.unmount();
        };
        const cancelHandler = () => {
            if (typeof config.onCancel === 'function') {
                config.onCancel();
            }

            udwRoot.unmount();
        };
        const mergedConfig = { ...config, onConfirm: confirmHandler, onCancel: cancelHandler };

        udwRoot.render(React.createElement(ibexa.modules.UniversalDiscovery, mergedConfig));
    };

    ibexa.addConfig('richText.alloyEditor.callbacks.selectContent', selectContent);

    class EzRichTextValidator extends ibexa.BaseFieldValidator {
        constructor(config) {
            super(config);

            this.richtextEditor = config.richtextEditor;
        }
        /**
         * Validates the input
         *
         * @method validateInput
         * @param {Event} event
         * @returns {Object}
         * @memberof EzRichTextValidator
         */
        validateInput(event) {
            const fieldContainer = event.currentTarget.closest(SELECTOR_FIELD);
            const isRequired = fieldContainer.classList.contains('ibexa-field-edit--required');
            const label = fieldContainer.querySelector('.ibexa-field-edit__label').innerHTML;
            const isEmpty = !this.richtextEditor.getData().length;
            const isError = isRequired && isEmpty;
            const result = { isError };

            if (isError) {
                result.errorMessage = ibexa.errors.emptyField.replace('{fieldName}', label);
            }

            return result;
        }
    }

    doc.querySelectorAll(`${SELECTOR_FIELD} ${SELECTOR_INPUT}`).forEach((container) => {
        const richtextEditor = new ibexa.BaseRichText();

        richtextEditor.init(container);

        const validator = new EzRichTextValidator({
            classInvalid: 'is-invalid',
            fieldContainer: container.closest(SELECTOR_FIELD),
            richtextEditor,
            eventsMap: [
                {
                    selector: '.ibexa-data-source__input.ibexa-input--textarea',
                    eventName: 'input',
                    callback: 'validateInput',
                    errorNodeSelectors: [SELECTOR_ERROR_NODE],
                },
                {
                    selector: SELECTOR_INPUT,
                    eventName: 'blur',
                    callback: 'validateInput',
                    errorNodeSelectors: [SELECTOR_ERROR_NODE],
                    forcedInvalidElement: container.parentElement,
                },
            ],
        });

        validator.init();

        ibexa.addConfig('fieldTypeValidators', [validator], true);
    });
})(window, window.document, window.ibexa, window.React, window.ReactDOM);
