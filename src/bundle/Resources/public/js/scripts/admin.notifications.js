import { Alert } from '@ibexa-design-system/src/bundle/Resources/public/ts/components/alert';

(function (global, doc, ibexa) {
    const notificationsContainer = doc.querySelector('.ibexa-notifications-container');
    const notifications = JSON.parse(notificationsContainer.dataset.notifications);
    const getTemplate = (label) => {
        const templateName = `template${label.charAt(0).toUpperCase()}${label.slice(1)}`;

        return notificationsContainer.dataset[templateName] ?? notificationsContainer.dataset.templateInfo;
    };
    const addNotification = ({ detail }) => {
        const { onShow, label, message, customIconPath = '', rawPlaceholdersMap = {} } = detail;
        const config = ibexa.adminUiConfig.notifications[label];
        const timeout = config ? config.timeout : 0;
        const container = doc.createElement('div');
        let finalMessage = ibexa.helpers.text.escapeHTML(message);

        Object.entries(rawPlaceholdersMap).forEach(([placeholder, rawText]) => {
            finalMessage = finalMessage.replace(`{{ ${placeholder} }}`, rawText);
        });

        const notification = getTemplate(label).replace('{{ message }}', finalMessage).replace('{{ icon_path }}', customIconPath);

        container.insertAdjacentHTML('beforeend', notification);

        const notificationNode = container.querySelector('.ids-alert');
        const alertInstance = new Alert(notificationNode);

        alertInstance.init();
        notificationsContainer.append(notificationNode);

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
