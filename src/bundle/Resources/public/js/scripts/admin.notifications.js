(function (global, doc, ibexa) {
    const notificationsContainer = doc.querySelector('.ibexa-notifications-container');
    const notifications = JSON.parse(notificationsContainer.dataset.notifications);
    const { template } = notificationsContainer.dataset;
    const iconsMap = {
        info: 'system-information',
        error: 'circle-close',
        warning: 'warning-triangle',
        success: 'checkmark',
    };
    const addNotification = ({ detail }) => {
        const { onShow, label, message, customIconPath, rawPlaceholdersMap = {} } = detail;
        const config = ibexa.adminUiConfig.notifications[label];
        const timeout = config ? config.timeout : 0;
        const container = doc.createElement('div');
        const iconPath = customIconPath ?? ibexa.helpers.icon.getIconPath(iconsMap[label]);
        let finalMessage = ibexa.helpers.text.escapeHTML(message);

        Object.entries(rawPlaceholdersMap).forEach(([placeholder, rawText]) => {
            finalMessage = finalMessage.replace(`{{ ${placeholder} }}`, rawText);
        });

        const notification = template
            .replace('{{ label }}', label)
            .replace('{{ message }}', finalMessage)
            .replace('{{ icon_path }}', iconPath);

        container.insertAdjacentHTML('beforeend', notification);

        const notificationNode = container.querySelector('.alert');

        notificationsContainer.append(notificationNode);

        if (timeout) {
            global.setTimeout(() => notificationNode.querySelector('.ibexa-alert__close-btn').click(), timeout);
        }

        if (typeof onShow === 'function') {
            onShow(notificationNode);
        }
    };

    Object.entries(notifications).forEach(([label, messages]) => {
        messages.forEach((message) => addNotification({ detail: { label, message } }));
    });

    doc.body.addEventListener('ibexa-notify', addNotification, false);
})(window, window.document, window.ibexa);
