(function (global) {
    let registeredActionButtons = [];

    const QuickActionManager = (() => {
        const registerButton = (config) => {
            if (!config || !config.selector || registeredActionButtons.some((btn) => btn.name === config.name)) {
                return;
            }

            registeredActionButtons = [...registeredActionButtons, config];
            recalculateButtonsLayout();
        };
        const unregisterButton = (name) => {
            registeredActionButtons = registeredActionButtons.filter((btn) => btn.name !== name);
            recalculateButtonsLayout();
        };
        const recalculateButtonsLayout = () => {
            const sortedButtons = registeredActionButtons.sort((a, b) => a.priority - b.priority);
            const buttonsToRender = sortedButtons.filter((el) => {
                if (el.checkVisibility && typeof el.checkVisibility === 'function') {
                    const isVisible = el.checkVisibility();

                    return isVisible;
                }

                return false;
            });

            buttonsToRender.forEach((buttonConfig, index) => {
                const { selector } = buttonConfig;

                if (!selector.style.transition) {
                    selector.style.transition = 'all 0.3s ease-in-out';
                }

                selector.style.position = 'fixed';
                selector.style.right = '2rem';
                selector.style.zIndex = buttonConfig.zIndex || 1040;

                const bottomPosition = `${index === 0 ? 2 : (index + 1) * 3.2}rem`;

                selector.style.bottom = bottomPosition;
            });
        };

        return {
            registerButton,
            unregisterButton,
            recalculateButtonsLayout,
        };
    })();

    global.ibexa.adminUiConfig.quickActionManager = QuickActionManager;
})(window);
