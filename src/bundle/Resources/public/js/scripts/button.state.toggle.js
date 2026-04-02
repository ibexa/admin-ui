(function (global, doc) {
    const SELECTOR_TABLE_CHECKBOX = '.ids-input--checkbox';
    const toggleForms = doc.querySelectorAll('.ibexa-toggle-btn-state');

    toggleForms.forEach((toggleForm) => {
        const checkboxes = [...toggleForm.querySelectorAll(`.ibexa-table__cell--has-checkbox ${SELECTOR_TABLE_CHECKBOX}`)];
        const buttonRemove = doc.querySelector(toggleForm.dataset.toggleButtonId);

        if (!buttonRemove) {
            return;
        }

        const toggleButtonState = () => {
            const isAnythingSelected = checkboxes.some((el) => el.checked);

            buttonRemove.disabled = !isAnythingSelected;
        };

        toggleButtonState();
        checkboxes.forEach((checkbox) => checkbox.addEventListener('change', toggleButtonState, false));
    });
})(window, window.document);
