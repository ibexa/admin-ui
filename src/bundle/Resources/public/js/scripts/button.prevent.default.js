(function (global, doc) {
    doc.querySelectorAll('.ids-button--prevented').forEach((btn) => btn.addEventListener('click', (event) => event.preventDefault(), false));
})(window, window.document);
