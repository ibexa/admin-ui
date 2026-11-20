const encoreModule = require('@symfony/webpack-encore');
const path = require('path');

// @symfony/webpack-encore 7+ is an ES module, older versions are CommonJS
const Encore = encoreModule.__esModule ? encoreModule.default : encoreModule;

// @symfony/webpack-encore 7+ returns the webpack config as a Promise, older versions return it directly
const setConfigName = (config, name) => {
    if (typeof config?.then === 'function') {
        return config.then((resolvedConfig) => setConfigName(resolvedConfig, name));
    }

    config.name = name;

    return config;
};

Encore.reset();
Encore.setOutputPath('public/assets/react/build')
    .setPublicPath('/assets/react/build')
    .addAliases({
        '@ibexa-admin-ui-assets': path.resolve('./vendor/ibexa/admin-ui-assets'),
    })
    .enableSassLoader()
    .disableSingleRuntimeChunk();

Encore.addEntry('ibexa-admin-ui-react-load-js', [path.resolve(__dirname, '../public/js/scripts/react.load.js')]);

const customConfigReact = setConfigName(Encore.getWebpackConfig(), 'react');

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

const customConfigReactDOM = setConfigName(Encore.getWebpackConfig(), 'reactDOM');

module.exports = [customConfigReact, customConfigReactDOM];
