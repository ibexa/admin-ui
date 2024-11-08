(function (global, doc, ibexa) {
    class IbexaStorage {
        constructor(config) {
            const keySuffix = config.isUserAware ? `:${ibexa.helpers.user.getId()}` : '';

            this.key = `${config.key}${keySuffix}`;
            this.eventName = config.eventName;
        }

        stringfyData(data) {
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
                return null;
            }
        }

        setItem(data) {
            const stringifiedData = this.stringfyData(data);

            window.localStorage.setItem(this.key, stringifiedData);

            this.fireStorageChangeEvent(stringifiedData);
        }

        getItem() {
            return this.parseData(window.localStorage.getItem(this.key));
        }

        fireStorageChangeEvent(data) {
            if (this.eventName) {
                const storageChangeEvent = new CustomEvent(this.eventName, {
                    cancelable: true,
                    detail: { content: this.parseData(data) },
                });

                document.body.dispatchEvent(storageChangeEvent);
            }
        }

        init() {
            if (this.eventName) {
                window.addEventListener('storage', (event) => this.fireStorageChangeEvent(event.newValue));
            }
        }
    }

    ibexa.addConfig('core.Storage', IbexaStorage);
})(window, window.document, window.ibexa);
