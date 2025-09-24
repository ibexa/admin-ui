(function (global, doc, ibexa) {
    const SCROLL_POSITION_TO_FIT = 50;
    const MIN_HEIGHT_DIFF_FOR_FITTING_HEADER = 150;
    const headerNode = doc.querySelector('.ibexa-edit-header');
    const contentNode = doc.querySelector('.ibexa-edit-content');

    if (!headerNode || !contentNode) {
        return;
    }

    const detailsContainer = headerNode.querySelector('.ibexa-edit-header__container--details');
    const { height: expandedHeaderHeight } = headerNode.getBoundingClientRect();
    const scrolledContent = doc.querySelector('.ibexa-edit-content > :first-child');
    const { controlManyZIndexes } = ibexa.helpers.modal;
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
        doc.body.dispatchEvent(
            new CustomEvent('ibexa:edit-content-change-header-size', {
                detail: { isHeaderSlim: shouldHeaderBeSlim },
            }),
        );
    };
    const items = [{ container: headerNode }];

    if (detailsContainer) {
        items.push({ container: detailsContainer });
    }

    contentNode.addEventListener('scroll', fitHeader, false);
    controlManyZIndexes(items, headerNode);
})(window, window.document, window.ibexa);
