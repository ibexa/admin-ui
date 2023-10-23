(function (global, doc) {
    const createActions = doc.querySelectorAll('.ibexa-extra-actions--create');

    if (!createActions.length) {
        return;
    }

    createActions.forEach((container) => {
        const radioInputs = container.querySelectorAll('.form-check [type="radio"]');
        const submitBtn = container.querySelector('.ibexa-extra-actions__btn--confirm');
        const cancelBtn = container.querySelector('.ibexa-extra-actions__btn--cancel');
        const closeBtn = container.querySelector('.ibexa-extra-actions__header .ibexa-btn--close');

        cancelBtn.addEventListener(
            'click',
            () => {
                closeBtn.click();
            },
            false,
        );
        radioInputs.forEach((radioInput) =>
            radioInput.addEventListener(
                'change',
                (event) => {
                    const selectedItems = container.querySelectorAll('.ibexa-instant-filter__group-item--selected');
                    const itemToSelect = event.currentTarget.closest('.ibexa-instant-filter__group-item');

                    selectedItems.forEach((selectedItem) => selectedItem.classList.remove('ibexa-instant-filter__group-item--selected'));
                    itemToSelect.classList.add('ibexa-instant-filter__group-item--selected');

                    submitBtn.removeAttribute('disabled');
                },
                false,
            ),
        );
    });
})(window, window.document);
