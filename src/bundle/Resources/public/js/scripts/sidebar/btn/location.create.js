(function (global, doc) {
    const createActions = doc.querySelectorAll('.ibexa-extra-actions--create');

    if (!createActions.length) {
        return;
    }

    createActions.forEach((container) => {
        const btns = container.querySelectorAll('.form-check [type="radio"]');
        const form = container.querySelector('form');

        btns.forEach((btn) => btn.addEventListener('change', () => form.submit(), false));
    });
})(window, window.document);
