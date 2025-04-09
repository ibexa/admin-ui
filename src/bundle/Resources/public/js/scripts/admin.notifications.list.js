(function (global, doc, ibexa, Translator, Routing) {
    const SELECTOR_MODAL_ITEM = '.ibexa-notifications-modal__item';
    const SELECTOR_GO_TO_NOTIFICATION = '.ibexa-notification-view-all__show';
    const SELECTOR_TOGGLE_NOTIFICATION = '.ibexa-notification-view-all__mail';
    const { showErrorNotification } = ibexa.helpers.notification;
    const { getJsonFromResponse } = ibexa.helpers.request;
    const markAllAsReadBtn = doc.querySelector('.ibexa-notification-list__mark-all-read');
    const markAllAsRead = () => {
        const markAllAsReadLink = Routing.generate('ibexa.notifications.mark_all_as_read');

        fetch(markAllAsReadLink, { mode: 'same-origin', credentials: 'same-origin' })
            .then(ibexa.helpers.request.getJsonFromResponse)
            .then((response) => {
                if (response.status === 'success') {
                    global.location.reload();
                }
            })
            .catch(() => {
                const message = Translator.trans(
                    /* @Desc("Cannot mark all notifications as read") */ 'notifications.modal.message.error.mark_all_as_read',
                    {},
                    'ibexa_notifications',
                );

                showErrorNotification(message);
            });
    };
    const handleNotificationClick = (notification, isToggle = false) => {
        const notificationRow = notification.closest('.ibexa-table__row');
        const isRead = notification.classList.contains('ibexa-notifications-modal__item--read');
        const notificationReadLink =
            isToggle && isRead ? notificationRow.dataset.notificationUnread : notificationRow.dataset.notificationRead;
        const request = new Request(notificationReadLink, {
            mode: 'cors',
            credentials: 'same-origin',
        });

        fetch(request)
            .then(getJsonFromResponse)
            .then((response) => {
                if (response.status === 'success') {
                    notification.classList.toggle('ibexa-notifications-modal__item--read', !isRead);

                    if (isToggle) {
                        notification
                            .querySelector('.ibexa-table__cell .ibexa-notification-view-all__mail-open')
                            ?.classList.toggle('ibexa-notification-view-all__icon-hidden');
                        notification
                            .querySelector('.ibexa-table__cell .ibexa-notification-view-all__mail-closed')
                            ?.classList.toggle('ibexa-notification-view-all__icon-hidden');

                        const statusText = isRead
                            ? Translator.trans(/*@Desc("Unread")*/ 'notification.unread', {}, 'ibexa_notifications')
                            : Translator.trans(/*@Desc("Read")*/ 'notification.read', {}, 'ibexa_notifications');
                        notification.closest('.ibexa-table__row').querySelector('.ibexa-notification-view-all__read').innerHTML =
                            statusText;

                        return;
                    }

                    if (response.redirect) {
                        global.location = response.redirect;
                    }
                }
            })
            .catch(showErrorNotification);
    };

    const handleNotificationActionClick = (event, isToggle = false) => {
        const notification = event.target.closest(SELECTOR_MODAL_ITEM);

        if (!notification) {
            return;
        }

        handleNotificationClick(notification, isToggle);
    };
    const initStatusIcons = () => {
        doc.querySelectorAll(SELECTOR_MODAL_ITEM).forEach((item) => {
            const isRead = item.classList.contains('ibexa-notifications-modal__item--read');

            item.querySelector(`.ibexa-table__cell .ibexa-notification-view-all__mail-closed`)?.classList.toggle(
                'ibexa-notification-view-all__icon-hidden',
                !isRead,
            );
            item.querySelector(`.ibexa-table__cell .ibexa-notification-view-all__mail-open`)?.classList.toggle(
                'ibexa-notification-view-all__icon-hidden',
                isRead,
            );
        }, false);
    };

    initStatusIcons();

    doc.querySelectorAll(SELECTOR_GO_TO_NOTIFICATION).forEach((link) =>
        link.addEventListener('click', handleNotificationActionClick, false),
    );
    doc.querySelectorAll(SELECTOR_TOGGLE_NOTIFICATION).forEach((link) =>
        link.addEventListener('click', (event) => handleNotificationActionClick(event, true), false),
    );
    markAllAsReadBtn.addEventListener('click', markAllAsRead, false);
})(window, window.document, window.ibexa, window.Translator, window.Routing);
