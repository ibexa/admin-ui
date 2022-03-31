const path = require('path');

module.exports = (Encore) => {
    Encore.addEntry('ibexa-admin-ui-layout-css', [
        path.resolve(__dirname, '../public/scss/ibexa-bootstrap.scss'),
        path.resolve(__dirname, '../public/scss/ibexa.scss'),
        path.resolve(__dirname, '../public/scss/ui/ibexa-modules.scss'),
        path.resolve('./vendor/ibexa/admin-ui-assets/src/bundle/Resources/public/vendors/flatpickr/dist/flatpickr.min.css'),
    ])
        .addEntry('ibexa-admin-ui-content-edit-parts-css', [
            path.resolve('./vendor/ibexa/admin-ui-assets/src/bundle/Resources/public/vendors/leaflet/dist/leaflet.css'),
        ])
        .addEntry('ibexa-admin-ui-location-view-css', [
            path.resolve('./vendor/ibexa/admin-ui-assets/src/bundle/Resources/public/vendors/leaflet/dist/leaflet.css'),
        ])
        .addEntry('ibexa-admin-ui-security-base-css', [
            path.resolve(__dirname, '../public/scss/ibexa-bootstrap.scss'),
            path.resolve(__dirname, '../public/scss/ibexa.scss'),
        ]);
};
