import { appendNotification } from './helpers/notification.helper';

(function (global, doc, ibexa) {
    const notificationsContainer = doc.querySelector('.ibexa-notifications-container');
    const notifications = JSON.parse(notificationsContainer.dataset.notifications);
    const addNotification = ({ detail }) => {
        const { onShow, label, message, customIconPath = '', rawPlaceholdersMap = {} } = detail;
        const config = ibexa.adminUiConfig.notifications[label];
        const timeout = config ? config.timeout : 0;
        let finalMessage = ibexa.helpers.text.escapeHTML(message);

        Object.entries(rawPlaceholdersMap).forEach(([placeholder, rawText]) => {
            finalMessage = finalMessage.replace(`{{ ${placeholder} }}`, rawText);
        });

        const { notificationNode, alertInstance } = appendNotification(notificationsContainer, {
            label,
            message: finalMessage,
            iconPath: customIconPath,
        });

        if (timeout) {
            global.setTimeout(() => alertInstance.dismiss(), timeout);
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
