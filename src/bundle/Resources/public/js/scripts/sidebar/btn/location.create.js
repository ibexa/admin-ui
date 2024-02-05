(function (global, doc) {
    const createActions = doc.querySelectorAll('.ibexa-extra-actions--create');
    const bindCreateActionsEvents = (container) => {
        const radioInputs = container.querySelectorAll('.form-check [type="radio"]');
        const submitBtn = container.querySelector('.ibexa-extra-actions__btn--confirm');

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
    };

    doc.body.addEventListener('ibexa-instant-filters:add-group', (event) => {
        const createActionsContainer = event.detail.container.closest('.ibexa-extra-actions--create');

        bindCreateActionsEvents(createActionsContainer);
    });

    if (!createActions.length) {
        return;
    }

    createActions.forEach(bindCreateActionsEvents);
})(window, window.document);
