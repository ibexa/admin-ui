(function (global, doc) {
    if (!doc.querySelector('.ibexa-navigation-menu')) {
        return;
    }

    const SECTION_ADJUST_MARGIN_TOP = 20;
    const formContainerNode = doc.querySelector('.ibexa-edit-content');
    const getSecondarySectionActiveItems = () => {
        const secondarySectionItems = formContainerNode.querySelectorAll(
            '.ibexa-edit-content__primary-section--active .ibexa-edit-content__secondary-section',
        );

        return [...secondarySectionItems];
    };
    let currentlyVisibleSections = getSecondarySectionActiveItems();
    const fitSecondarySections = () => {
        const primarySection = doc.querySelector('.ibexa-edit-content__primary-section--active');
        const contentColumn = doc.querySelector('.ibexa-main-container__content-column');
        const firstSection = primarySection.querySelector('.ibexa-edit-content__secondary-section:first-child');
        const lastSection = primarySection.querySelector('.ibexa-edit-content__secondary-section:last-child');
        const contentContainer = contentColumn.querySelector('.ibexa-edit-content__container');

        contentContainer.style.paddingBottom = '0px';

        if (!firstSection.isSameNode(lastSection) && lastSection && lastSection.offsetHeight) {
            const headerContainer = doc.querySelector('.ibexa-edit-header__container');
            const heightFromLastSection = contentContainer.offsetHeight - lastSection.offsetTop;
            const contentColumnBodyHeight = contentColumn.offsetHeight - headerContainer.offsetHeight;
            const heightDiff = contentColumnBodyHeight - heightFromLastSection;

            if (heightDiff > 0) {
                contentContainer.style.paddingBottom = `${heightDiff}px`;
            }
        }
    };
    const navigateTo = (targetId) => {
        const secondarySectionNode = formContainerNode.querySelector(`.ibexa-edit-content__secondary-section[data-id="${targetId}"]`);

        formContainerNode.scrollTo({
            top: secondarySectionNode.offsetTop,
            behavior: 'smooth',
        });
    };
    const setActiveSecondaryMenu = (node) => {
        const secondaryMenuItems = doc.querySelectorAll(
            '.ibexa-navigation-menu__secondary--active .ibexa-navigation-menu__secondary-item-btn',
        );

        secondaryMenuItems.forEach((item) => {
            item.classList.toggle('ibexa-navigation-menu__secondary-item-btn--active', item.isSameNode(node));
        });
    };
    const showPrimarySection = (id) => {
        const primarySectionItems = formContainerNode.querySelectorAll('.ibexa-edit-content__primary-section');

        primarySectionItems.forEach((item) => {
            item.classList.toggle('ibexa-edit-content__primary-section--active', item.dataset.id === id);
        });

        currentlyVisibleSections = getSecondarySectionActiveItems();

        fitSecondarySections();
    };
    const showSecondaryMenu = (node) => {
        const items = doc.querySelectorAll('.ibexa-navigation-menu__secondary');

        items.forEach((item) => item.classList.toggle('ibexa-navigation-menu__secondary--active', item.isSameNode(node)));
    };
    const onSelectPrimaryMenuList = (event) => {
        const { targetId } = event.currentTarget.dataset;
        const secondaryMenuNode = doc.querySelector(`.ibexa-navigation-menu__secondary[data-id="${targetId}"]`);
        const primaryMenuItems = doc.querySelectorAll('.ibexa-navigation-menu__primary--list .ibexa-navigation-menu__primary-item');

        primaryMenuItems.forEach((item) => {
            item.classList.toggle('ibexa-navigation-menu__primary-item--active', item.isSameNode(event.target));
        });
        showPrimarySection(targetId);

        if (secondaryMenuNode) {
            showSecondaryMenu(secondaryMenuNode);
        }
    };
    const onSelectPrimaryMenuDropdown = (event) => {
        const targetId = event.currentTarget.value;
        const secondaryMenuNode = doc.querySelector(`.ibexa-navigation-menu__secondary[data-id="${targetId}"]`);

        showPrimarySection(targetId);

        if (secondaryMenuNode) {
            showSecondaryMenu(secondaryMenuNode);
        }
    };
    const onSelectSecondaryMenu = (event) => {
        const { targetId } = event.currentTarget.dataset;

        navigateTo(targetId);
    };
    const bindPrimaryMenuListEvents = () => {
        const items = doc.querySelectorAll('.ibexa-navigation-menu__primary--list .ibexa-navigation-menu__primary-item');

        items.forEach((item) => item.addEventListener('click', onSelectPrimaryMenuList, false));
    };
    const bindPrimaryMenuDropdownEvents = () => {
        const sourceSelect = doc.querySelector('.ibexa-navigation-menu__primary--dropdown .ibexa-dropdown__source .ibexa-input');

        if (!sourceSelect) {
            return;
        }

        sourceSelect.addEventListener('change', onSelectPrimaryMenuDropdown, false);
    };
    const bindSecondaryMenuEvents = () => {
        const items = doc.querySelectorAll('.ibexa-navigation-menu .ibexa-navigation-menu__secondary-item-btn');

        items.forEach((item) => item.addEventListener('click', onSelectSecondaryMenu, false));
    };
    const bindScrollContainerEvents = () => {
        const allSections = [...doc.querySelectorAll('.ibexa-edit-content__secondary-section')];
        const headerContainer = doc.querySelector('.ibexa-edit-header__container');
        let previousFirstVisibleSection = null;

        if (formContainerNode && allSections.length) {
            formContainerNode.addEventListener('scroll', () => {
                let firstVisibleSection = currentlyVisibleSections.find((section) => {
                    const { top, height } = section.getBoundingClientRect();

                    return top + height >= headerContainer.offsetHeight + SECTION_ADJUST_MARGIN_TOP;
                });

                if (!firstVisibleSection) {
                    firstVisibleSection = currentlyVisibleSections.at(-1);
                }

                if (previousFirstVisibleSection === firstVisibleSection) {
                    return;
                }

                previousFirstVisibleSection = firstVisibleSection;

                const targetId = firstVisibleSection.dataset.id;

                const secondaryMenuNode = doc.querySelector(
                    `.ibexa-navigation-menu__secondary--active .ibexa-navigation-menu__secondary-item-btn[data-target-id="${targetId}"]`,
                );

                setActiveSecondaryMenu(secondaryMenuNode);
            });
        }
    };

    bindPrimaryMenuListEvents();
    bindPrimaryMenuDropdownEvents();
    bindSecondaryMenuEvents();
    bindScrollContainerEvents();
    fitSecondarySections();
})(window, window.document);
