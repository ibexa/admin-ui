(function (global, doc, ibexa, bootstrap) {
    const notificationsContainer = doc.querySelector('.ibexa-notifications-container');
    const notifications = JSON.parse(notificationsContainer.dataset.notifications);
    const { template } = notificationsContainer.dataset;
    const iconsMap = {
        info: 'about',
        error: 'notice',
        warning: 'warning',
        success: 'approved',
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
            const alertBootstrapInstance = bootstrap.Alert.getOrCreateInstance(notificationNode);

            global.setTimeout(() => alertBootstrapInstance.close(), timeout);
        }

        if (typeof onShow === 'function') {
            onShow(notificationNode);
        }
    };

    Object.entries(notifications).forEach(([label, messages]) => {
        messages.forEach((message) => addNotification({ detail: { label, message } }));
    });

    doc.body.addEventListener('ibexa-notify', addNotification, false);
})(window, window.document, window.ibexa, window.bootstrap);
