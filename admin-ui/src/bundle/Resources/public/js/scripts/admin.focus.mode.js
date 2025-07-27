(function (global, doc) {
    const FORM_SELECTOR = 'form[name=focus_mode_change]';
    const form = doc.querySelector(FORM_SELECTOR);

    if (form) {
        form.querySelectorAll('input[type=checkbox]').forEach((input) => {
            input.addEventListener('change', () => {
                form.requestSubmit();
            });
        });
    }
})(window, window.document);
