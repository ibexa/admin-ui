(function (global, doc, ibexa) {
    const setInstance = (domElement, instance) => {
        if (domElement.ibexaInstance) {
            throw new Error('Instance for this DOM element already exists!');
        }

        domElement.ibexaInstance = instance;
    };
    const getInstance = (domElement) => {
        return domElement.ibexaInstance;
    };
    const clearInstance = (domElement) => {
        delete domElement.ibexaInstance;
    };

    ibexa.addConfig('helpers.objectInstances', {
        setInstance,
        getInstance,
        clearInstance,
    });
})(window, window.document, window.ibexa);
