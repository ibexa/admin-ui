(function (global, doc, ibexa) {
    const SCROLL_POSITION_TO_FIT = 50;
    const HEADER_RIGHT_MARGIN = 50;
    const MIN_HEIGHT_DIFF_FOR_FITTING_HEADER = 150;
    const headerNode = doc.querySelector('.ibexa-edit-header');
    const contentNode = doc.querySelector('.ibexa-edit-content');

    if (!headerNode || !contentNode) {
        return;
    }

    const { height: expandedHeaderHeight } = headerNode.getBoundingClientRect();
    const scrolledContent = doc.querySelector('.ibexa-edit-content > :first-child');
    const { controlZIndex } = ibexa.helpers.modal;
    const fitEllipsizedTitle = () => {
        const headerBottomRowNode = headerNode.querySelector('.ibexa-edit-header__row--bottom');
        const titleNode = headerBottomRowNode.querySelector('.ibexa-edit-header__name--ellipsized');
        const firstMenuEntryNode = headerNode.querySelector('.ibexa-context-menu .ibexa-context-menu__item');
        const { left: titleNodeLeft, width: titleNodeWidth } = titleNode.getBoundingClientRect();
        const { left: firstMenuEntryNodeLeft } = firstMenuEntryNode.getBoundingClientRect();
        const bottomRowNodeWidthNew = firstMenuEntryNodeLeft - titleNodeLeft;
        const titleNodeWidthNew = bottomRowNodeWidthNew - HEADER_RIGHT_MARGIN;

        headerBottomRowNode.style.width = `${bottomRowNodeWidthNew}px`;

        if (titleNodeWidth > titleNodeWidthNew) {
            titleNode.style.width = `${titleNodeWidthNew}px`;
        }
    };
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

        if (shouldHeaderBeSlim) {
            fitEllipsizedTitle();
        }
    };

    contentNode.addEventListener('scroll', fitHeader, false);
    controlZIndex(headerNode);
})(window, window.document, window.ibexa);
