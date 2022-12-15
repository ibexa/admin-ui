(function (global, doc, ibexa) {
    class EditTranslation {
        constructor(config) {
            this.container = config.container;
            this.toggler = config.container.querySelector('.ibexa-btn--translations-list-toggler');
            this.translationsList = config.container.querySelector('.ibexa-translation-selector__list-wrapper');

            this.hideTranslationsList = this.hideTranslationsList.bind(this);
            this.showTranslationsList = this.showTranslationsList.bind(this);
            this.setPosition = this.setPosition.bind(this);
        }

        setPosition() {
            const topOffset = parseInt(this.translationsList.dataset.topOffset, 10);
            const topPosition = window.scrollY > topOffset ? 0 : topOffset - window.scrollY;
            const height = window.scrollY > topOffset ? window.innerHeight : window.innerHeight + window.scrollY - topOffset;

            this.translationsList.style.top = `${topPosition}px`;
            this.translationsList.style.height = `${height}px`;
        }

        hideTranslationsList(event) {
            const closestTranslationSelector = event.target.closest('.ibexa-translation-selector');
            const clickedOnTranslationsList = closestTranslationSelector && closestTranslationSelector.isSameNode(this.container);
            const clickedOnDraftConflictModal = event.target.closest('.ibexa-modal--version-draft-conflict');

            if (clickedOnTranslationsList || clickedOnDraftConflictModal) {
                return;
            }

            this.translationsList.classList.add('ibexa-translation-selector__list-wrapper--hidden');
            doc.removeEventListener('click', this.hideTranslationsList, false);
        }

        showTranslationsList() {
            this.translationsList.classList.remove('ibexa-translation-selector__list-wrapper--hidden');

            this.setPosition();

            doc.addEventListener('click', this.hideTranslationsList, false);
            ibexa.helpers.tooltips.hideAll();
        }

        init() {
            this.toggler.addEventListener('click', this.showTranslationsList, false);
        }
    }

    const translationSelectors = doc.querySelectorAll('.ibexa-translation-selector');

    translationSelectors.forEach((translationSelector) => {
        const editTranslation = new EditTranslation({ container: translationSelector });

        editTranslation.init();
    });
})(window, document, window.ibexa);
