(function (global, doc, iconPaths) {
    const notificationsContainer = doc.querySelector('.ibexa-notifications-container');
    const notifications = JSON.parse(notificationsContainer.dataset.notifications);
    const { template } = notificationsContainer.dataset;
    const iconsMap = {
        info: 'system-information',
        error: 'circle-close',
        warning: 'warning-triangle',
        success: 'checkmark',
    };
    const escapeHTML = (string) => {
        const stringTempNode = doc.createElement('div');

        stringTempNode.appendChild(doc.createTextNode(string));

        return stringTempNode.innerHTML;
    };
    const addNotification = ({ detail }) => {
        const { label, message } = detail;
        const container = doc.createElement('div');
        const iconSetPath = iconPaths.iconSets[iconPaths.defaultIconSet];
        const iconPath = `${iconSetPath}#${iconsMap[label]}`;
        const finalMessage = escapeHTML(message);

        const notification = template
            .replace('{{ label }}', label)
            .replace('{{ message }}', finalMessage)
            .replace('{{ icon_path }}', iconPath);

        container.insertAdjacentHTML('beforeend', notification);

        const notificationNode = container.querySelector('.alert');

        notificationsContainer.append(notificationNode);
    };

    Object.entries(notifications).forEach(([label, messages]) => {
        messages.forEach((message) => addNotification({ detail: { label, message } }));
    });

    doc.body.addEventListener('ibexa-notify', addNotification, false);
})(window, window.document, window.ibexa.iconPaths);
