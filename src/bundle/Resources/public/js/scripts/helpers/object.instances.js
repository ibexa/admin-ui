(function(global, doc, ibexa) {
    const setInstance = (domElement, instance) => {
        domElement.ibexaInstance = instance;
    }
    const getInstance = (domElement) => {
        if (!domElement.ibexaInstance) {
            return undefined;
        }

        return domElement.ibexaInstance;
    }

    ibexa.addConfig('helpers.objectInstances', {
        setInstance,
        getInstance,
    });
})(window, window.document, window.ibexa);
