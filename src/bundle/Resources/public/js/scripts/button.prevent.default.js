(function (global, doc) {
    doc.querySelectorAll('.ids-btn--prevented').forEach((btn) => btn.addEventListener('click', (event) => event.preventDefault(), false));
})(window, window.document);
