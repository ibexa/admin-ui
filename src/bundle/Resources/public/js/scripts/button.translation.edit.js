(function (global, doc, ibexa) {
    class EditTranslation {
        constructor(config) {
            this.container = config.container;
            this.toggler = config.container.querySelector('.ids-button--translations-list-toggler');
            this.extraActionsContainer = config.container.querySelector('.ibexa-extra-actions');
            this.closeBtn = config.container.querySelector('.ibexa-extra-actions__close-btn');
            this.confirmBtn = config.container.querySelector('.ibexa-extra-actions__confirm-btn');
            this.languagesBtns = config.container.querySelectorAll('.ids-button--select-language');
            this.backdrop = config.backdrop;

            this.tableNode = null;

            this.hideExtraActionPanel = this.hideExtraActionPanel.bind(this);
            this.showExtraActionPanel = this.showExtraActionPanel.bind(this);
            this.setActiveLanguage = this.setActiveLanguage.bind(this);
            this.resetLanguageSelector = this.resetLanguageSelector.bind(this);

            this.setPosition = this.setPosition.bind(this);
        }

        setPosition() {
            const topOffset = parseInt(this.extraActionsContainer.dataset.topOffset, 10);
            const topPosition = window.scrollY > topOffset ? 0 : topOffset - window.scrollY;
            const height = window.scrollY > topOffset ? window.innerHeight : window.innerHeight + window.scrollY - topOffset;

            this.extraActionsContainer.style.top = `${topPosition}px`;
            this.extraActionsContainer.style.height = `${height}px`;
        }

        hideExtraActionPanel() {
            if (this.tableNode) {
                this.tableNode.classList.add('ibexa-table--last-column-sticky');

                this.tableNode = null;
            }

            this.backdrop.hide();
            this.extraActionsContainer.classList.add('ibexa-extra-actions--hidden');

            this.closeBtn.removeEventListener('click', this.hideExtraActionPanel, false);
        }

        showExtraActionPanel({ currentTarget }) {
            this.extraActionsContainer.classList.remove('ibexa-extra-actions--hidden');

            this.tableNode = currentTarget.closest('.ibexa-table--last-column-sticky');

            if (this.tableNode) {
                this.tableNode.classList.remove('ibexa-table--last-column-sticky');
            }

            this.setPosition();
            this.backdrop.show();
            this.closeBtn.addEventListener('click', this.hideExtraActionPanel, false);

            ibexa.helpers.tooltips.hideAll();
        }

        setActiveLanguage(event) {
            const { contentId, languageCode } = event.currentTarget.dataset;

            this.confirmBtn.dataset.contentId = contentId;
            this.confirmBtn.dataset.languageCode = languageCode;
            this.confirmBtn.disabled = false;

            this.languagesBtns.forEach((btn) => btn.classList.remove('ids-button--active'));
            event.currentTarget.classList.add('ids-button--active');
        }

        resetLanguageSelector() {
            this.confirmBtn.dataset.contentId = null;
            this.confirmBtn.dataset.languageCode = null;
            this.confirmBtn.disabled = true;

            this.languagesBtns.forEach((btn) => btn.classList.remove('ids-button--active'));
        }

        init() {
            this.toggler.addEventListener('click', this.showExtraActionPanel, false);
            this.languagesBtns.forEach((btn) => {
                btn.addEventListener('click', this.setActiveLanguage, false);
            });

            document.body.addEventListener('ibexa:edit-content-reset-language-selector', this.resetLanguageSelector, false);
        }
    }

    const translationSelectors = doc.querySelectorAll('.ibexa-translation-selector');

    translationSelectors.forEach((translationSelector) => {
        const backdrop = new ibexa.core.Backdrop();
        const editTranslation = new EditTranslation({ container: translationSelector, backdrop });

        editTranslation.init();
    });
})(window, document, window.ibexa);
