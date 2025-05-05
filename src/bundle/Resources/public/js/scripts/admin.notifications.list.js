(function (global, doc, ibexa, Translator, Routing) {
    const SELECTOR_MODAL_ITEM = '.ibexa-notifications-modal__item';
    const SELECTOR_GO_TO_NOTIFICATION = '.ibexa-notification-view-all__show';
    const SELECTOR_TOGGLE_NOTIFICATION = '.ibexa-notification-view-all__mail';
    const { showErrorNotification } = ibexa.helpers.notification;
    const { getJsonFromResponse } = ibexa.helpers.request;
    const markAllAsReadBtn = doc.querySelector('.ibexa-notification-list__mark-all-read');
    const markAsReadBtn = doc.querySelector('.ibexa-notification-list__btn--mark-as-read');
    const deleteBtn = doc.querySelector('.ibexa-notification-list__btn--delete');
    const checkboxes = [...doc.querySelectorAll('.ibexa-notification-list .ibexa-table__cell--has-checkbox .ibexa-input--checkbox')];
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

    const markSelectedAsRead = () => {
        const selectedNotifications = [...checkboxes]
            .filter((checkbox) => checkbox.checked)
            .map((checkbox) => checkbox.dataset.notificationId);

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

        fetch(request)
            .then(getJsonFromResponse)
            .then((response) => {
                if (response.status === 'success') {
                    global.location.reload();
                }
            })
            .catch(() => {
                const message = Translator.trans(
                    /* @Desc("Cannot mark notifications as read") */
                    'notifications.modal.message.error.mark_as_read',
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

                        notificationRow.querySelectorAll('.ibexa-notification-view-all__notice-dot').forEach((noticeDot) => {
                            noticeDot.setAttribute('data-is-read', (!isRead).toString());
                        });
                        notificationRow.querySelector('.ibexa-notification-view-all__read').innerHTML = statusText;

                        return;
                    }

                    if (!isToggle && response.redirect) {
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
    markAsReadBtn.addEventListener('click', markSelectedAsRead, false);

    const toggleActionButtonState = () => {
        const checkedNotifications = checkboxes.filter((el) => el.checked);
        const isAnythingSelected = checkedNotifications.length > 0;
        const unreadLabel = Translator.trans(/* @Desc("Unread") */ 'notification.unread', {}, 'ibexa_notifications');

        deleteBtn.disabled = !isAnythingSelected;
        markAsReadBtn.disabled =
            !isAnythingSelected ||
            !checkedNotifications.every(
                (checkbox) =>
                    checkbox.closest('.ibexa-table__row').querySelector('.ibexa-notification-view-all__read').innerText === unreadLabel,
            );
    };
    const handleCheckboxChange = (checkbox) => {
        const checkboxFormId = checkbox.dataset?.formCheckboxId;
        const formRemoveCheckbox = doc.querySelector(
            `[data-toggle-button-id="#confirm-notification_selection_remove"] input#${checkboxFormId}`,
        );

        if (formRemoveCheckbox) {
            formRemoveCheckbox.checked = checkbox.checked;
        }

        toggleActionButtonState();
    };

    checkboxes.forEach((checkbox) => checkbox.addEventListener('change', () => handleCheckboxChange(checkbox), false));
})(window, window.document, window.ibexa, window.Translator, window.Routing);
