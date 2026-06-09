(function (global, doc) {
    const modal = doc.querySelector('#ibexa-modal--custom-url-alias');

    if (modal) {
        const discardBtns = modal.querySelectorAll('[data-bs-dismiss="modal"]');
        const submitBtn = modal.querySelector('#custom_url_add_add');
        const input = modal.querySelector('#custom_url_add_path') || modal.querySelector('[name="custom_url_add[path]"]');
        const siteRootCheckbox = modal.querySelector('[name="custom_url_add[site_root]"]');
        const toggleButtonState = () => {
            if (!input || !submitBtn) {
                return;
            }

            const hasValue = input.value.trim().length !== 0;
            const methodName = hasValue ? 'removeAttribute' : 'setAttribute';

            submitBtn[methodName]('disabled', true);
        };
        const clearValues = () => {
            if (!input) {
                return;
            }

            input.value = '';
            toggleButtonState();
        };
        const toggleSiteAccessSelect = (event) => {
            const isChecked = event.target.checked;
            const siteAccessSelect = modal.querySelector('.ibexa-custom-url-from__item--siteacces .ibexa-dropdown');

            if (!siteAccessSelect) {
                return;
            }

            siteAccessSelect.classList.toggle('ibexa-dropdown--is-disabled', isChecked);
        };

        if (input) {
            input.addEventListener('input', toggleButtonState, false);
        }

        if (siteRootCheckbox) {
            siteRootCheckbox.addEventListener('change', toggleSiteAccessSelect, false);
        }

        discardBtns.forEach((btn) => btn.addEventListener('click', clearValues, false));
    }
})(window, window.document);
