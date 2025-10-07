(function (global) {
    const ACTION_BTN_VERTICAL_SPACING = 4.3;
    let actionButtonConfigs = [];

    const registerButton = (config) => {
        if (!config || !config.container || actionButtonConfigs.some((btn) => btn.id === config.id)) {
            return;
        }

        actionButtonConfigs = [...actionButtonConfigs, config].sort((a, b) => a.priority - b.priority);
        recalculateButtonsLayout();
    };
    const unregisterButton = (id) => {
        actionButtonConfigs = actionButtonConfigs.filter((btn) => btn.id !== id);
        recalculateButtonsLayout();
    };
    const recalculateButtonsLayout = () => {
        const buttonsToRender = actionButtonConfigs.filter((btn) => {
            if (typeof btn.checkVisibility === 'function') {
                const isVisible = btn.checkVisibility();

                return isVisible;
            }

            return false;
        });

        buttonsToRender.forEach((buttonConfig, index) => {
            const { container } = buttonConfig;

            if (!container.style.transition) {
                container.style.transition = 'all 0.3s ease-in-out';
            }

            container.style.position = 'fixed';
            container.style.right = '2rem';
            container.style.zIndex = buttonConfig.zIndex || 1040;

            const bottomPosition = `${index * ACTION_BTN_VERTICAL_SPACING + 2}rem`;

            container.style.bottom = bottomPosition;
        });
    };

    global.ibexa.quickAction = {
        registerButton,
        unregisterButton,
        recalculateButtonsLayout,
    };
})(window);
