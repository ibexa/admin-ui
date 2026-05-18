(function (global, doc) {
    const SELECTOR_MODAL = '.ibexa-modal';
    const observerConfig = {
        attributes: true,
        attributeFilter: ['class'],
    };
    const toggleBtnDisabledState = (select) => {
        const modal = select.closest(SELECTOR_MODAL);
        const buttonCreate = modal.querySelector('.ibexa-btn--create-translation');
        buttonCreate.toggleAttribute('disabled', !select.value);
    };

    doc.querySelectorAll('.ibexa-translation__language-wrapper--language').forEach((select) => {
        const dropdown = select.closest('.ibexa-dropdown');
        const observer = new MutationObserver(() => toggleBtnDisabledState(select));

        toggleBtnDisabledState(select);
        select.addEventListener('change', ({ target }) => toggleBtnDisabledState(target), false);

        observer.observe(dropdown, observerConfig);
    });
})(window, window.document);
