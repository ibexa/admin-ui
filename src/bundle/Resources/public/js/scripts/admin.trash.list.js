(function (global, doc, ibexa, React, ReactDOMClient, Translator) {
    const { escapeHTML, escapeHTMLAttribute } = ibexa.helpers.text;
    const { dangerouslySetInnerHTML } = ibexa.helpers.dom;
    const { getInstance } = ibexa.helpers.objectInstances;
    let getUsersTimeout;
    const CLASS_SORTED_ASC = 'ibexa-table__sort-column--asc';
    const CLASS_SORTED_DESC = 'ibexa-table__sort-column--desc';
    const sortedActiveField = doc.querySelector('#trash_search_sort_field').value;
    const sortedActiveDirection = doc.querySelector('#trash_search_sort_direction').value;
    const trashedDateTimeRangeNode = doc.querySelector('.ibexa-trash-search-form__trashed-date-time-range');
    const trashedDateTimeRange = getInstance(trashedDateTimeRangeNode);
    const trashedTypeInput = doc.querySelector('#trash_search_trashed');
    const token = doc.querySelector('meta[name="CSRF-Token"]').content;
    const siteaccess = doc.querySelector('meta[name="SiteAccess"]').content;
    const formSearch = doc.querySelector('form[name="trash_search"]');
    const sortField = doc.querySelector('#trash_search_sort_field');
    const sortDirection = doc.querySelector('#trash_search_sort_direction');
    const creatorInput = doc.querySelector('.ibexa-trash-search-form__item--creator .ibexa-trash-search-form__input');
    const usersList = doc.querySelector('.ibexa-trash-search-form__item--creator .ibexa-trash-search-form__user-list');
    const resetCreatorBtn = doc.querySelector('.ibexa-btn--reset-creator');
    const searchCreatorInput = doc.querySelector('#trash_search_creator');
    const sortableColumns = doc.querySelectorAll('.ibexa-table__sort-column');
    const btns = doc.querySelectorAll('.ibexa-btn--open-udw');
    const udwContainer = doc.getElementById('react-udw');
    const autoSendNodes = doc.querySelectorAll('.ibexa-trash-search-form__item--auto-send');
    const errorMessage = Translator.trans(/* @Desc("Cannot fetch user list") */ 'trash.user_list.error', {}, 'ibexa_trash_ui');
    let udwRoot = null;
    const closeUDW = () => udwRoot.unmount();
    const onConfirm = (form, content) => {
        const field = form.querySelector('#trash_item_restore_location_location');

        field.value = content.map((item) => item.id).join();

        closeUDW();
        form.submit();
    };
    const onCancel = () => closeUDW();
    const openUDW = (event) => {
        event.preventDefault();

        const form = event.target.closest('form[name="trash_item_restore"]');
        const config = JSON.parse(event.currentTarget.dataset.udwConfig);
        const title = Translator.trans(
            /* @Desc("Select a new location to restore the content items") */ 'restore_under_new_location.title',
            {},
            'ibexa_universal_discovery_widget',
        );

        udwRoot = ReactDOMClient.createRoot(udwContainer);
        udwRoot.render(
            React.createElement(ibexa.modules.UniversalDiscovery, {
                onConfirm: onConfirm.bind(this, form),
                onCancel,
                title,
                containersOnly: true,
                multiple: false,
                ...config,
            }),
        );
    };

    btns.forEach((btn) => btn.addEventListener('click', openUDW, false));

    const trashRestoreCheckboxes = [...doc.querySelectorAll('form[name="trash_item_restore"] input[type="checkbox"]')];
    const buttonRestore = doc.querySelector('#trash_item_restore_restore');
    const buttonRestoreUnderNewParent = doc.querySelector('#trash_item_restore_location_select_content');
    const buttonDelete = doc.querySelector('#delete-trash-items');

    const enableButtons = () => {
        const isEmptySelection = trashRestoreCheckboxes.every((el) => !el.checked);
        const isMissingParent = trashRestoreCheckboxes.some((el) => el.checked && parseInt(el.dataset.isParentInTrash, 10) === 1);

        if (buttonRestore) {
            buttonRestore.disabled = isEmptySelection || isMissingParent;
        }

        if (buttonDelete) {
            buttonDelete.disabled = isEmptySelection;
        }

        if (buttonRestoreUnderNewParent) {
            buttonRestoreUnderNewParent.disabled = isEmptySelection;
        }
    };
    const updateTrashForm = (checkboxes) => {
        checkboxes.forEach((checkbox) => {
            const trashFormCheckbox = doc.querySelector(`form[name="trash_item_delete"] input[type="checkbox"][value="${checkbox.value}"]`);

            if (trashFormCheckbox) {
                trashFormCheckbox.checked = checkbox.checked;
            }
        });
    };
    const handleCheckboxChange = (event) => {
        updateTrashForm([event.target]);
        enableButtons();
    };
    const handleResetUser = () => {
        searchCreatorInput.value = '';

        creatorInput.value = '';
        creatorInput.removeAttribute('disabled');
    };
    const handleClickOutsideUserList = (event) => {
        if (event.target.closest('.ibexa-trash-search-form__item--creator')) {
            return;
        }

        creatorInput.value = '';
        usersList.classList.add('ibexa-trash-search-form__item__user-list--hidden');
        doc.querySelector('body').removeEventListener('click', handleClickOutsideUserList, false);
    };
    const getUsersList = (value) => {
        const body = JSON.stringify({
            ViewInput: {
                identifier: `find-user-by-name-${encodeURIComponent(value)}`,
                public: false,
                ContentQuery: {
                    FacetBuilders: {},
                    SortClauses: {},
                    Query: {
                        FullTextCriterion: `${value}*`,
                        ContentTypeIdentifierCriterion: creatorInput.dataset.contentTypeIdentifiers.split(','),
                    },
                    limit: 50,
                    offset: 0,
                },
            },
        });
        const request = new Request('/api/ibexa/v2/views', {
            method: 'POST',
            headers: {
                Accept: 'application/vnd.ibexa.api.View+json; version=1.1',
                'Content-Type': 'application/vnd.ibexa.api.ViewInput+json; version=1.1',
                'X-Siteaccess': siteaccess,
                'X-CSRF-Token': token,
            },
            body,
            mode: 'same-origin',
            credentials: 'same-origin',
        });

        fetch(request)
            .then(ibexa.helpers.request.getJsonFromResponse)
            .then(showUsersList)
            .catch(() => ibexa.helpers.notification.showErrorNotification(errorMessage));
    };
    const createUsersListItem = (user) => {
        const userNameHtmlEscaped = escapeHTML(user.TranslatedName);
        const userNameHtmlAttributeEscaped = escapeHTMLAttribute(user.TranslatedName);

        return `<li data-id="${user._id}" data-name="${userNameHtmlAttributeEscaped}" class="ibexa-trash-search-form__user-item">${userNameHtmlEscaped}</li>`;
    };
    const showUsersList = (data) => {
        const hits = data.View.Result.searchHits.searchHit;
        const users = hits.reduce((total, hit) => total + createUsersListItem(hit.value.Content), '');
        const methodName = users ? 'addEventListener' : 'removeEventListener';

        dangerouslySetInnerHTML(usersList, users);
        usersList.classList.remove('ibexa-trash-search-form__user-list--hidden');

        doc.querySelector('body')[methodName]('click', handleClickOutsideUserList, false);
    };
    const handleTyping = (event) => {
        const value = event.target.value.trim();

        global.clearTimeout(getUsersTimeout);

        if (value.length > 2) {
            getUsersTimeout = global.setTimeout(getUsersList.bind(null, value), 200);
        } else {
            usersList.classList.add('ibexa-trash-search-form__user-list--hidden');
            doc.querySelector('body').removeEventListener('click', handleClickOutsideUserList, false);
        }
    };
    const handleSelectUser = (event) => {
        searchCreatorInput.value = event.target.dataset.id;

        usersList.classList.add('ibexa-trash-search-form__user-list--hidden');

        creatorInput.value = event.target.dataset.name;
        creatorInput.setAttribute('disabled', true);

        doc.querySelector('body').removeEventListener('click', handleClickOutsideUserList, false);
        formSearch.submit();
    };
    const sortTrashItems = (event) => {
        const { target } = event;
        const { field, direction } = target.dataset;

        sortField.value = field;
        target.dataset.direction = direction === 'ASC' ? 'DESC' : 'ASC';
        sortDirection.setAttribute('value', direction === 'DESC' ? 1 : 0);
        formSearch.submit();
    };
    const toggleDatesSelectVisibility = (event) => {
        const datesRangeNode = doc.querySelector(event.target.dataset.targetSelector);

        if (event.target.value !== 'custom_range') {
            trashedDateTimeRange.toggleHidden(true);

            trashedDateTimeRange.clearDates();
            doc.querySelector(datesRangeNode.dataset.periodSelector).value = event.target.value;

            formSearch.submit();

            return;
        }

        trashedDateTimeRange.toggleHidden(false);
    };
    const handleAutoSubmitNodes = (event) => {
        event.preventDefault();

        if (event.target.value !== 'custom_range') {
            formSearch.submit();
        }
    };
    const setSortedClass = () => {
        doc.querySelectorAll('.ibexa-table__sort-column').forEach((node) => {
            node.classList.remove(CLASS_SORTED_ASC, CLASS_SORTED_DESC);
        });

        if (sortedActiveField) {
            const sortedFieldNode = doc.querySelector(`.ibexa-table__sort-column--${sortedActiveField}`);

            if (!sortedFieldNode) {
                return;
            }

            if (parseInt(sortedActiveDirection, 10) === 1) {
                sortedFieldNode.classList.add(CLASS_SORTED_ASC);
            } else {
                sortedFieldNode.classList.add(CLASS_SORTED_DESC);
            }
        }
    };

    setSortedClass();
    trashedDateTimeRangeNode.addEventListener(
        'ibexa:date-time-range-single:change',
        (event) => {
            const { dates } = event.detail;

            if (dates.length === 2 || dates.length === 0) {
                formSearch.submit();
            }
        },
        false,
    );
    autoSendNodes.forEach((node) => node.addEventListener('change', handleAutoSubmitNodes, false));
    sortableColumns.forEach((column) => column.addEventListener('click', sortTrashItems, false));
    trashedTypeInput.addEventListener('change', toggleDatesSelectVisibility, false);
    creatorInput.addEventListener('keyup', handleTyping, false);
    usersList.addEventListener('click', handleSelectUser, false);
    resetCreatorBtn.addEventListener('click', handleResetUser, false);
    updateTrashForm(trashRestoreCheckboxes);
    enableButtons();
    trashRestoreCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', handleCheckboxChange, false));
})(window, window.document, window.ibexa, window.React, window.ReactDOMClient, window.Translator, window.flatpickr);
