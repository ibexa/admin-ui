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
    const getVisibleSections = () => {
        let sectionGroupNode = formContainerNode;

        sectionGroupNode = formContainerNode.querySelector('.ibexa-anchor-navigation__section-group--active') ?? sectionGroupNode;
        sectionGroupNode = formContainerNode.querySelector('.ibexa-anchor-navigation__section-group') ?? sectionGroupNode;

        const sections = sectionGroupNode.querySelectorAll('.ibexa-anchor-navigation__section');

        return [...sections];
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
                const visibleSections = getVisibleSections();
                let firstVisibleSection = visibleSections.find((section) => {
                    const { top, height } = section.getBoundingClientRect();
                    const headerBottomContainerHeight = header.offsetHeight - headerContainer?.offsetHeight;

                    return top + height >= headerContainer?.offsetHeight + headerBottomContainerHeight + SECTION_ADJUST_MARGIN_TOP;
                });

                if (!firstVisibleSection) {
                    firstVisibleSection = visibleSections.at(-1);
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
    const getTabHash = (node) => {
        const nodeId = node.href.split('#')[1];

        return `#${nodeId}`;
    };
    const attachMenuTabShowEvents = () => {
        doc.querySelectorAll('.ibexa-anchor-navigation .ibexa-tabs__tab:not(.ibexa-tabs__tab--more)').forEach((tabLink) => {
            tabLink.addEventListener('shown.bs.tab', (event) => {
                const { target, relatedTarget } = event;
                const prevHashId = getTabHash(relatedTarget);
                const currHashId = getTabHash(target);
                const prevMainContentTab = doc.querySelector(`[data-id="${prevHashId}"]`);
                const currMainContentTab = doc.querySelector(`[data-id="${currHashId}"]`);

                prevMainContentTab.classList.toggle('ibexa-anchor-navigation__section-group--active', false);
                currMainContentTab.classList.toggle('ibexa-anchor-navigation__section-group--active', true);

                initFitSection();
            });
        });
    };
    const attachMenuSectionsEvents = () => {
        const items = doc.querySelectorAll('.ibexa-anchor-navigation-menu .ibexa-anchor-navigation-menu__sections-item-btn');

        items.forEach((item) => item.addEventListener('click', onSelectSectionsMenu, false));
    };
    const onSelectSectionsMenu = (event) => {
        const { targetId } = event.currentTarget.dataset;

        navigateTo(targetId);
    };
    const navigateTo = (targetId) => {
        const sectionNode = formContainerNode.querySelector(`.ibexa-anchor-navigation__section[data-id="${targetId}"]`);

        if (!sectionNode) {
            return;
        }

        formContainerNode.scrollTo({
            top: sectionNode.offsetTop,
            behavior: 'smooth',
        });
    };

    attachMenuTabShowEvents();
    attachMenuSectionsEvents();
    initFitSection();
    attachScrollContainerEvents();
    ibexa.helpers.tooltips.parse(navigationMenu);
})(window, window.document, window.ibexa);
