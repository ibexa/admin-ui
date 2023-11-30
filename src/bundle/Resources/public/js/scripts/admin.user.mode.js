(function (global, doc, ibexa) {
    const FORM_SELECTOR = 'form[name=user_mode_change]';
    const form = doc.querySelector(FORM_SELECTOR);

    form.querySelectorAll('input[type=checkbox]').forEach((input) => {
        input.addEventListener('change', (e) => {
            form.submit();
        })
    })
})(window, window.document, window.ibexa);
