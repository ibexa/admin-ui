(function (global, doc) {
    class BaseSidebarFilter {
        constructor(config) {
            this.wrapper = config.wrapper;
            this.removeBtn = this.wrapper.querySelector('.ibexa-sidebar-filter__remove-btn');

            this.removeFilter = this.removeFilter.bind(this);
        }

        removeFilter() {
            this.removeBtn.removeEventListener('click', this.removeFilter);
            this.wrapper.remove();
        }

        init() {
            if (this.removeBtn) {
                this.removeBtn.addEventListener('click', this.removeFilter, false);
            }
        }
    }

    ibexa.addConfig('BaseSidebarFilter', BaseSidebarFilter);
})(window, window.document);
