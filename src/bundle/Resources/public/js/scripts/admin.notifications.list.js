(function (global, doc, ibexa, Translator, Routing) {
    const SELECTOR_MODAL_ITEM = '.ibexa-notifications-modal__item';
    const SELECTOR_GO_TO_NOTIFICATION = '.ibexa-notification-view-all__show';
    const SELECTOR_TOGGLE_NOTIFICATION = '.ibexa-notification-view-all__mail';
    const { showErrorNotification } = ibexa.helpers.notification;
    const { getJsonFromResponse } = ibexa.helpers.request;
    const markAllAsReadBtn = doc.querySelector('.ibexa-notification-list__mark-all-as-read');
    const markAsReadBtn = doc.querySelector('.ibexa-notification-list__btn-mark-as-read');
    const deleteBtn = doc.querySelector('.ibexa-notification-list__btn-delete');
    const notificationsCheckboxes = [
        ...doc.querySelectorAll('.ibexa-notification-list .ibexa-table__cell--has-checkbox .ibexa-input--checkbox'),
    ];
    const markAllAsRead = () => {
        const markAllAsReadLink = Routing.generate('ibexa.notifications.mark_all_as_read');
        const message = Translator.trans(
            /* @Desc("Cannot mark all notifications as read") */ 'notifications.modal.message.error.mark_all_as_read',
            {},
            'ibexa_notifications',
        );

        fetch(markAllAsReadLink, { mode: 'same-origin', credentials: 'same-origin' })
            .then(getJsonFromResponse)
            .then((response) => {
                if (response.status === 'success') {
                    clearCheckboxes();
                    global.location.reload();
                } else {
                    showErrorNotification(message);
                }
            })
            .catch(() => {
                showErrorNotification(message);
            });
    };

    const markSelectedAsRead = () => {
        const selectedNotifications = [...notificationsCheckboxes]
            .filter((checkbox) => checkbox.checked)
            .map((checkbox) => checkbox.dataset.notificationId);

        if (!selectedNotifications.length) {
            return;
        }

        const markAsReadLink = Routing.generate('ibexa.notifications.mark_multiple_as_read');
        const request = new Request(markAsReadLink, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            mode: 'same-origin',
            credentials: 'same-origin',
            body: JSON.stringify({
                ids: selectedNotifications,
            }),
        });
        const message = Translator.trans(
            /* @Desc("Cannot mark selected notifications as read") */
            'notifications.modal.message.error.mark_selected_as_read',
            {},
            'ibexa_notifications',
        );

        fetch(request)
            .then(getJsonFromResponse)
            .then((response) => {
                if (response.status === 'success') {
                    clearCheckboxes();
                    global.location.reload();
                } else {
                    showErrorNotification(message);
                }
            })
            .catch(() => {
                showErrorNotification(message);
            });
    };

    const clearCheckboxes = () => {
        // Firefox persists checkbox states on refresh — manually reset them
        const multipleCheckbox = doc.querySelector('.ibexa-input--checkbox.ibexa-table__header-cell-checkbox');
        multipleCheckbox.checked = false;

        notificationsCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
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

                        notificationRow.querySelectorAll('.ibexa-notification-view-all__notice-dot').forEach((noticeDot) => {
                            noticeDot.setAttribute('data-is-read', (!isRead).toString());
                        });
                        notificationRow.querySelector('.ibexa-notification-view-all__read').innerHTML = statusText;
                        getNotificationsStatus();
                        toggleActionButtonState();

                        return;
                    }

                    if (!isToggle && response.redirect) {
                        global.location = response.redirect;
                    }
                } else {
                    const message = Translator.trans(
                        /* @Desc("Cannot update this notification") */
                        'notifications.modal.message.error.update',
                        {},
                        'ibexa_notifications',
                    );

                    showErrorNotification(message);
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
    const getNotificationsStatus = () => {
        const notificationsTable = doc.querySelector('.ibexa-table--notifications');
        const notificationsStatusLink = notificationsTable.dataset.notificationsCount;
        const request = new Request(notificationsStatusLink, {
            mode: 'cors',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return fetch(request)
            .then(getJsonFromResponse)
            .then((notificationsInfo) => {
                markAllAsReadBtn.disabled = notificationsInfo.pending === 0;
            });
    };
    const init = () => {
        getNotificationsStatus();
        doc.querySelector('.ibexa-notifications-modal').dataset.closeReload = 'true';

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

    init();

    doc.querySelectorAll(SELECTOR_GO_TO_NOTIFICATION).forEach((link) =>
        link.addEventListener('click', handleNotificationActionClick, false),
    );
    doc.querySelectorAll(SELECTOR_TOGGLE_NOTIFICATION).forEach((link) =>
        link.addEventListener('click', (event) => handleNotificationActionClick(event, true), false),
    );
    markAllAsReadBtn.addEventListener('click', markAllAsRead, false);
    markAsReadBtn.addEventListener('click', markSelectedAsRead, false);

    const toggleActionButtonState = () => {
        const checkedNotifications = notificationsCheckboxes.filter((el) => el.checked);
        const isAnythingSelected = checkedNotifications.length > 0;

        deleteBtn.disabled = !isAnythingSelected;
        markAsReadBtn.disabled =
            !isAnythingSelected ||
            !checkedNotifications.some(
                (checkbox) =>
                    checkbox.closest('.ibexa-table__row').querySelector('.ibexa-notification-view-all__notice-dot').dataset.isRead ===
                    'false',
            );
    };
    const handleCheckboxChange = (checkbox) => {
        const checkboxFormId = checkbox.dataset?.formCheckboxId;
        const formRemoveCheckbox = doc.querySelector(`[data-toggle-button-id="#confirm-selection_remove"] input#${checkboxFormId}`);

        if (formRemoveCheckbox) {
            formRemoveCheckbox.checked = checkbox.checked;
        }

        toggleActionButtonState();
    };

    notificationsCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', () => handleCheckboxChange(checkbox), false));
})(window, window.document, window.ibexa, window.Translator, window.Routing);
