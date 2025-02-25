(function (global, doc) {
    const showMoreBtns = doc.querySelectorAll('.ibexa-details__show-more-btn');
    const toggleShowMore = (ellipsizedContent, showMoreBtn) => {
        const showMoreIcon = showMoreBtn.querySelector('.ibexa-details__show-more-btn-icon');
        const showMoreLabel = showMoreBtn.querySelector('.ibexa-details__show-more-label');
        const showLessLabel = showMoreBtn.querySelector('.ibexa-details__show-less-label');

        ellipsizedContent.classList.toggle('ibexa-details__item-content--ellipsized');

        showMoreLabel.classList.toggle('ibexa-details__show-more-label--hidden');
        showLessLabel.classList.toggle('ibexa-details__show-less-label--hidden');
        showMoreIcon.classList.toggle('ibexa-details__show-more-btn-icon--opened');
        showMoreBtn.classList.toggle('ibexa-details__show-more-btn--opened');
    };

    showMoreBtns.forEach((showMoreBtn) => {
        const contentWrapper = showMoreBtn.closest('.ibexa-details__item-content-wrapper');
        const ellipsizedContent = contentWrapper.querySelector('.ibexa-details__item-content');
        const isEllipsized = ellipsizedContent.offsetWidth < ellipsizedContent.scrollWidth;

        showMoreBtn.classList.toggle('ibexa-details__show-more-btn--hidden', !isEllipsized);

        showMoreBtn.addEventListener('click', () => toggleShowMore(ellipsizedContent, showMoreBtn), false);
    });
})(window, document);
