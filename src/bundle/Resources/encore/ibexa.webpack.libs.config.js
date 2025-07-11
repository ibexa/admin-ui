const Encore = require('@symfony/webpack-encore');
const path = require('path');

Encore.reset();
Encore.setOutputPath('public/assets/react/build')
    .setPublicPath('/assets/react/build')
    .addAliases({
        '@ibexa-admin-ui-assets': path.resolve('./vendor/ibexa/admin-ui-assets'),
    })
    .enableSassLoader()
    .disableSingleRuntimeChunk();

Encore.addEntry('ibexa-admin-ui-react-load-js', [path.resolve(__dirname, '../public/js/scripts/react.load.js')]);

const customConfigReact = Encore.getWebpackConfig();

customConfigReact.name = 'react';

Encore.reset();
Encore.setOutputPath('public/assets/react-dom/build')
    .setPublicPath('/assets/react-dom/build')
    .addExternals({
        react: 'React',
    })
    .addAliases({
        '@ibexa-admin-ui-assets': path.resolve('./vendor/ibexa/admin-ui-assets'),
    })
    .enableSassLoader()
    .disableSingleRuntimeChunk();

Encore.addEntry('ibexa-admin-ui-react-dom-load-js', [path.resolve(__dirname, '../public/js/scripts/react.dom.load.js')]);

const customConfigReactDOM = Encore.getWebpackConfig();

customConfigReactDOM.name = 'reactDOM';

module.exports = [customConfigReact, customConfigReactDOM];
