(function (global, doc) {
    const INPUT_PADDING = 12;
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
        recalculateStyling();
    };
    const recalculateInputStyling = (inputActionsContainer) => {
        const input = inputActionsContainer.closest('.ibexa-input-text-wrapper').querySelector('input');

        if (!input) {
            return;
        }

        const { width: actionsWidth } = inputActionsContainer.getBoundingClientRect();

        input.style.paddingRight = `${actionsWidth + INPUT_PADDING}px`;
    };
    const recalculateStyling = () => {
        const inputActionsContainers = doc.querySelectorAll('.ibexa-input-text-wrapper__actions');

        inputActionsContainers.forEach((inputActionsContainer) => {
            const inputActionsContainerObserver = new ResizeObserver(() => {
                recalculateInputStyling(inputActionsContainer);
            });

            inputActionsContainerObserver.observe(inputActionsContainer);
            recalculateInputStyling(inputActionsContainer);
        });
    };

    doc.body.addEventListener('ibexa-inputs:added', attachListenersToAllInputs, false);
    doc.body.addEventListener('ibexa-inputs:recalculate-styling', recalculateStyling, false);

    attachListenersToAllInputs();
})(window, window.document);
