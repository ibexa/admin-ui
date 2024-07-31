(function (global, doc, ibexa) {
    class SplitBtn {
        constructor(config) {
            this.container = config.container;
            this.toggleBtn = this.container.querySelector('.ibexa-split-btn__toggle-btn');
            this.multilevelPopupMenuContainer = this.container.querySelector('.ibexa-multilevel-popup-menu');

            this.handlePopupOpened = this.handlePopupOpened.bind(this);
            this.handlePopupClosed = this.handlePopupClosed.bind(this);
        }

        init() {
            const multilevelPopupMenu = new ibexa.core.MultilevelPopupMenu({
                container: this.multilevelPopupMenuContainer,
                triggerElement: this.toggleBtn,
                referenceElement: this.container,
                initialBranchPlacement: 'bottom-start',
                initialBranchFallbackPlacements: ['bottom-end', 'top-end', 'top-start'],
                onTopBranchOpened: this.handlePopupOpened,
                onTopBranchClosed: this.handlePopupClosed,
            });

            multilevelPopupMenu.init();
        }

        handlePopupOpened() {
            this.toggleTogglerBtnState(true);
        }

        handlePopupClosed() {
            this.toggleTogglerBtnState(false);
        }

        toggleTogglerBtnState(areSubitemsOpened) {
            this.toggleBtn.classList.toggle('ibexa-split-btn__toggle-btn--subitems-opened', areSubitemsOpened);
        }
    }

    ibexa.addConfig('core.SplitBtn', SplitBtn);
})(window, window.document, window.ibexa);
