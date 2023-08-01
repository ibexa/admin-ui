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
            const itemsWithSubitems = this.container.querySelectorAll('.ibexa-multilevel-popup-menu__item--has-subitems');

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
            this.container.appendChild(branchElement);

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
                ],
            });

            branchElement.popperInstance = popperInstance;
            triggerElement.addEventListener('click', this.handleItemWithSubitemsClick, false);
        }

        async handleItemWithSubitemsClick(event) {
            const itemWithSubitems = event.currentTarget;
            const { branchElement } = itemWithSubitems;
            const isExpanded = !branchElement.classList.contains('ibexa-multilevel-popup-menu__branch--hidden');
            const shouldBeExpanded = !isExpanded;

            console.log(branchElement, !isExpanded);

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

            branchElement.classList.toggle('ibexa-multilevel-popup-menu__branch--hidden', !shouldBeExpanded);

            if (branchElement === topBranch) {
                if (shouldBeExpanded) {
                    this.onTopBranchOpened();
                } else {
                    this.onTopBranchClosed();
                }
            }
        }

        closeWithSubbranches(branchElement) {
            const subitemsWithSubitems = branchElement.querySelectorAll(':scope > .ibexa-multilevel-popup-menu__item--has-subitems');

            subitemsWithSubitems.forEach((subitem) => {
                this.closeWithSubbranches(subitem.branchElement);
            });

            this.closeBranch(branchElement);
        }

        // generateItems(itemsToGenerate, processAfterCreated) {
        //     const { itemTemplate } = this.popupMenuElement.dataset;
        //     const fragment = doc.createDocumentFragment();

        //     itemsToGenerate.forEach((item) => {
        //         const container = doc.createElement('ul');
        //         const renderedItem = itemTemplate.replace('{{ label }}', item.label);

        //         container.insertAdjacentHTML('beforeend', renderedItem);

        //         const popupMenuItem = container.querySelector('.ibexa-popup-menu__item');

        //         processAfterCreated(popupMenuItem, item);

        //         popupMenuItem.addEventListener(
        //             'click',
        //             (event) => {
        //                 this.popupMenuElement.classList.add(CLASS_POPUP_MENU_HIDDEN);
        //                 this.onItemClick(event);
        //             },
        //             false,
        //         );

        //         fragment.append(popupMenuItem);
        //     });

        //     this.popupMenuElement.innerHTML = '';
        //     this.popupMenuElement.append(fragment);
        // }

        // attachOnClickToExistingItems() {
        //     const items = this.getItems();

        //     items.forEach(this.attachOnClickToItem);
        // }

        // attachOnClickToItem(item) {
        //     item.querySelector('.ibexa-popup-menu__item-content').addEventListener(
        //         'click',
        //         (event) => {
        //             this.popupMenuElement.classList.add(CLASS_POPUP_MENU_HIDDEN);
        //             this.onItemClick(event);
        //         },
        //         false,
        //     );
        // }

        // getItems() {
        //     return this.popupMenuElement.querySelectorAll('.ibexa-popup-menu__item');
        // }

        // toggleItems(shouldHide) {
        //     const popupMenuItems = [...this.popupMenuElement.querySelectorAll('.ibexa-popup-menu__item')];

        //     popupMenuItems.forEach((popupMenuItem) => {
        //         popupMenuItem.classList.toggle('ibexa-popup-menu__item--hidden', shouldHide(popupMenuItem));
        //     });
        // }

        // handleToggle() {
        //     this.popupMenuElement.classList.toggle(CLASS_POPUP_MENU_HIDDEN);
        //     this.updatePosition();
        // }

        handleClickOutside(event) {
            const topBranch = this.triggerElement.branchElement;
            const isPopupMenuExpanded = !topBranch.classList.contains('ibexa-multilevel-popup-menu__branch--hidden');
            const isClickInsideTrigger = this.triggerElement.contains(event.target);
            const isClickInsideBranch = this.container.contains(event.target);

            if (!isPopupMenuExpanded || isClickInsideTrigger || isClickInsideBranch) {
                return;
            }

            this.closeWithSubbranches(topBranch);
        }

        // updatePosition() {
        //     const isHidden = this.popupMenuElement.classList.contains(CLASS_POPUP_MENU_HIDDEN);

        //     if (isHidden) {
        //         return;
        //     }

        //     this.position(this.popupMenuElement);
        // }
    }

    ibexa.addConfig('core.MultilevelPopupMenu', MultilevelPopupMenu);
})(window, window.document, window.ibexa, window.Popper);
