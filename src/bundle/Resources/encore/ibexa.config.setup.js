const path = require('path');

module.exports = (Encore) => {
    Encore.addAliases({
        '@ibexa-admin-ui': path.resolve('./vendor/ibexa/admin-ui'),
    });
};
