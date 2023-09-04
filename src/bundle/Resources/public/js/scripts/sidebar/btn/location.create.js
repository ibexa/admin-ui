(function (global, doc) {
    const createActions = doc.querySelectorAll('.ibexa-extra-actions--create');

    if (!createActions.length) {
        return;
    }

    createActions.forEach((container) => {
        const radioInputs = container.querySelectorAll('.form-check [type="radio"]');
        const form = container.querySelector('form');

        radioInputs.forEach((radioInput) => radioInput.addEventListener('change', () => form.submit(), false));
    });
})(window, window.document);
