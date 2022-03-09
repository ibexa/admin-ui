(function (global, doc) {
    const backToTopBtn = doc.querySelector('.ibexa-back-to-top__button');
    const backToTopAnchor = doc.querySelector('.ibexa-back-to-top-anchor');

    if (!backToTopBtn || !backToTopAnchor) {
        return;
    }

    backToTopBtn.addEventListener('click', () => {
        backToTopAnchor.scrollIntoView({
            behavior: 'smooth',
        });
    });
})(window, window.document);
