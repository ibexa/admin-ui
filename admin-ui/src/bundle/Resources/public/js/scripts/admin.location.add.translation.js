(function (global, doc) {
    const SELECTOR_MODAL = '.ibexa-modal';
    const toggleBtnDisabledState = (select) => {
        const modal = select.closest(SELECTOR_MODAL);
        const buttonCreate = modal.querySelector('.ibexa-btn--create-translation');

        buttonCreate.toggleAttribute('disabled', !select.value);
    };

    doc.querySelectorAll('.ibexa-translation__language-wrapper--language').forEach((select) => {
        toggleBtnDisabledState(select);

        select.addEventListener('change', toggleBtnDisabledState, false);
    });
})(window, window.document);
