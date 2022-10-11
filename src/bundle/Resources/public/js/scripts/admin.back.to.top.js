(function (global, doc) {
    const backToTopBtn = doc.querySelector('.ibexa-back-to-top__btn');
    const backToTopAnchor = doc.querySelector('.ibexa-back-to-top-anchor');
    const backToTopScrollContainer = doc.querySelector('.ibexa-back-to-top-scroll-container');

    if (!backToTopBtn || !backToTopAnchor || !backToTopScrollContainer) {
        return;
    }

    const backToTopBtnTitle = backToTopBtn.querySelector('.ibexa-back-to-top__title');
    let currentBackToTopAnchorHeight = backToTopAnchor.offsetHeight;
    const toggleBackToTopBtnText = (container) => {
        const isTitleVisible = Math.abs(container.scrollHeight - container.scrollTop - container.clientHeight) <= 2;

        backToTopBtn.classList.toggle('ibexa-back-to-top__btn--visible', container.scrollTop !== 0);
        backToTopBtn.classList.toggle('ibexa-btn--no-text', !isTitleVisible);
        backToTopBtnTitle.classList.toggle('ibexa-back-to-top__title--visible', isTitleVisible);
    };

    backToTopScrollContainer.addEventListener('scroll', (event) => {
        const container = event.target;

        toggleBackToTopBtnText(container);
    });
    backToTopBtn.addEventListener('click', () => {
        backToTopAnchor.scrollIntoView({
            behavior: 'smooth',
        });
    });

    const resizeObserver = new ResizeObserver((entries) => {
        if (currentBackToTopAnchorHeight === entries[0].target.clientHeight) {
            return;
        }

        currentBackToTopAnchorHeight = entries[0].target.clientHeight;

        toggleBackToTopBtnText(backToTopScrollContainer);
    });

    resizeObserver.observe(backToTopAnchor);
})(window, window.document);
