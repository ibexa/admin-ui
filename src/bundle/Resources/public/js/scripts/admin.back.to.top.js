(function (global, doc) {
    const backToTopBtn = doc.querySelector('.ibexa-back-to-top__button');
    const backToTopAnchor = doc.querySelector('.ibexa-back-to-top-anchor');
    const backToTopScrollContainer = doc.querySelector('.ibexa-back-to-top-scroll-container');

    if (!backToTopBtn || !backToTopAnchor || !backToTopScrollContainer) {
        return;
    }

    backToTopScrollContainer.addEventListener('scroll', (event) => {
        backToTopBtn.classList.toggle('ibexa-back-to-top__button--visible', event.target.scrollTop !== 0);
    });
    backToTopBtn.addEventListener('click', () => {
        backToTopAnchor.scrollIntoView({
            behavior: 'smooth',
        });
    });
})(window, window.document);
