(function (global, doc, ibexa, Popper) {
    class MultilevelPopupMenu {
        constructor(config) {
            this.container = config.container;
            this.triggerElement = config.triggerElement;
            this.referenceElement = config.referenceElement ?? this.triggerElement;
            this.onTopBranchClosed = config.onTopBranchClosed ?? (() => {});
            this.onTopBranchOpened = config.onTopBranchOpened ?? (() => {});
            this.initialBranchPlacement = config.initialBranchPlacement ?? 'right-start';
            this.initialBranchFallbackPlacements = config.initialBranchFallbackPlacements ?? ['right-end', 'left-start', 'left-end'];

            this.handleClickOutside = this.handleClickOutside.bind(this);
            this.handleItemWithSubitemsClick = this.handleItemWithSubitemsClick.bind(this);

            doc.addEventListener('click', this.handleClickOutside, false);
        }

        init() {
            console.log('a');
            const topBranch = this.container.querySelector('.ibexa-multilevel-popup-menu__branch');

            if (!topBranch) {
                return;
            }

            const itemsWithSubitems = this.container.querySelectorAll('ibexa-popup-menu__item--has-subitems');

            this.initBranch(
                this.triggerElement,
                topBranch,
                this.referenceElement,
                this.initialBranchPlacement,
                this.initialBranchFallbackPlacements,
            );
            this.triggerElement.branchElement = topBranch;

            itemsWithSubitems.forEach((item) => {
                const branchElement = item.querySelector(':scope > .ibexa-multilevel-popup-menu__branch');

                this.initBranch(item, branchElement);

                item.branchElement = branchElement;
            });
        }

        initBranch(
            triggerElement,
            branchElement,
            referenceElement = null,
            placement = 'right-start',
            fallbackPlacements = ['right-end', 'left-start', 'left-end'],
        ) {
            // this.container.appendChild(branchElement);
            doc.body.appendChild(branchElement)

            const isTopBranch = !triggerElement.classList.contains('ibexa-popup-menu__item');
            const offset = isTopBranch ? [0, 3] : [-8, 8];

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

            branchElement.popperInstance = popperInstance;
            triggerElement.addEventListener('click', this.handleItemWithSubitemsClick, false);
        }

        async handleItemWithSubitemsClick(event) {
            const itemWithSubitems = event.currentTarget;
            const { branchElement } = itemWithSubitems;
            const isExpanded = !branchElement.classList.contains('ibexa-popup-menu--hidden');
            const shouldBeExpanded = !isExpanded;

            if (shouldBeExpanded) {
                // await branchElement.popperInstance.update();

                this.openBranch(branchElement);
                await branchElement.popperInstance.update();
            } else {
                this.closeWithSubbranches(branchElement, shouldBeExpanded);
            }
        }

        openBranch(branchElement) {
            this.toggleBranch(branchElement);
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

        generateBranch(data, processAfterCreated = () => {}) {
            const { triggerElement, placement, fallbackPlacements } = data;
            const { branchTemplate } = this.container.dataset;

            const container = doc.createElement('div');
            const renderedItem = branchTemplate;

            container.insertAdjacentHTML('beforeend', renderedItem);

            const newBranchElement = container.querySelector('.ibexa-multilevel-popup-menu__branch');

            processAfterCreated(newBranchElement, data);

            this.initBranch(triggerElement, newBranchElement, null, placement, fallbackPlacements);
            triggerElement.branchElement = newBranchElement;

            const isTriggerMultilevelMenuItemElement = triggerElement.classList.contains('ibexa-popup-menu__item');

            if (isTriggerMultilevelMenuItemElement) {
                triggerElement.classList.add('ibexa-popup-menu__item--has-subitems');
            }

            return newBranchElement;
        }

        generateItem(data, processAfterCreated = () => {}) {
            const { label, branchElement } = data;
            const { itemTemplate } = this.container.dataset;

            const container = doc.createElement('div');
            const renderedItem = itemTemplate.replaceAll('{{ label }}', label);

            container.insertAdjacentHTML('beforeend', renderedItem);

            const newItemElement = container.querySelector('.ibexa-popup-menu__item');

            processAfterCreated(newItemElement, data);

            branchElement.appendChild(newItemElement);

            return newItemElement;
        }

        getBranchItems(branchElement) {
            return [...branchElement.querySelectorAll(':scope > .ibexa-popup-menu__item')];
        }

        toggleItemVisibility(menuItem, shouldBeVisible) {
            const { branchElement } = menuItem;

            menuItem.classList.toggle('ibexa-popup-menu__item--hidden', !shouldBeVisible);

            if (branchElement && !shouldBeVisible) {
                this.closeWithSubbranches(branchElement);
            }
        }

        handleClickOutside(event) {
            const topBranch = this.triggerElement.branchElement;
            const isPopupMenuExpanded = !topBranch.classList.contains('ibexa-popup-menu--hidden');
            const isClickInsideTrigger = this.triggerElement.contains(event.target);
            // TODO: check if this branch belongs to our component
            const isClickInsideBranch = event.target.closest('.ibexa-popup-menu');

            if (!isPopupMenuExpanded || isClickInsideTrigger || isClickInsideBranch) {
                return;
            }

            this.closeWithSubbranches(topBranch);
        }
    }

    ibexa.addConfig('core.MultilevelPopupMenu', MultilevelPopupMenu);
})(window, window.document, window.ibexa, window.Popper);
