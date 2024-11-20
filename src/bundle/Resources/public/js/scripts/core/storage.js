(function (global, doc, ibexa) {
    class IbexaStorage {
        constructor(config) {
            const keySuffix = config.isUserAware ? `:${ibexa.helpers.user.getId()}` : '';

            this.key = `${config.key}${keySuffix}`;
            this.eventName = config.eventName;
        }

        stringifyData(data) {
            try {
                return JSON.stringify(data);
            } catch (error) {
                console.warn('Error stringifying data', error);
            }
        }

        parseData(data) {
            try {
                return JSON.parse(data);
            } catch (error) {
                console.warn('Error parsing data', error);

                return null;
            }
        }

        setItem(data) {
            const stringifiedData = this.stringifyData(data);

            global.localStorage.setItem(this.key, stringifiedData);

            this.fireStorageChangeEvent(stringifiedData);
        }

        getItem() {
            return this.parseData(global.localStorage.getItem(this.key));
        }

        fireStorageChangeEvent(data) {
            if (this.eventName) {
                const storageChangeEvent = new CustomEvent(this.eventName, {
                    cancelable: true,
                    detail: { content: this.parseData(data) },
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
