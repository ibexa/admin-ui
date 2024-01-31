(function (global, doc, ibexa, Popper) {
    class MultilevelPopupMenu {
        constructor(config) {
            this.container = config.container;
            this.triggerElement = config.triggerElement;
            this.referenceElement = config.referenceElement ?? this.triggerElement;
            this.onTopBranchClosed = config.onTopBranchClosed ?? (() => {});
            this.onTopBranchOpened = config.onTopBranchOpened ?? (() => {});
            this.processBranchOnInitAfter = config.processBranchOnInitAfter ?? (() => {});
            this.processItemOnInitAfter = config.processItemOnInitAfter ?? (() => {});
            this.initialBranchPlacement = config.initialBranchPlacement ?? 'right-start';
            this.initialBranchFallbackPlacements = config.initialBranchFallbackPlacements ?? ['right-end', 'left-start', 'left-end'];

            this.hoveredItemsBranches = new Set();
            this.hoveredBranches = new Set();

            this.handleClickOutside = this.handleClickOutside.bind(this);
            this.handleItemWithSubitemsClick = this.handleItemWithSubitemsClick.bind(this);

            doc.addEventListener('click', this.handleClickOutside, false);

            ibexa.helpers.objectInstances.setInstance(this.container, this);
        }

        init() {
            const topBranch = this.container.querySelector('.ibexa-multilevel-popup-menu__branch');

            if (!topBranch) {
                return;
            }

            const itemsWithSubitems = this.container.querySelectorAll('.ibexa-popup-menu__item--has-subitems');

            this.initBranch({
                triggerElement: this.triggerElement,
                branchElement: topBranch,
                referenceElement: this.referenceElement,
                placement: this.initialBranchPlacement,
                fallbackPlacements: this.initialBranchFallbackPlacements,
                processBranchAfter: this.processBranchOnInitAfter,
                processBranchItemAfter: this.processItemOnInitAfter,
            });
            this.triggerElement.branchElement = topBranch;

            itemsWithSubitems.forEach((itemElement) => {
                const branchElement = itemElement.querySelector(':scope > .ibexa-multilevel-popup-menu__branch');
                const parentBranchElement = itemElement.closest('.ibexa-popup-menu');

                this.initBranch({
                    triggerElement: itemElement,
                    branchElement: branchElement,
                    processBranchAfter: this.processBranchOnInitAfter,
                    processBranchItemAfter: this.processItemOnInitAfter,
                });

                itemElement.branchElement = branchElement;
                branchElement.itemElement = itemElement;
                branchElement.parentBranchElement = parentBranchElement;
            });
        }

        initBranch({
            triggerElement,
            branchElement,
            referenceElement = null,
            placement = 'right-start',
            fallbackPlacements = ['right-end', 'left-start', 'left-end'],
            processBranchAfter = () => {},
            processBranchItemAfter = () => {},
        }) {
            doc.body.appendChild(branchElement);

            const isTopBranch = !triggerElement.classList.contains('ibexa-popup-menu__item');
            const branchItems = this.getBranchItems(branchElement);
            const offset = isTopBranch ? [0, 3] : [-8, 2];
            const branchSearchInput = branchElement.querySelector('.ibexa-multilevel-popup-menu__search-input');
            const popperInstance = Popper.createPopper(referenceElement ?? triggerElement, branchElement, {
                placement,
                modifiers: [
                    {
                        name: 'flip',
                        enabled: true,
                        options: {
                            fallbackPlacements,
                        },
                    },
                    {
                        name: 'offset',
                        options: {
                            offset,
                        },
                    },
                ],
            });

            branchSearchInput.addEventListener('keyup', this.filterBranchItems, false);
            branchSearchInput.addEventListener('input', this.filterBranchItems, false);
            branchElement.popperInstance = popperInstance;

            if (isTopBranch) {
                triggerElement.addEventListener('click', this.handleItemWithSubitemsClick, false);

                return;
            }

            triggerElement.addEventListener(
                'mouseenter',
                () => {
                    this.hoveredItemsBranches.add(branchElement);
                    this.updateBranchAndParentBranchesOpenState(branchElement);
                },
                false,
            );
            triggerElement.addEventListener(
                'mouseleave',
                () =>
                    setTimeout(() => {
                        this.hoveredItemsBranches.delete(branchElement);
                        this.updateBranchAndParentBranchesOpenState(branchElement);
                    }, 50),
                false,
            );
            branchElement.addEventListener(
                'mouseenter',
                () => {
                    this.hoveredBranches.add(branchElement);
                    this.updateBranchAndParentBranchesOpenState(branchElement);
                },
                false,
            );
            branchElement.addEventListener(
                'mouseleave',
                () => {
                    setTimeout(() => {
                        this.hoveredBranches.delete(branchElement);
                        this.updateBranchAndParentBranchesOpenState(branchElement);
                    }, 50);
                },
                false,
            );
            branchElement.addEventListener(
                'ibexa-multilevel-popup-menu:close-branch',
                () => {
                    this.hoveredBranches.delete(branchElement);
                    this.updateBranchAndParentBranchesOpenState(branchElement);
                },
                false,
            );

            processBranchAfter(branchElement);
            branchItems.forEach((itemElement) => processBranchItemAfter(itemElement));
        }

        updateBranchAndParentBranchesOpenState(branchElement) {
            const isTopBranch = !branchElement?.parentBranchElement;

            if (isTopBranch) {
                return;
            }

            this.updateBranchOpenState(branchElement);
            this.updateBranchAndParentBranchesOpenState(branchElement.parentBranchElement);
        }

        updateBranchOpenState(branchElement) {
            const searchInput = branchElement.querySelector('.ibexa-multilevel-popup-menu__search-input');
            const isSearchInputFilled = !!searchInput?.value;
            const isSubbranchOpened = (otherBranchElement) => {
                return (
                    otherBranchElement &&
                    (branchElement === otherBranchElement || isSubbranchOpened(otherBranchElement.parentBranchElement))
                );
            };
            const isBranchOrAnySubbranchHovered = [...this.hoveredItemsBranches, ...this.hoveredBranches].some(isSubbranchOpened);

            if (isBranchOrAnySubbranchHovered || isSearchInputFilled) {
                this.openBranch(branchElement);
            } else {
                this.closeWithSubbranches(branchElement);
            }
        }

        async handleItemWithSubitemsClick(event) {
            const itemWithSubitems = event.currentTarget;
            const { branchElement } = itemWithSubitems;
            const isExpanded = !branchElement.classList.contains('ibexa-popup-menu--hidden');
            const shouldBeExpanded = !isExpanded;

            if (shouldBeExpanded) {
                this.openBranch(branchElement);
            } else {
                this.closeWithSubbranches(branchElement, shouldBeExpanded);
            }
        }

        async openBranch(branchElement) {
            this.toggleBranch(branchElement);
            await branchElement.popperInstance.update();
        }

        closeBranch(branchElement) {
            this.toggleBranch(branchElement, false);
        }

        toggleBranch(branchElement, shouldBeExpanded = true) {
            const topBranch = this.triggerElement.branchElement;

            branchElement.classList.toggle('ibexa-popup-menu--hidden', !shouldBeExpanded);

            if (branchElement === topBranch) {
                if (shouldBeExpanded) {
                    this.onTopBranchOpened();
                } else {
                    this.onTopBranchClosed();
                }
            }
        }

        closeWithSubbranches(branchElement) {
            const subitemsWithSubitems = branchElement.querySelectorAll(':scope > .ibexa-popup-menu__item--has-subitems');

            subitemsWithSubitems.forEach((subitem) => {
                this.closeWithSubbranches(subitem.branchElement);
            });

            this.closeBranch(branchElement);
        }

        generateMenu(menuTree) {
            const {
                triggerElement,
                groups,
                placement,
                fallbackPlacements,
                processAfterCreated: processBranchAfterCreated,
                hasSearch,
            } = menuTree;

            const branchElement = this.generateBranch(
                {
                    triggerElement,
                    placement,
                    fallbackPlacements,
                    hasSearch,
                },
                processBranchAfterCreated,
            );

            groups.forEach((group) => {
                const { id: groupId, items, processAfterCreated: processGroupAfterCreated } = group;

                this.generateGroup({ id: groupId, branchElement }, processGroupAfterCreated);

                items.forEach((item) => {
                    const { label, sublabel, href, onClick, processAfterCreated: processItemAfterCreated } = item;

                    const itemElement = this.generateItem(
                        { label, sublabel, branchElement, groupId, href, onClick },
                        processItemAfterCreated,
                    );

                    if (item.branch) {
                        this.generateMenu({
                            ...item.branch,
                            triggerElement: itemElement,
                        });
                    }
                });
            });

            return branchElement;
        }

        generateBranch(data, processAfterCreated = () => {}) {
            const { triggerElement, placement, fallbackPlacements, hasSearch = false } = data;
            const { branchTemplate } = this.container.dataset;

            const container = doc.createElement('div');
            const renderedItem = branchTemplate;

            container.insertAdjacentHTML('beforeend', renderedItem);

            const newBranchElement = container.querySelector('.ibexa-multilevel-popup-menu__branch');
            const searchInputWrapper = newBranchElement.querySelector('.ibexa-multilevel-popup-menu__search');

            searchInputWrapper.classList.toggle('ibexa-multilevel-popup-menu__search--hidden', !hasSearch);

            processAfterCreated(newBranchElement, data);

            this.initBranch({ triggerElement, branchElement: newBranchElement, placement, fallbackPlacements });

            const parentBranchElement = triggerElement.closest('.ibexa-popup-menu');

            triggerElement.branchElement = newBranchElement;
            newBranchElement.itemElement = triggerElement;
            newBranchElement.parentBranchElement = parentBranchElement;

            const isTriggerMultilevelMenuItemElement = triggerElement.classList.contains('ibexa-popup-menu__item');

            if (isTriggerMultilevelMenuItemElement) {
                triggerElement.classList.add('ibexa-popup-menu__item--has-subitems');
            }

            return newBranchElement;
        }

        // eslint-disable-next-line no-unused-vars
        generateGroupIfNotExists(data, processAfterCreated = () => {}) {
            const { branchElement, id } = data;
            const groupElement = branchElement.querySelector(`[data-group-id="${id}"]`);

            if (groupElement) {
                return groupElement;
            }

            return this.generateGroup(...arguments);
        }

        generateGroup(data, processAfterCreated = () => {}) {
            const { branchElement, id } = data;
            const { groupTemplate } = this.container.dataset;

            const container = doc.createElement('div');
            const renderedGroup = groupTemplate.replaceAll('{{ group_id }}', id);

            container.insertAdjacentHTML('beforeend', renderedGroup);

            const newGroupElement = container.querySelector('.ibexa-popup-menu__group');

            processAfterCreated(newGroupElement, data);

            const newGroupContainer = branchElement.querySelector('.ibexa-multilevel-popup-menu__groups');

            newGroupContainer.appendChild(newGroupElement);

            return newGroupElement;
        }

        generateItem(data, processAfterCreated = () => {}) {
            const { label, sublabel = '', branchElement, groupId, href, onClick } = data;
            const { itemTemplateBtn, itemTemplateLink } = this.container.dataset;
            const groupElement = this.generateGroupIfNotExists({ branchElement, id: groupId });
            const itemTemplate = !!href ? itemTemplateLink : itemTemplateBtn;

            const container = doc.createElement('div');
            const renderedItem = itemTemplate.replaceAll('{{ label }}', label).replaceAll('{{ sublabel }}', sublabel);

            container.insertAdjacentHTML('beforeend', renderedItem);

            const newItemElement = container.querySelector('.ibexa-popup-menu__item');
            const newItemContentElement = newItemElement.querySelector('.ibexa-popup-menu__item-content');

            if (href) {
                newItemContentElement.href = href;
            }

            if (onClick) {
                newItemContentElement.addEventListener('click', () => onClick(newItemElement, data), false);
            }

            processAfterCreated(newItemElement, data);

            groupElement.appendChild(newItemElement);

            return newItemElement;
        }

        getBranchItems(branchElement) {
            return [
                ...branchElement.querySelectorAll(
                    ':scope > .ibexa-popup-menu__groups > .ibexa-popup-menu__group > .ibexa-popup-menu__item',
                ),
            ];
        }

        toggleItemVisibility(menuItem, shouldBeVisible) {
            const { branchElement } = menuItem;

            menuItem.classList.toggle('ibexa-popup-menu__item--hidden', !shouldBeVisible);

            if (branchElement && !shouldBeVisible) {
                this.closeWithSubbranches(branchElement);
            }
        }

        isBranchBelongingToThisMenu(branch) {
            const topBranch = this.triggerElement.branchElement;

            return !!branch && (topBranch === branch || this.isBranchBelongingToThisMenu(branch.parentBranchElement));
        }

        handleClickOutside(event) {
            const topBranch = this.triggerElement.branchElement;

            if (!topBranch) {
                return;
            }

            const { target } = event;
            const isPopupMenuExpanded = !topBranch.classList.contains('ibexa-popup-menu--hidden');
            const isClickInsideTrigger = this.triggerElement.contains(target);
            const isTargetBranch = target.classList.contains('ibexa-multilevel-popup-menu__branch');
            const targetBranch = target.closest('.ibexa-multilevel-popup-menu__branch');
            const isClickInsideMenu = isTargetBranch || !!targetBranch;

            if (!isPopupMenuExpanded || isClickInsideTrigger || isClickInsideMenu) {
                return;
            }

            const branchesSearchInput = doc.querySelectorAll('.ibexa-multilevel-popup-menu__search-input');

            branchesSearchInput.forEach((searchInput) => {
                if (searchInput.value !== '') {
                    const searchInputBranch = searchInput.closest('.ibexa-multilevel-popup-menu__branch');

                    searchInput.value = '';
                    searchInputBranch.dispatchEvent(new CustomEvent('ibexa-multilevel-popup-menu:close-branch'));
                    searchInput.dispatchEvent(new Event('input'));
                }
            });

            this.closeWithSubbranches(topBranch);
        }

        filterBranchItems(event) {
            const searchInput = event.currentTarget;
            const branch = searchInput.closest('.ibexa-multilevel-popup-menu__branch');
            const branchItems = branch.querySelectorAll('.ibexa-popup-menu__group > .ibexa-popup-menu__item');
            const phraseLowerCase = searchInput.value.toLowerCase();

            branchItems.forEach((item) => {
                const { searchLabel } = item.dataset;
                const searchLabelLowerCase = searchLabel.toLowerCase();
                const hideItem = !searchLabelLowerCase.includes(phraseLowerCase);

                item.classList.toggle('ibexa-popup-menu__item--hidden', hideItem);
            });
        }
    }

    ibexa.addConfig('core.MultilevelPopupMenu', MultilevelPopupMenu);
})(window, window.document, window.ibexa, window.Popper);
