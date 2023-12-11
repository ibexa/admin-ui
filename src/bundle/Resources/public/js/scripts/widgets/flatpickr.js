import flatpickrLanguages from '@ibexa-admin-ui-assets/src/bundle/Resources/public/vendors/flatpickr/dist/l10n';

(function (global, doc, ibexa, flatpickr) {
    const { backOfficeLanguage } = ibexa.adminUiConfig;
    const flatpickrLanguage = flatpickrLanguages[backOfficeLanguage] ?? flatpickrLanguages.default;

    flatpickr.localize(flatpickrLanguage);
})(window, window.document, window.ibexa, window.flatpickr);
