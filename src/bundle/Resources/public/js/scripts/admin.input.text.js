(function (global, doc) {
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

        textInputClearBtns.forEach((clearBtn) => clearBtn.addEventListener('click', clearText, false));
    };

    doc.body.addEventListener('ibexa-inputs:added', attachListenersToAllInputs, false);

    attachListenersToAllInputs();
})(window, window.document);
