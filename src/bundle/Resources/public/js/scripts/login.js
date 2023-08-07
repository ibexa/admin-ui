(function (global, doc) {
    const AUTOFILL_TIMEOUT = 500;
    const loginBtn = doc.querySelector('.ibexa-login__btn--sign-in');
    const nameInput = doc.querySelector('.ibexa-login__input--name');
    const passwordInput = doc.querySelector('.ibexa-login__input--password');
    const toggleLoginBtnState = () => {
        const shouldBeDisabled = !nameInput.value || !passwordInput.value;

        loginBtn.toggleAttribute('disabled', shouldBeDisabled);
    };
    const handleAutofill = () => {
        const isNameInputAutofilled = nameInput.matches(':-webkit-autofill');
        const isPasswordInputAutofilled = nameInput.matches(':-webkit-autofill');
        const isAutofilled = isNameInputAutofilled && isPasswordInputAutofilled;

        if (isAutofilled) {
            loginBtn.removeAttribute('disabled');
        }
    };

    if (loginBtn) {
        nameInput.addEventListener('keyup', toggleLoginBtnState, false);
        nameInput.addEventListener('change', toggleLoginBtnState, false);
        passwordInput.addEventListener('keyup', toggleLoginBtnState, false);
        passwordInput.addEventListener('change', toggleLoginBtnState, false);

        toggleLoginBtnState();

        global.setTimeout(handleAutofill, AUTOFILL_TIMEOUT);
    }
})(window, window.document);
