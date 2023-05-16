(function (global, doc, ibexa) {
    const adapatItemsContainer = doc.querySelector('.ibexa-context-menu');

    if (!adapatItemsContainer) {
        return;
    }

    const menuButtons = [...adapatItemsContainer.querySelectorAll('.ibexa-context-menu__item > .ibexa-btn:not(.ibexa-btn--more)')];
    const popupMenuElement = adapatItemsContainer.querySelector('.ibexa-popup-menu');
    const showPopupButton = adapatItemsContainer.querySelector('.ibexa-btn--more');

    if (!showPopupButton) {
        return;
    }

    const adaptiveItems = new ibexa.core.AdaptiveItems({
        itemHiddenClass: 'ibexa-context-menu__item--hidden',
        container: adapatItemsContainer,
        getActiveItem: () => {
            return adapatItemsContainer.querySelector('.ibexa-context-menu__item');
        },
        onAdapted: (visibleItems, hiddenItems) => {
            const hiddenButtonsIds = [...hiddenItems].map((item) => item.querySelector('.ibexa-btn').id);

            popupMenu.toggleItems((popupMenuItem) => !hiddenButtonsIds.includes(popupMenuItem.dataset.relatedButtonId));
        },
    });
    const popupMenu = new ibexa.core.PopupMenu({
        popupMenuElement,
        triggerElement: showPopupButton,
        onItemClick: (event) => {
            const { relatedButtonId } = event.currentTarget.dataset;
            const button = doc.getElementById(relatedButtonId);

            button.click();
        },
    });
    const popupItemsToGenerate = [...menuButtons].map((button) => {
        const relatedButtonId = button.id;
        const label = button.querySelector('.ibexa-btn__label').textContent;

        return {
            label,
            relatedButtonId,
            disabled: button.disabled,
        };
    });

    popupMenu.generateItems(popupItemsToGenerate, (itemElement, item) => {
        const itemContentElement = itemElement.querySelector('.ibexa-popup-menu__item-content');

        itemElement.dataset.relatedButtonId = item.relatedButtonId;

        if (item.disabled) {
            itemContentElement.classList.add('ibexa-popup-menu__item-content--disabled');
        }
    });

    adaptiveItems.init();
    adapatItemsContainer.classList.remove('ibexa-context-menu--before-adaptive-items-init');
})(window, window.document, window.ibexa);
