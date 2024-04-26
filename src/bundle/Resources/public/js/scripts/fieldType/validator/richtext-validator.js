const ibexa = window.ibexa;

class RichTextValidator extends ibexa.BaseFieldValidator {
    constructor(config) {
        super({ richtextEditor, selectorField, labelSelector });

        this.richtextEditor = richtextEditor;
        this.selectorField = selectorField;
        this.labelSelector = labelSelector;
    }

    /**
     * Validates the input
     *
     * @method validateInput
     * @param {Event} event
     * @returns {Object}
     * @memberof RichTextValidator
     */
    validateInput(event) {
        const fieldContainer = event.currentTarget.closest(this.selectorField);
        const isRequired = fieldContainer.classList.contains('ibexa-field-edit--required');
        const label = fieldContainer.querySelector(this.labelSelector).innerHTML;
        const isEmpty = !this.richtextEditor.getData().length;
        const isError = isRequired && isEmpty;
        const result = { isError };

        if (isError) {
            result.errorMessage = ibexa.errors.emptyField.replace('{fieldName}', label);
        }

        return result;
    }
}

const initValidator = (container, selectorField, selectorErrorNone, selectorInput, labelSelector, richtextEditor) => {
    const validator = new RichTextValidator({
        classInvalid: 'is-invalid',
        fieldContainer: container.closest(selectorField),
        eventsMap: [
            {
                selector: '.ibexa-data-source__input.ibexa-input--textarea',
                eventName: 'input',
                callback: 'validateInput',
                errorNodeSelectors: [selectorErrorNone],
            },
            {
                selector: selectorInput,
                eventName: 'blur',
                callback: 'validateInput',
                errorNodeSelectors: [selectorErrorNone],
            },
        ],
        richtextEditor,
        selectorField,
        selectorInput,
        labelSelector,
    });

    validator.init();
};

export default initValidator;
