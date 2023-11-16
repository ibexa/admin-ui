const path = require('path');

module.exports = (Encore) => {
    Encore.addAliases({
        '@ibexa-admin-ui': path.resolve('./vendor/ibexa/admin-ui'),
        '@ibexa-admin-ui-modules': path.resolve('./vendor/ibexa/admin-ui/src/bundle/ui-dev/src/modules'),
    });
};
