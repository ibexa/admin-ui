(function(global, doc, ibexa) {
    const set = (domElement, instance) => {
        domElement.ibexaInstance = instance;
    }
    const get = (domElement) => {
        if (!domElement.ibexaInstance) {
            throw new Error('This DOM element doesn\'t have any object instance associated');
        }

        return domElement.ibexaInstance;
    }

    ibexa.addConfig('helpers.objectInstances', {
        set,
        get,
    });
})(window, window.document, window.ibexa);
