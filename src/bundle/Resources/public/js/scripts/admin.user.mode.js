(function (global, doc) {
    const FORM_SELECTOR = 'form[name=user_mode_change]';
    const form = doc.querySelector(FORM_SELECTOR);

    if (form) {
        form.querySelectorAll('input[type=checkbox]').forEach((input) => {
            input.addEventListener('change', () => {
                form.submit();
            });
        });
    }
})(window, window.document);
