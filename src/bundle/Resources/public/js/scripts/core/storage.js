(function (global, doc, ibexa) {
    class IbexaStorage {
        constructor(config) {
            const keySuffix = config.isUserAware ? `:${ibexa.helpers.user.getId()}` : '';

            this.key = `${config.key}${keySuffix}`;
            this.eventName = config.eventName;
        }

        setItem(data) {
            const stringifiedData = JSON.stringify(data);

            global.localStorage.setItem(this.key, stringifiedData);

            this.fireStorageChangeEvent(stringifiedData);
        }

        getItem() {
            return JSON.parse(global.localStorage.getItem(this.key));
        }

        fireStorageChangeEvent(data) {
            if (this.eventName) {
                const storageChangeEvent = new CustomEvent(this.eventName, {
                    cancelable: true,
                    detail: { content: JSON.parse(data) },
                });

                doc.body.dispatchEvent(storageChangeEvent);
            }
        }

        init() {
            if (this.eventName) {
                global.addEventListener('storage', (event) => {
                    if (event.key === this.key) {
                        this.fireStorageChangeEvent(event.newValue);
                    }
                });
            }
        }
    }

    ibexa.addConfig('core.Storage', IbexaStorage);
})(window, window.document, window.ibexa);
