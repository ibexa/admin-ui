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
        const input = inputWrapper.querySelector('.ibexa-input--text, .ibexa-input--date');

        input.value = '';
        input.dispatchEvent(new Event('input'));

        if (!input.readOnly) {
            input.select();
        }

        if (currentTarget.hasAttribute('data-send-form-after-clearing')) {
            currentTarget.closest('form').submit();
        }
    };
    const attachListenersToAllInputs = () => {
        const inputClearBtns = doc.querySelectorAll(`
                .ibexa-input--text + .ibexa-input-text-wrapper__actions .ibexa-input-text-wrapper__action-btn--clear,
                .ibexa-input--date + .ibexa-input-text-wrapper__actions .ibexa-input-text-wrapper__action-btn--clear
        `);
        const passwordTogglerBtns = doc.querySelectorAll('.ibexa-input-text-wrapper__action-btn--password-toggler');

        inputClearBtns.forEach((clearBtn) => clearBtn.addEventListener('click', clearText, false));
        passwordTogglerBtns.forEach((passwordTogglerBtn) => passwordTogglerBtn.addEventListener('click', togglePasswordVisibility, false));
    };
    const handleInputChange = ({ target: { value } }, btn) => {
        btn.disabled = value === '';
    };
    const initExtraBtns = (event) => {
        const extraBtns = doc.querySelectorAll('.ibexa-input-text-wrapper__action-btn--extra-btn');

        extraBtns.forEach((btn) => {
            const input = btn.closest('.ibexa-input-text-wrapper').querySelector('input');

            if (!input) {
                return;
            }

            const marginClearButton = 5;
            const marginWidth = 24;
            const paddingRight = `${btn.offsetWidth + marginClearButton + marginWidth}px`;

            btn.disabled = true;
            input.style.paddingRight = paddingRight;
            input.addEventListener('input', (inputEvent) => handleInputChange(inputEvent, btn), false);
        });
    };

    doc.body.addEventListener('ibexa-inputs:added', attachListenersToAllInputs, false);
    doc.body.addEventListener('ibexa-page-builder:iframe-loaded', initExtraBtns, false);

    attachListenersToAllInputs();
})(window, window.document);
