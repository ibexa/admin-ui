(function (global, doc, ibexa) {
    const navigationMenu = doc.querySelector('.ibexa-anchor-navigation-menu');

    if (!navigationMenu) {
        return;
    }

    const header = doc.querySelector('.ibexa-edit-header');
    const headerContainer = header?.querySelector('.ibexa-edit-header__container');
    const SECTION_ADJUST_MARGIN_TOP = 20;
    const formContainerNode = doc.querySelector('.ibexa-edit-content');
    const lastSectionObserver = new ResizeObserver(() => {
        fitSections();
    });
    const getSectionGroupActiveItems = () => {
        const sectionGroupNode = formContainerNode.querySelector('.ibexa-anchor-navigation__section-group') ?? formContainerNode;
        const sections = sectionGroupNode.querySelectorAll('.ibexa-anchor-navigation__section');

        return [...sections];
    };
    let currentlyVisibleSections = getSectionGroupActiveItems();
    const attachSectionGroupsMenuListEvents = () => {
        const items = doc.querySelectorAll('.ibexa-anchor-navigation-menu__section-groups--list .ibexa-switcher__item');

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
        const sectionGroupsMenuItems = doc.querySelectorAll('.ibexa-anchor-navigation-menu__section-groups--list .ibexa-switcher__item');

        sectionGroupsMenuItems.forEach((item) => {
            item.classList.toggle('ibexa-switcher__item--active', item.isSameNode(event.currentTarget));
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

        initFitSection();
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

        formContainerNode.scrollTo({
            top: sectionNode.offsetTop,
            behavior: 'smooth',
        });
    };
    const getFirstSection = (sectionGroup) => {
        return sectionGroup.querySelector('.ibexa-anchor-navigation__section');
    };
    const getLastSection = (sectionGroup) => {
        const sections = [...sectionGroup.querySelectorAll('.ibexa-anchor-navigation__section')];

        return sections[sections.length - 1];
    };
    const initFitSection = () => {
        const sectionGroup =
            formContainerNode.querySelector('.ibexa-anchor-navigation__section-group--active') ??
            formContainerNode.querySelector('.ibexa-anchor-navigation-sections');

        if (!sectionGroup) {
            return;
        }

        const lastSection = getLastSection(sectionGroup);

        if (!lastSection) {
            return;
        }

        const contentContainer = lastSection.closest('.ibexa-edit-content__container');

        if (!contentContainer) {
            return;
        }

        fitSections();

        lastSectionObserver.unobserve(contentContainer);
        lastSectionObserver.observe(contentContainer);
    };
    const fitSections = () => {
        const sectionGroup =
            formContainerNode.querySelector('.ibexa-anchor-navigation__section-group--active') ??
            formContainerNode.querySelector('.ibexa-anchor-navigation-sections');
        const contentColumn = doc.querySelector('.ibexa-main-container__content-column');
        const firstSection = getFirstSection(sectionGroup);
        const lastSection = getLastSection(sectionGroup);
        const contentContainer = lastSection.closest('.ibexa-edit-content__container');

        if (!firstSection.isSameNode(lastSection) && lastSection.offsetHeight) {
            const lastSectionHeight = lastSection.offsetHeight;
            const headerHeight = headerContainer?.offsetHeight;
            const contentColumnHeight = contentColumn.offsetHeight;
            const additionalContentHeight = Math.max(contentContainer.offsetHeight - sectionGroup.offsetHeight, 0);
            const valueToCorrectHeightDiff = headerHeight + additionalContentHeight;
            const lastSectionHeightDiff = contentColumnHeight - lastSectionHeight - valueToCorrectHeightDiff;

            if (lastSectionHeightDiff > 0) {
                contentContainer.style.paddingBottom = `${lastSectionHeightDiff}px`;
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
                    const headerBottomContainerHeight = header.offsetHeight - headerContainer?.offsetHeight;

                    return top + height >= headerContainer?.offsetHeight + headerBottomContainerHeight + SECTION_ADJUST_MARGIN_TOP;
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
    const attachListenForIsInvalidClass = () => {
        const classChangedCallback = (mutationList) => {
            mutationList.forEach((mutation) => {
                const { oldValue, target } = mutation;
                const hadIsInvalidClass = oldValue?.includes('.is-invalid') ?? false;
                const hasIsInvalidClass = target.classList.contains('is-invalid');

                if (hadIsInvalidClass !== hasIsInvalidClass) {
                    const sectionGroup = target.closest('.ibexa-anchor-navigation__section-group');

                    if (!sectionGroup) {
                        return;
                    }

                    const { id } = sectionGroup.dataset;
                    const hasGroupError = !!sectionGroup.querySelector('.is-invalid');
                    const correspondingMenuItem =
                        doc.querySelector(`.ibexa-switcher__item[data-target-id="${id}"]`) ??
                        doc.querySelector(`.ibexa-anchor-navigation-menu .ibexa-dropdown__item[data-value="${id}"]`);

                    if (!correspondingMenuItem) {
                        return;
                    }

                    const errorIconNode = correspondingMenuItem.querySelector('.ibexa-switcher__item-error');
                    const dropdownWidget = doc.querySelector('.ibexa-anchor-navigation-menu .ibexa-dropdown');

                    errorIconNode.classList.toggle('ibexa-switcher__item-error--hidden', !hasGroupError);

                    if (dropdownWidget) {
                        const hasError = !!dropdownWidget.querySelector(
                            '.ibexa-anchor-navigation-menu__item-error:not(ibexa-anchor-navigation-menu__item-error--hidden)',
                        );
                        const errorDropdownContainer = doc.querySelector('.ibexa-anchor-navigation-menu__error');

                        errorDropdownContainer.classList.toggle('ibexa-anchor-navigation-menu__error--hidden', !hasError);
                    }
                }
            });
        };
        const observer = new MutationObserver(classChangedCallback);

        observer.observe(formContainerNode, {
            subtree: true,
            attributes: true,
            attributeFilter: ['class'],
            attributeOldValue: true,
        });
    };

    attachSectionGroupsMenuListEvents();
    attachSectionGroupsMenuDropdownEvents();
    attachSectionsMenuEvents();
    attachScrollContainerEvents();
    attachListenForIsInvalidClass();
    initFitSection();
    ibexa.helpers.tooltips.parse(navigationMenu);
})(window, window.document, window.ibexa);
