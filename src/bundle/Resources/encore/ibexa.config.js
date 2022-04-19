const addJSEntries = require('./ibexa.js.config.js');
const addCSSEntries = require('./ibexa.css.config.js');

module.exports = (Encore) => {
    addJSEntries(Encore);
    addCSSEntries(Encore);
};
