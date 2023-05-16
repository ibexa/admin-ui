(function (global, doc, ibexa) {
    const OFFSET_ROUNDING_COMPENSATOR = 0.5;
    class AdaptiveItems {
        constructor(config) {
            this.isVertical = config.isVertical ?? false;
            this.prepareItemsBeforeAdapt = config.prepareItemsBeforeAdapt ?? (() => {});
            this.container = config.container;
            this.items = config.items
                ? [...config.items]
                : [...this.container.querySelectorAll(':scope > .ibexa-adaptive-items__item:not(.ibexa-adaptive-items__item--selector)')];
            this.selectorItem = config.selectorItem ?? this.container.querySelector(':scope > .ibexa-adaptive-items__item--selector');
            this.itemHiddenClass = config.itemHiddenClass;
            this.getActiveItem = config.getActiveItem;
            this.onAdapted = config.onAdapted;
            this.classForceHide = config.classForceHide ?? 'ibexa-adaptive-items__item--force-hide';
            this.classForceShow = config.classForceShow ?? 'ibexa-adaptive-items__item--force-show';
            this.animationFrame = null;
            this.containerResizeObserver = new ResizeObserver(() => {
                if (this.animationFrame) {
                    global.cancelAnimationFrame(this.animationFrame);
                }

                this.animationFrame = global.requestAnimationFrame(() => {
                    this.adapt();
                });
            });
        }

        init() {
            this.adapt();
            this.containerResizeObserver.observe(this.container);
        }

        adapt() {
            const sizeProperty = this.isVertical ? 'offsetHeight' : 'offsetWidth';
            const maxTotalSize = this.container[sizeProperty] - OFFSET_ROUNDING_COMPENSATOR;

            this.prepareItemsBeforeAdapt();

            [this.selectorItem, ...this.items].forEach((item) => item.classList.remove(this.itemHiddenClass));

            const activeItem = this.getActiveItem();
            const activeItemSize = activeItem ? activeItem[sizeProperty] + OFFSET_ROUNDING_COMPENSATOR : 0;
            const selectorSize = this.selectorItem[sizeProperty] + OFFSET_ROUNDING_COMPENSATOR;
            const forceVisibleItemsSize = [...this.items].reduce((totalSize, item) => {
                const computedSize = item.classList.contains(this.classForceShow) ? item[sizeProperty] + OFFSET_ROUNDING_COMPENSATOR : 0;

                return totalSize + computedSize;
            }, 0);
            const hiddenItemsWithoutSelector = new Set();
            let currentSize = selectorSize + activeItemSize + forceVisibleItemsSize;

            const itemsWithoutForce = this.items.filter((item) => {
                const isForceHide = item.classList.contains(this.classForceHide);
                const isForceVisible = item.classList.contains(this.classForceShow);

                return !isForceHide && !isForceVisible;
            });

            for (let i = 0; i < this.items.length; i++) {
                const item = this.items[i];
                const isForceHide = item.classList.contains(this.classForceHide);
                const isForceVisible = item.classList.contains(this.classForceShow);

                if (isForceHide) {
                    hiddenItemsWithoutSelector.add(item);

                    continue;
                }

                if (item === activeItem) {
                    continue;
                }

                const lastItem = this.items[this.items.length - 1];
                const isLastNonactiveItem =
                    lastItem === activeItem ? i === itemsWithoutForce.length - 2 : i === itemsWithoutForce.length - 1;
                const allPreviousItemsVisible = hiddenItemsWithoutSelector.size === 0;
                const fitsInsteadOfSelector = item[sizeProperty] + OFFSET_ROUNDING_COMPENSATOR < maxTotalSize - currentSize + selectorSize;

                if (isLastNonactiveItem && allPreviousItemsVisible && fitsInsteadOfSelector) {
                    break;
                }

                const itemComputedSize = item[sizeProperty] + OFFSET_ROUNDING_COMPENSATOR;

                if (itemComputedSize > maxTotalSize - currentSize && !isForceVisible) {
                    hiddenItemsWithoutSelector.add(item);
                }

                currentSize += itemComputedSize;
            }

            this.items.forEach((item) => {
                item.classList.toggle(this.itemHiddenClass, hiddenItemsWithoutSelector.has(item));
            });
            this.selectorItem.classList.toggle(this.itemHiddenClass, !hiddenItemsWithoutSelector.size);

            const visibleItemsWithoutSelector = new Set([...this.items].filter((item) => !hiddenItemsWithoutSelector.has(item)));

            this.onAdapted?.(visibleItemsWithoutSelector, hiddenItemsWithoutSelector);
        }
    }

    ibexa.addConfig('core.AdaptiveItems', AdaptiveItems);
})(window, window.document, window.ibexa);
