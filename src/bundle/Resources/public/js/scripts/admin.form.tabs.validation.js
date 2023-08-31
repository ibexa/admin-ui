(function (global, doc) {
    const tabsHeaders = doc.querySelectorAll('.ibexa-tabs[data-form-selector]');
    const getHrefFromGroup = (group) => {
        if (group.dataset.id) {
            return group.dataset.id;
        }

        return `#${group.id}`;
    }

    tabsHeaders.forEach((tabsHeader) => {
        const popupMenu = tabsHeader.querySelector('.ibexa-tabs__popup-menu');
        const moreBtn = tabsHeader.querySelector('.ibexa-tabs__tab--more');
        const { formSelector, formTabGroupSelector } = tabsHeader.dataset;
        const formNode = doc.querySelector(formSelector);
        const classInvalidChangedCallback = (mutationList) => {
            mutationList.forEach((mutation) => {
                const { oldValue, target } = mutation;
                const hadIsInvalidClass = oldValue?.includes('is-invalid') ?? false;
                const hasIsInvalidClass = target.classList.contains('is-invalid');

                if (hadIsInvalidClass !== hasIsInvalidClass) {
                    const sectionGroup = target.closest(formTabGroupSelector);

                    if (!sectionGroup) {
                        return;
                    }

                    const href = getHrefFromGroup(sectionGroup);
                    const hasGroupError = !!sectionGroup.querySelector('.is-invalid');
                    const correspondingMenuItemLink = doc.querySelector(`.ibexa-tabs__tab [href="${href}"]`);
                    const correspondingMenuItem = correspondingMenuItemLink.parentNode;

                    correspondingMenuItem?.classList.toggle('ibexa-tabs__tab--error', hasGroupError);

                    if (correspondingMenuItemLink) {
                        const tabLinkId = correspondingMenuItemLink.id;
                        const popupMenuItem = popupMenu.querySelector(`[data-tab-link-id="${tabLinkId}"]`);

                        popupMenuItem?.classList.toggle('ibexa-popup-menu__item--error', hasGroupError);
                    }

                }
            });
        };
        const invalidObserver = new MutationObserver(classInvalidChangedCallback);

        invalidObserver.observe(formNode, {
            subtree: true,
            attributes: true,
            attributeFilter: ['class'],
            attributeOldValue: true,
        });

        if (popupMenu) {
            const classInvalidHiddenChangedCallback = (mutationList) => {
                mutationList.forEach(() => {
                    const popupMenuItems = popupMenu.querySelectorAll('.ibexa-popup-menu__item--error:not(.ibexa-popup-menu__item--hidden)');

                    moreBtn.classList.toggle('ibexa-tabs__tab--error', popupMenuItems.length);
                });
            };
            const invalidHiddenObserver = new MutationObserver(classInvalidHiddenChangedCallback);

            invalidHiddenObserver.observe(popupMenu, {
                subtree: true,
                attributes: true,
                attributeFilter: ['class'],
                attributeOldValue: true,
            });
        }
    });
})(window, window.document);
