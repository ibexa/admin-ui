(function (global, doc, ibexa) {
    const navigationMenu = doc.querySelector('.ibexa-anchor-navigation-menu');

    if (!navigationMenu) {
        return;
    }

    const header = doc.querySelector('.ibexa-edit-header');
    const headerContainer = header.querySelector('.ibexa-edit-header__container');
    const SECTION_ADJUST_MARGIN_TOP = 20;
    const formContainerNode = doc.querySelector('.ibexa-edit-content');
    const getSectionGroupActiveItems = () => {
        const sectionGroupNode = formContainerNode.querySelector('.ibexa-anchor-navigation__section-group') ?? formContainerNode;
        const sections = sectionGroupNode.querySelectorAll('.ibexa-anchor-navigation__section');

        return [...sections];
    };
    let currentlyVisibleSections = getSectionGroupActiveItems();
    const attachSectionGroupsMenuListEvents = () => {
        const items = doc.querySelectorAll(
            '.ibexa-anchor-navigation-menu__section-groups--list .ibexa-anchor-navigation-menu__section-groups-item',
        );

        items.forEach((item) => item.addEventListener('click', onSelectSectionGroupsMenuList, false));
    };
    const attachSectionGroupsMenuDropdownEvents = () => {
        const sourceSelect = doc.querySelector(
            '.ibexa-anchor-navigation-menu__section-groups--dropdown .ibexa-dropdown__source .ibexa-input',
        );

        if (!sourceSelect) {
            return;
        }

        sourceSelect.addEventListener('change', onSelectSectionGroupsMenuDropdown, false);
    };
    const onSelectSectionGroupsMenuList = (event) => {
        const { targetId } = event.currentTarget.dataset;
        const sectionsMenuNode = doc.querySelector(`.ibexa-anchor-navigation-menu__sections[data-id="${targetId}"]`);
        const sectionGroupsMenuItems = doc.querySelectorAll(
            '.ibexa-anchor-navigation-menu__section-groups--list .ibexa-anchor-navigation-menu__section-groups-item',
        );

        sectionGroupsMenuItems.forEach((item) => {
            item.classList.toggle('ibexa-anchor-navigation-menu__section-groups-item--active', item.isSameNode(event.currentTarget));
        });
        showSectionGroup(targetId);
        showSectionsMenu(sectionsMenuNode);
    };
    const onSelectSectionGroupsMenuDropdown = (event) => {
        const targetId = event.currentTarget.value;
        const sectionsMenuNode = doc.querySelector(`.ibexa-anchor-navigation-menu__sections[data-id="${targetId}"]`);

        showSectionGroup(targetId);
        showSectionsMenu(sectionsMenuNode);
    };
    const showSectionsMenu = (node) => {
        const items = doc.querySelectorAll('.ibexa-anchor-navigation-menu__sections');

        items.forEach((item) => item.classList.toggle('ibexa-anchor-navigation-menu__sections--active', item.isSameNode(node)));
    };
    const showSectionGroup = (id) => {
        const sectionGroupItems = formContainerNode.querySelectorAll('.ibexa-anchor-navigation__section-group');

        sectionGroupItems.forEach((item) => {
            item.classList.toggle('ibexa-anchor-navigation__section-group--active', item.dataset.id === id);
        });

        currentlyVisibleSections = getSectionGroupActiveItems();

        fitSections();
    };
    const attachSectionsMenuEvents = () => {
        const items = doc.querySelectorAll('.ibexa-anchor-navigation-menu .ibexa-anchor-navigation-menu__sections-item-btn');

        items.forEach((item) => item.addEventListener('click', onSelectSectionsMenu, false));
    };
    const onSelectSectionsMenu = (event) => {
        const { targetId } = event.currentTarget.dataset;

        navigateTo(targetId);
    };
    const navigateTo = (targetId) => {
        const sectionNode = formContainerNode.querySelector(`.ibexa-anchor-navigation__section[data-id="${targetId}"]`);
        const headerBottomContainerHeight = header.offsetHeight - headerContainer.offsetHeight;
        console.log(sectionNode);
        formContainerNode.scrollTo({
            top: sectionNode.offsetTop,
            behavior: 'smooth',
        });
    };
    const getFirstSection = (sectionGroup) => {
        return sectionGroup.querySelector('.ibexa-anchor-navigation__section');
    };
    const getLastSection = (sectionGroup) => {
        const sections = sectionGroup.querySelectorAll('.ibexa-anchor-navigation__section');
        return sections ? [...sections].at(-1) : null;
    };
    const fitSections = () => {
        const sectionGroup =
            formContainerNode.querySelector('.ibexa-anchor-navigation__section-group--active') ??
            formContainerNode.querySelector('.ibexa-anchor-navigation-sections');

        if (!sectionGroup) {
            return;
        }

        const contentColumn = doc.querySelector('.ibexa-main-container__content-column');
        const contentContainer = contentColumn.querySelector('.ibexa-edit-content__container');
        const firstSection = getFirstSection(sectionGroup);
        const lastSection = getLastSection(sectionGroup);

        contentContainer.style.paddingBottom = '0px';

        if (!firstSection.isSameNode(lastSection) && lastSection.offsetHeight) {
            const heightFromLastSection = contentContainer.offsetHeight - lastSection.offsetTop;
            const contentColumnBodyHeight = contentColumn.offsetHeight - headerContainer.offsetHeight;
            const heightDiff = contentColumnBodyHeight - heightFromLastSection;

            if (heightDiff > 0) {
                contentContainer.style.paddingBottom = `${heightDiff}px`;
            }
        }
    };
    const attachScrollContainerEvents = () => {
        const allSections = [...formContainerNode.querySelectorAll('.ibexa-anchor-navigation__section')];
        let previousFirstVisibleSection = null;

        if (formContainerNode && allSections.length) {
            formContainerNode.addEventListener('scroll', () => {
                let firstVisibleSection = currentlyVisibleSections.find((section) => {
                    const { top, height } = section.getBoundingClientRect();
                    const headerBottomContainerHeight = header.offsetHeight - headerContainer.offsetHeight;

                    return top + height >= headerContainer.offsetHeight + headerBottomContainerHeight + SECTION_ADJUST_MARGIN_TOP;
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
                    `.ibexa-anchor-navigation-menu__sections--active .ibexa-anchor-navigation-menu__sections-item-btn[data-target-id="${targetId}"]`,
                );

                setActiveSecondaryMenu(secondaryMenuNode);
            });
        }
    };
    const setActiveSecondaryMenu = (node) => {
        const secondaryMenuItems = doc.querySelectorAll(
            '.ibexa-anchor-navigation-menu__sections--active .ibexa-anchor-navigation-menu__sections-item-btn',
        );

        secondaryMenuItems.forEach((item) => {
            item.classList.toggle('ibexa-anchor-navigation-menu__sections-item-btn--active', item.isSameNode(node));
        });
    };

    attachSectionGroupsMenuListEvents();
    attachSectionGroupsMenuDropdownEvents();
    attachSectionsMenuEvents();
    attachScrollContainerEvents();
    fitSections();
    ibexa.helpers.tooltips.parse(navigationMenu);
})(window, window.document, window.ibexa);
