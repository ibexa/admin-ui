(function (global, doc, ibexa, Translator) {
    const formatErrorLine = (errorMessage) => {
        const errorIcon = `<svg class="ibexa-icon ibexa-icon--small ibexa-form-error__icon">
            <use xlink:href="${ibexa.helpers.icon.getIconPath('warning-triangle')}"></use>
        </svg>`;
        const container = document.createElement('em');
        const errorMessageNode = document.createTextNode(errorMessage);

        container.classList.add('ibexa-form-error__row');
        container.insertAdjacentHTML('beforeend', errorIcon);
        container.append(errorMessageNode);

        return container;
    };
    const checkIsEmpty = (field) => {
        let errorMessage = '';
        const input = field.querySelector('.ibexa-input');
        const label = field.querySelector('.ibexa-label');

        if (label) {
            const fieldName = label.innerText;

            errorMessage = Translator.trans(/*@Desc("%fieldName% Field is required")*/ 'error.required.field', { fieldName }, 'forms');
        } else {
            errorMessage = Translator.trans(/*@Desc("This value should not be blank")*/ 'error.required.field_not_blank', {}, 'forms');
        }

        return {
            isValid: input.value,
            errorMessage,
        };
    };
    const validateIsEmptyField = (field) => {
        const input = field.querySelector('.ibexa-input');
        const errorWrapper = field.querySelector('.ibexa-form-error');
        const validatorOutput = checkIsEmpty(field);
        const { isValid, errorMessage } = validatorOutput;

        input.classList.toggle('is-invalid', !isValid);
        errorWrapper.innerText = '';

        if (!isValid) {
            errorWrapper.append(formatErrorLine(errorMessage));
        }

        return validatorOutput;
    };
    ibexa.addConfig('helpers.formValidation', {
        formatErrorLine,
        validateIsEmptyField,
    });
})(window, window.document, window.ibexa, window.Translator);
