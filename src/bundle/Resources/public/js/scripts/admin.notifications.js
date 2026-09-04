import { appendNotification } from './helpers/notification.helper';

(function (global, doc, ibexa) {
    const notificationsContainer = doc.querySelector('.ibexa-notifications-container');
    const notifications = JSON.parse(notificationsContainer.dataset.notifications);
    const addNotification = ({ detail }) => {
        const { onShow, label, message, customIconPath = '' } = detail;
        const config = ibexa.adminUiConfig.notifications[label];
        const timeout = config ? config.timeout : 0;
        const { alertInstance } = appendNotification(notificationsContainer, {
            label,
            message,
            iconPath: customIconPath,
            onShow,
        });

        if (timeout) {
            global.setTimeout(() => alertInstance.dismiss(), timeout);
        }
    };

    Object.entries(notifications).forEach(([label, messages]) => {
        messages.forEach((message) => addNotification({ detail: { label, message } }));
    });

    doc.body.addEventListener('ibexa-notify', addNotification, false);
})(window, window.document, window.ibexa);
