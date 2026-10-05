(function (global, doc, ibexa) {
    const form = doc.querySelector('.ibexa-section-edit__form');
    const inputsToValidate = [...form.querySelectorAll('.ibexa-input[required]')];
    const validateInput = (input) => ibexa.helpers.formValidation.validateIsEmptyField(input.closest('.form-group'));
    const validateForm = (event) => {
        const isFormValid = inputsToValidate.map(validateInput).every(({ isValid }) => isValid);

        if (!isFormValid) {
            event.preventDefault();

            form.querySelector('.ibexa-input.is-invalid').focus();
        }
    };

    form.setAttribute('novalidate', true);
    form.addEventListener('submit', validateForm, false);
    inputsToValidate.forEach((input) => input.addEventListener('blur', () => validateInput(input), false));
})(window, window.document, window.ibexa);
