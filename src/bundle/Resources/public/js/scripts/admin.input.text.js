(function(global, doc, $, ibexa) {
    const clearText = (event) => {
        const inputWrapper = event.target.closest('.ibexa-input-text-wrapper');
        const textInput = inputWrapper.querySelector('.ibexa-input--text');

        textInput.value = '';
        textInput.dispatchEvent(new Event('input'));
        textInput.select();
    };
    const attachListenersToAllInputs = () => {
        const textInputClearBtns = doc.querySelectorAll('.ibexa-input-text-wrapper__action-btn--clear');

        textInputClearBtns.forEach((clearBtn) => clearBtn.addEventListener('click', clearText, false));
    };

    doc.body.addEventListener('ibexa-new-inputs-added', attachListenersToAllInputs, false);

    attachListenersToAllInputs();
})(window, window.document, window.jQuery, window.ibexa);
