(function (global, doc) {
    const backToTopBtn = doc.querySelector('.ibexa-back-to-top__btn');
    const backToTopAnchor = doc.querySelector('.ibexa-back-to-top-anchor');
    const backToTopScrollContainer = doc.querySelector('.ibexa-back-to-top-scroll-container');

    if (!backToTopBtn || !backToTopAnchor || !backToTopScrollContainer) {
        return;
    }

    const backToTopBtnTitle = backToTopBtn.querySelector('.ibexa-back-to-top__title');

    backToTopScrollContainer.addEventListener('scroll', (event) => {
        const container = event.target;
        const isTitleVisible = Math.abs(container.scrollHeight - container.scrollTop - container.clientHeight) <= 2;

        backToTopBtn.classList.toggle('ibexa-back-to-top__btn--visible', container.scrollTop !== 0);
        backToTopBtn.classList.toggle('ibexa-btn--no-text', !isTitleVisible);
        backToTopBtnTitle.classList.toggle('ibexa-back-to-top__title--visible', isTitleVisible);
    });
    backToTopBtn.addEventListener('click', () => {
        backToTopAnchor.scrollIntoView({
            behavior: 'smooth',
        });
    });
})(window, window.document);
