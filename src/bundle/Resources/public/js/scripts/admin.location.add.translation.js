(function (global, doc) {
    const SELECTOR_MODAL = '.ibexa-modal';
    const observerConfig = {
        attributes: true,
        attributeFilter: ['class'],
    };
    const toggleBtnDisabledState = (select) => {
        const modal = select.closest(SELECTOR_MODAL);
        if (!modal) {
            return;
        }
        const buttonCreate = modal.querySelector('.ids-btn--create-translation');
        if (!buttonCreate) {
            return;
        }

        buttonCreate.toggleAttribute('disabled', !select.value);
    };

    doc.querySelectorAll('.ibexa-translation__language-wrapper--language').forEach((select) => {
        const dropdown = select.closest('.ibexa-dropdown, .ids-dropdown');
        const observer = new MutationObserver(() => toggleBtnDisabledState(select));

        toggleBtnDisabledState(select);
        select.addEventListener('change', ({ target }) => toggleBtnDisabledState(target), false);

        if (!dropdown) {
            return;
        }

        observer.observe(dropdown, observerConfig);
    });
})(window, window.document);
