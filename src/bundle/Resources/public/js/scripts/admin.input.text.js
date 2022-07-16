(function (global, doc) {
    const togglePasswordVisibility = (event) => {
        const passwordTogglerBtn = event.currentTarget;
        const passwordShowIcon = passwordTogglerBtn.querySelector('.ibexa-input-text-wrapper__password-show');
        const passwordHideIcon = passwordTogglerBtn.querySelector('.ibexa-input-text-wrapper__password-hide');
        const inputTextWrapper = passwordTogglerBtn.closest('.ibexa-input-text-wrapper');
        const input = inputTextWrapper.querySelector('.ibexa-input--text');

        if (input) {
            const inputTypeToSet = input.type === 'password' ? 'text' : 'password';

            input.type = inputTypeToSet;
            passwordShowIcon.classList.toggle('d-none');
            passwordHideIcon.classList.toggle('d-none');
        }
    };
    const clearText = ({ currentTarget }) => {
        const inputWrapper = currentTarget.closest('.ibexa-input-text-wrapper');
        const textInput = inputWrapper.querySelector('.ibexa-input--text');

        textInput.value = '';
        textInput.dispatchEvent(new Event('input'));

        if (!textInput.readOnly) {
            textInput.select();
        }

        if (currentTarget.hasAttribute('data-send-form-after-clearing')) {
            currentTarget.closest('form').submit();
        }
    };
    const attachListenersToAllInputs = () => {
        const textInputClearBtns = doc.querySelectorAll('.ibexa-input-text-wrapper__action-btn--clear');
        const passwordTogglersBtns = doc.querySelectorAll('.ibexa-input-text-wrapper__action-btn--password-toggler');

        textInputClearBtns.forEach((clearBtn) => clearBtn.addEventListener('click', clearText, false));
        passwordTogglersBtns.forEach((passwordTogglerBtn) => passwordTogglerBtn.addEventListener('click', togglePasswordVisibility, false));
    };

    doc.body.addEventListener('ibexa-inputs:added', attachListenersToAllInputs, false);

    attachListenersToAllInputs();
})(window, window.document);
