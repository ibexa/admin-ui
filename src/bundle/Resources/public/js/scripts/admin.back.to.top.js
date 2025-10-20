(function (global, doc, ibexa) {
    const backToTopBtn = doc.querySelector('.ibexa-back-to-top__btn');
    const backToTop = doc.querySelector('.ibexa-back-to-top');
    const backToTopAnchor = doc.querySelector('.ibexa-back-to-top-anchor');
    const backToTopScrollContainer = doc.querySelector('.ibexa-back-to-top-scroll-container');

    if (!backToTopBtn || !backToTopAnchor || !backToTopScrollContainer) {
        return;
    }

    const checkIsVisible = () => {
        if (!backToTop) {
            return false;
        }

        return backToTopBtn.classList.contains('ibexa-back-to-top__btn--visible');
    };
    const backToTopBtnTitle = backToTopBtn.querySelector('.ibexa-back-to-top__title');
    let currentBackToTopAnchorHeight = backToTopAnchor.offsetHeight;
    const setBackToTopBtnTextVisibility = (container) => {
        const isTitleVisible = Math.abs(container.scrollHeight - container.scrollTop - container.clientHeight) <= 2;
        const shouldBeVisible = container.scrollTop !== 0;

        if (backToTopBtn.classList.contains('ibexa-back-to-top__btn--visible') && !shouldBeVisible) {
            backToTopBtn.classList.remove('ibexa-back-to-top__btn--visible');
        }

        if (!backToTopBtn.classList.contains('ibexa-back-to-top__btn--visible') && shouldBeVisible) {
            backToTopBtn.classList.add('ibexa-back-to-top__btn--visible');
            ibexa.quickAction.recalculateButtonsLayout();
        }

        backToTopBtn.classList.toggle('ibexa-btn--no-text', !isTitleVisible);
        backToTopBtnTitle.classList.toggle('ibexa-back-to-top__title--visible', isTitleVisible);
    };

    backToTopScrollContainer.addEventListener('scroll', (event) => {
        const container = event.target;

        setBackToTopBtnTextVisibility(container);
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

        setBackToTopBtnTextVisibility(backToTopScrollContainer);
    });
    const config = {
        id: 'back-to-top',
        zIndex: 10,
        container: backToTop,
        priority: 100,
        checkVisibility: checkIsVisible,
    };

    ibexa.quickAction.registerButton(config);
    resizeObserver.observe(backToTopAnchor);
})(window, window.document, window.ibexa);
