(function (global, doc, ibexa) {
    let id = 0;

    class Backdrop {
        constructor(config = {}) {
            this.isTransparent = config.isTransparent ?? false;
            this.extraClasses = config.extraClasses ?? [];
            this.backdrop = null;
            this.id = id++;

            this.remove = this.remove.bind(this);
            this.init = this.init.bind(this);
            this.toggle = this.toggle.bind(this);
            this.hide = this.hide.bind(this);
            this.show = this.show.bind(this);
        }

        toggle(shouldBackdropDisplay) {
            this.backdrop.classList.toggle('ibexa-backdrop--active', shouldBackdropDisplay);
        }

        remove() {
            if (this.backdrop) {
                this.backdrop.remove();
                this.backdrop = null;
            }
        }

        hide() {
            this.toggle(false);
        }

        show() {
            if (this.backdrop === null) {
                this.init();
            }

            this.toggle(true);
            doc.dispatchEvent(new CustomEvent('ibexa-backdrop:after-show'));
        }

        init() {
            const classes = {
                'ibexa-backdrop--transparent': this.isTransparent,
            };
            const backdropClasses = Object.keys(classes).filter((property) => classes[property]);
            const bodyFirstNode = document.body.firstChild;

            this.backdrop = doc.createElement('div');
            this.backdrop.id = `ibexa-backdrop-no-${this.id}`;
            this.backdrop.classList.add('ibexa-backdrop', ...backdropClasses, ...this.extraClasses);
            doc.body.insertBefore(this.backdrop, bodyFirstNode);
        }
    }

    ibexa.addConfig('core.Backdrop', Backdrop);
})(window, window.document, window.ibexa);
