(function (global, doc, ibexa, Translator) {
    let currentPageLink = null;
    let getNotificationsStatusErrorShowed = false;
    let lastFailedCountFetchNotificationNode = null;
    const SELECTOR_MODAL_ITEM = '.ibexa-notifications-modal__item';
    const SELECTOR_MODAL_RESULTS = '.ibexa-notifications-modal__type-content';
    const SELECTOR_GO_TO_NOTIFICATION = '.ibexa-notification-viewAll__show';
    const SELECTOR_TOGGLE_NOTIFICATION = '.ibexa-notification-viewAll__mail';
    const SELECTOR_MODAL_TITLE = '.ibexa-side-panel__header';
    const SELECTOR_DESC_TEXT = '.description__text';
    const SELECTOR_LIST = '.ibexa-list--notifications';
    const CLASS_ELLIPSIS = 'description__text--ellipsis';
    const CLASS_PAGINATION_LINK = 'page-link';
    const CLASS_MODAL_LOADING = 'ibexa-notifications-modal--loading';
    const INTERVAL = 30000;
    const panel = doc.querySelector('.ibexa-notifications-modal');
    const popupBtns = [...doc.querySelectorAll('.ibexa-multilevel-popup-menu__item-content')];
    const SELECTOR_MORE_ACTION = '.ibexa-notifications-modal--more';
    const { showErrorNotification, showWarningNotification } = ibexa.helpers.notification;
    const { getJsonFromResponse, getTextFromResponse } = ibexa.helpers.request;
    const handleNotificationClick = (notification, isToggle = false) => {
        const notificationRow = notification.closest('.ibexa-table__row');
        const isRead = notification.classList.contains('ibexa-notifications-modal__item--read');
        const notificationReadLink = isToggle && isRead ? notificationRow.dataset.notificationUnread : notificationRow.dataset.notificationRead;
        const request = new Request(notificationReadLink, {
            mode: 'cors',
            credentials: 'same-origin',
        });
 
        fetch(request).then(getJsonFromResponse).then((response) => {
            if (response.status === 'success') {
                notification.classList.toggle('ibexa-notifications-modal__item--read', !isRead);

                if(isToggle) {
                    notification.querySelector('.ibexa-table__cell .ibexa-notification-viewAll__mail-open')?.classList.toggle('ibexa-notification-viewAll__icon-hidden');
                    notification.querySelector('.ibexa-table__cell .ibexa-notification-viewAll__mail-closed')?.classList.toggle('ibexa-notification-viewAll__icon-hidden');
                    
                    const statusText = isRead ? Translator.trans(
                        /*@Desc("Unread")*/ 'notification.unread',
                        {},
                        'ibexa_notifications',
                    ) : Translator.trans(
                        /*@Desc("Read")*/ 'notification.read',
                        {},
                        'ibexa_notifications',
                    );
                    notification.closest('.ibexa-table__row').querySelector('.ibexa-notification-viewAll__read').innerHTML = statusText;
                    return;
                }

                if (response.redirect) {
                    global.location = response.redirect;
                }
            }
        }).catch(showErrorNotification);
    };
    const handleTableClick = (event) => {
        if (event.target.classList.contains('description__read-more')) {
            event.target.closest(SELECTOR_MODAL_ITEM).querySelector(SELECTOR_DESC_TEXT).classList.remove(CLASS_ELLIPSIS);

            return;
        }

        const notification = event.target.closest(SELECTOR_MODAL_ITEM);

        if (!notification) {
            return;
        }

        handleNotificationClick(notification);
    };

    const initNotificationPopup = () => {
        //TODO: init popups
        // const notificationsTable = panel.querySelector(SELECTOR_LIST);
        // const popups = [...panel.querySelectorAll('.ibexa-multilevel-popup-menu:not(.ibexa-multilevel-popup-menu--custom-init)')];
        // popups.forEach(function (popupBtn) {
        //     const multilevelPopupMenu = new ibexa.core.MultilevelPopupMenu({
        //         container: popupBtn,
        //         triggerElement: popupBtn,
        //         // referenceElement: this.container,
        //         initialBranchPlacement: popupBtn.dataset?.initialBranchPlacement,
        //         // initialBranchFallbackPlacements: ['bottom-end', 'top-end', 'top-start'],
        //         // onTopBranchOpened: this.handlePopupOpened,
        //         // onTopBranchClosed: this.handlePopupClosed,
        //     });
        //     multilevelPopupMenu.init();
        // });
    }
    const getNotificationsStatus = () => {
        const notificationsTable = panel.querySelector(SELECTOR_LIST);
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
                setPendingNotificationCount(notificationsInfo);
                updateModalTitleTotalInfo(notificationsInfo.total);
                getNotificationsStatusErrorShowed = false;
            })
            .catch(onGetNotificationsStatusFailure);
    };

    /**
     * Handle a failure while getting notifications status
     *
     * @method onGetNotificationsStatusFailure
     */
    const onGetNotificationsStatusFailure = (error) => {
        if (lastFailedCountFetchNotificationNode && doc.contains(lastFailedCountFetchNotificationNode)) {
            return;
        }

        if (!getNotificationsStatusErrorShowed) {
            const message = Translator.trans(
                /* @Desc("Cannot update notifications") */ 'notifications.modal.message.error',
                { error: error.message },
                'ibexa_notifications',
            );

            showWarningNotification(message, (notificationNode) => {
                lastFailedCountFetchNotificationNode = notificationNode;
            });
        }

        getNotificationsStatusErrorShowed = true;
    };
    const updateModalTitleTotalInfo = (notificationsCount) => {
        const modalTitle = panel.querySelector(SELECTOR_MODAL_TITLE);

        modalTitle.dataset.notificationsTotal = `(${notificationsCount})`;
    };
    const updatePendingNotificationsView = (notificationsInfo) => {
        const noticeDot = doc.querySelector('.ibexa-header-user-menu__notice-dot');

        noticeDot.dataset.count = notificationsInfo.pending;
        noticeDot.classList.toggle('ibexa-header-user-menu__notice-dot--no-notice', notificationsInfo.pending === 0);
    };
    const setPendingNotificationCount = (notificationsInfo) => {
        updatePendingNotificationsView(notificationsInfo);

        const notificationsTable = panel.querySelectzor(SELECTOR_LIST);
        const notificationsTotal = notificationsInfo.total;
        const notificationsTotalOld = parseInt(notificationsTable.dataset.notificationsTotal, 10);

        if (notificationsTotal !== notificationsTotalOld) {
            notificationsTable.dataset.notificationsTotal = notificationsTotal;

            fetchNotificationPage(currentPageLink);
        }
    };
    const showNotificationPage = (pageHtml) => {
        const modalResults = panel.querySelector(SELECTOR_MODAL_RESULTS);

        modalResults.innerHTML = pageHtml;
        toggleLoading(false);
    };
    const toggleLoading = (show) => {
        panel.classList.toggle(CLASS_MODAL_LOADING, show);
    };
    const fetchNotificationPage = (link) => {
        if (!link) {
            return;
        }

        const request = new Request(link, {
            method: 'GET',
            headers: {
                Accept: 'text/html',
            },
            credentials: 'same-origin',
            mode: 'cors',
        });

        currentPageLink = link;
        toggleLoading(true);
        fetch(request).then(getTextFromResponse).then(showNotificationPage).catch(showErrorNotification);
    };
    const handleModalResultsClick = (event) => {
        const isPaginationBtn = event.target.classList.contains(CLASS_PAGINATION_LINK);

        if (isPaginationBtn) {
            handleNotificationsPageChange(event);
            return;
        }

        handleTableClick(event);
    };
    const handleNotificationsPageChange = (event) => {
        event.preventDefault();

        const notificationsPageLink = event.target.href;

        fetchNotificationPage(notificationsPageLink);
    };

    if (!panel) {
        return;
    }

    const initTooltipIfOverflow = (popup) => {
        const label = popup.querySelector('.ibexa-btn__label');
        const popupContainer = popup.closest('.ibexa-multilevel-popup-menu__group');
        
        if (label.scrollWidth < popupContainer.offsetWidth) {
            return;
        }

        popup.title = label.textContent;
        ibexa.helpers.tooltips.parse(popup);
    };
    const handleMoreActionBtnClick =(btn) => {
        const noticeId = btn.closest('.ibexa-notifications-modal__item').dataset.notificationId;
        popupBtns.forEach(function (popupBtn) {
            const actionGroup = popupBtn.closest('.ibexa-multilevel-popup-menu__group');

            if(actionGroup.dataset.groupId === noticeId) {
                return initTooltipIfOverflow(popupBtn);
            };
        });
        //event.removeEventListener('click', handleMoreActionBtnClick);
      };

      const handleNotificationActionClick =(event, isToggle = false) => {
        const notification = event.target.closest(SELECTOR_MODAL_ITEM);

        if (!notification) {
            return
        }

        handleNotificationClick(notification, isToggle);
    }
    const initStatusIcons = () => {
        doc.querySelectorAll(SELECTOR_MODAL_ITEM).forEach((item) => {
            const isRead = item.classList.contains('ibexa-notifications-modal__item--read');
            
            item.querySelector(`.ibexa-table__cell .ibexa-notification-viewAll__mail-closed`)?.classList.toggle('ibexa-notification-viewAll__icon-hidden', !isRead);
            item.querySelector(`.ibexa-table__cell .ibexa-notification-viewAll__mail-open`)?.classList.toggle('ibexa-notification-viewAll__icon-hidden', isRead);
        }, false);
    };

    initStatusIcons(); 
    const notificationsTable = panel.querySelector(SELECTOR_LIST);
    currentPageLink = notificationsTable.dataset.notifications;
    const interval = Number.parseInt(notificationsTable.dataset.notificationsCountInterval, 10) || INTERVAL;

    panel.querySelectorAll(SELECTOR_MODAL_RESULTS).forEach((link) => link.addEventListener('click', handleModalResultsClick, false));
    panel.querySelectorAll(SELECTOR_MORE_ACTION).forEach((btn) => btn.addEventListener('click', () => handleMoreActionBtnClick(btn)));
    doc.querySelectorAll(SELECTOR_GO_TO_NOTIFICATION).forEach((link) => link.addEventListener('click', handleNotificationActionClick, false));
    doc.querySelectorAll(SELECTOR_TOGGLE_NOTIFICATION).forEach((link) => link.addEventListener('click', (event) => handleNotificationActionClick(event, true), false));

    const getNotificationsStatusLoop = () => {
        getNotificationsStatus().finally(() => {
            global.setTimeout(getNotificationsStatusLoop, interval);
        });
    };

    getNotificationsStatusLoop();
})(window, window.document, window.ibexa, window.Translator);
