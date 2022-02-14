(function (global, doc, ibexa) {
    const dropdowns = doc.querySelectorAll('.ibexa-dropdown:not(.ibexa-dropdown--custom-init)');

    dropdowns.forEach((dropdownContainer) => {
        const dropdown = new ibexa.core.Dropdown({
            container: dropdownContainer,
        });

        dropdown.init();
    });
})(window, window.document, window.ibexa);
