(function (global, doc) {
    const EMAIL_REGEXP =
        /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

    const findErrorType = (input) => {
        const value = input.value.trim();

        if (!value) {
            return 'empty';
        }

        if (input.type === 'email' && !EMAIL_REGEXP.test(value)) {
            return 'invalid-email';
        }

        return null;
    };
    const toggleFieldState = (input, errorType) => {
        const field = input.closest('.form-group');
        const label = field.querySelector('.ids-label');
        const hasError = errorType !== null;

        field.classList.toggle('has-error', hasError);
        input.classList.toggle('ids-input--error', hasError);
        label?.classList.toggle('ids-label--error', hasError);

        field.querySelectorAll('.ibexa-form-error__row').forEach((errorRow) => {
            errorRow.hidden = errorRow.dataset.errorType !== errorType;
        });
    };
    const checkIsInputValid = (input) => {
        const errorType = findErrorType(input);

        toggleFieldState(input, errorType);

        return errorType === null;
    };
    const handleSubmit = (event) => {
        const form = event.currentTarget;
        const inputs = [...form.querySelectorAll('input[required]')];
        const invalidInputs = inputs.filter((input) => !checkIsInputValid(input));

        if (invalidInputs.length) {
            event.preventDefault();

            invalidInputs[0].focus();
        }
    };
    const handleInput = (event) => {
        const input = event.currentTarget;

        if (input.closest('.form-group').classList.contains('has-error')) {
            checkIsInputValid(input);
        }
    };

    doc.querySelectorAll('form[data-account-validate]').forEach((form) => {
        form.addEventListener('submit', handleSubmit, false);

        form.querySelectorAll('input[required]').forEach((input) => {
            input.addEventListener('input', handleInput, false);
        });
    });
})(window, window.document);
