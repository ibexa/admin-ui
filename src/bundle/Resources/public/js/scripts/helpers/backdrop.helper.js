(function (global, doc, ibexa) {
    class Backdrop {
        constructor(config = {}) {
            this.isTransparent = config.isTransparent ?? false;
            this.extraClasses = config.extraClasses ?? [];
            this.backdrop = null;

            this.remove = this.remove.bind(this);
            this.init = this.init.bind(this);
        }

        remove() {
            if (this.backdrop) {
                this.backdrop.remove();
            }
        }

        init() {
            const classes = {
                'ibexa-backdrop--transparent': this.isTransparent,
            };
            const backdropClasses = Object.keys(classes).filter((property) => classes[property]);
            const backdropExtraClasses = Array.isArray(this.extraClasses) ? this.extraClasses : [this.extraClasses];
            const bodyFirstNode = document.body.firstChild;

            this.backdrop = doc.createElement('div');
            this.backdrop.classList.add('ibexa-backdrop', ...backdropClasses, ...backdropExtraClasses);
            doc.body.insertBefore(this.backdrop, bodyFirstNode);
            doc.dispatchEvent(new CustomEvent('ibexa-backdrop:after-show'));
        }
    }

    ibexa.addConfig('core.Backdrop', Backdrop);
})(window, window.document, window.ibexa);
