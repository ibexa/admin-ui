(function (global, doc) {
    const SCROLL_POSITION_TO_FIT = 50;
    const MIN_HEIGHT_DIFF_FOR_FITTING_HEADER = 150;
    const headerNode = doc.querySelector('.ibexa-edit-header');
    const contentNode = doc.querySelector('.ibexa-edit-content');

    if (!headerNode || !contentNode) {
        return;
    }

    const { height: expandedHeaderHeight } = headerNode.getBoundingClientRect();
    const scrolledContent = doc.querySelector('.ibexa-edit-content > :first-child');
    const fitHeader = (event) => {
        const { height: formHeight } = scrolledContent.getBoundingClientRect();
        const contentHeightWithExpandedHeader = formHeight + expandedHeaderHeight;
        const heightDiffBetweenWindowAndContent = contentHeightWithExpandedHeader - global.innerHeight;

        if (heightDiffBetweenWindowAndContent < MIN_HEIGHT_DIFF_FOR_FITTING_HEADER) {
            return;
        }

        const { scrollTop } = event.currentTarget;
        const shouldHeaderBeSlim = scrollTop > SCROLL_POSITION_TO_FIT;

        headerNode.classList.toggle('ibexa-edit-header--slim', shouldHeaderBeSlim);
    };

    contentNode.addEventListener('scroll', fitHeader, false);
})(window, window.document);
