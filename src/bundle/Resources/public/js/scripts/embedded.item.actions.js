(function (global, doc, ibexa, Routing, Translator, Popper) {
    const MIN_ITEMS_NUMBER_TO_SHOW_SEARCH = 10;
    const MENU_PROPS = {
        placement: 'bottom-start',
        fallbackPlacements: ['top-end', 'top-start'],
    };
    const token = document.querySelector('meta[name="CSRF-Token"]').content;
    const siteaccess = document.querySelector('meta[name="SiteAccess"]').content;
    const metaLanguageCode = document.querySelector('meta[name="LanguageCode"]')?.content;
    const previewLanguageCode = metaLanguageCode ?? ibexa.adminUiConfig.languages.priority[0];
    const adminUiLanguages = ibexa.adminUiConfig.languages.mappings;
    const emdedItemsUpdateChannel = new BroadcastChannel('ibexa-emded-item-live-update');
    const editEmbeddedItemForm = doc.querySelector('[name="embedded_item_edit"]');
    const actionsMenuTriggerBtns = doc.querySelectorAll('.ibexa-embedded-item-actions__menu-trigger-btn');
    const updateNode = ({ node, value, isMiddleEllipsis }) => {
        if (!isMiddleEllipsis) {
            node.textContent = value;

            return;
        }

        const middleEllipsisNode = node.querySelector('.ibexa-middle-ellipsis');
        const middleEllipsisNameStartNode = node.querySelector(
            '.ibexa-middle-ellipsis__name--start .ibexa-middle-ellipsis__name-ellipsized',
        );
        const middleEllipsisNameEndNode = node.querySelector('.ibexa-middle-ellipsis__name--end .ibexa-middle-ellipsis__name-ellipsized');

        middleEllipsisNode.title = value;
        middleEllipsisNameStartNode.textContent = value;
        middleEllipsisNameEndNode.textContent = value;

        ibexa.helpers.ellipsis.middle.parse(node);
    };
    const updateNodes = async (contentId) => {
        const nodesToUpdate = doc.querySelectorAll(`[data-ibexa-update-content-id="${contentId}"]`);

        if (!nodesToUpdate) {
            return;
        }

        const contentData = await loadContentData(contentId);

        [...nodesToUpdate].forEach((nodeToUpdate) => {
            let sourceValue = contentData;
            const { ibexaUpdateSourceDataPath } = nodeToUpdate.dataset;
            const updateSourceDataPathArray = ibexaUpdateSourceDataPath.split('.');

            for (const pathLevelIndex in updateSourceDataPathArray) {
                const pathLevel = updateSourceDataPathArray[pathLevelIndex];

                sourceValue = sourceValue[pathLevel];
            }

            if (sourceValue) {
                const { ibexaUpdateMiddleEllipsis } = nodeToUpdate.dataset;

                updateNode({
                    node: nodeToUpdate,
                    value: sourceValue,
                    isMiddleEllipsis: ibexaUpdateMiddleEllipsis,
                });
            }
        });
    };
    const loadContentData = async (contentId) => {
        try {
            const loadContentRequest = new Request(`/api/ibexa/v2/content/objects/${contentId}`, {
                method: 'GET',
                headers: {
                    Accept: 'application/vnd.ibexa.api.Content+json',
                    'X-Siteaccess': siteaccess,
                    'X-CSRF-Token': token,
                },
                mode: 'same-origin',
                credentials: 'same-origin',
            });
            const response = await fetch(loadContentRequest);

            return ibexa.helpers.request.getJsonFromResponse(response);
        } catch (error) {
            ibexa.helpers.notification.showErrorNotification(error);
        }
    };
    const editContent = ({ contentId, locationId, languageCode }) => {
        if (!contentId || !locationId || !languageCode) {
            return;
        }

        const contentInfoInput = editEmbeddedItemForm.querySelector('[name="embedded_item_edit[content_info]"]');
        const locationInput = editEmbeddedItemForm.querySelector('[name="embedded_item_edit[location]"]');
        const languageInput = editEmbeddedItemForm.querySelector(`[name="embedded_item_edit[language]"][value="${languageCode}"]`);

        contentInfoInput.value = contentId;
        locationInput.value = locationId;
        languageInput.click();

        editEmbeddedItemForm.submit();
    };
    const generateGoToActionItem = ({ contentId, locationId, productCode }) => {
        const href = productCode
            ? Routing.generate('ibexa.product_catalog.product.view', {
                  productCode,
                  languageCode: previewLanguageCode,
              })
            : Routing.generate('ibexa.content.translation.view', {
                  contentId,
                  locationId,
                  languageCode: previewLanguageCode,
              });

        return {
            label: Translator.trans(/*@Desc("Go to content")*/ 'embedded_items.action.go_to_label', {}, 'ibexa_content'),
            href,
        };
    };
    const generateEditActionItem = ({ contentId, locationId, productCode, languages }) => {
        if (languages.length > 1) {
            return {
                label: Translator.trans(/*@Desc("Edit")*/ 'embedded_items.action.edit', {}, 'ibexa_content'),
                branch: {
                    hasSearch: languages.length >= MIN_ITEMS_NUMBER_TO_SHOW_SEARCH,
                    groups: [
                        {
                            id: 'edit-group',
                            items: languages.map(({ languageCode, name }) => {
                                const languageEditAction = productCode
                                    ? {
                                          href: Routing.generate('ibexa.product_catalog.product.edit', {
                                              productCode,
                                              languageCode,
                                          }),
                                      }
                                    : {
                                          onClick: () => editContent({ contentId, locationId, languageCode }),
                                      };

                                return {
                                    label: name,
                                    ...languageEditAction,
                                };
                            }),
                        },
                    ],
                },
            };
        }

        const editAction = productCode
            ? {
                  href: Routing.generate('ibexa.product_catalog.product.edit', {
                      productCode,
                      languageCode: languages[0].languageCode,
                  }),
              }
            : {
                  onClick: () =>
                      editContent({
                          contentId,
                          locationId,
                          languageCode: languages[0].languageCode,
                      }),
              };

        return {
            label: Translator.trans(/*@Desc("Edit")*/ 'embedded_items.action.edit', {}, 'ibexa_content'),
            ...editAction,
        };
    };
    const generateMenuTreeItems = ({ contentId, locationId, productCode, languages }) => {
        const goToItem = generateGoToActionItem({ contentId, locationId, productCode });
        const editItem = generateEditActionItem({ contentId, locationId, productCode, languages });

        return {
            groups: [
                {
                    id: 'default',
                    items: [goToItem, editItem],
                },
            ],
        };
    };
    const getLanguagesData = async ({ contentId, initialFunc = () => {}, callbackFunc = () => {} }) => {
        try {
            initialFunc();

            const url = window.Routing.generate('ibexa.permission.limitation.language.content_edit', { contentInfoId: contentId });
            const request = new Request(url, {
                method: 'GET',
                headers: { 'X-CSRF-Token': token },
                mode: 'same-origin',
                credentials: 'same-origin',
            });
            const response = await fetch(request);
            const data = await ibexa.helpers.request.getJsonFromResponse(response);

            callbackFunc();

            return data.filter((language) => language.hasAccess);
        } catch (error) {
            ibexa.helpers.notification.showErrorNotification(error);
        }
    };
    const getMenuData = ({ container, event }) => {
        const { contentId, locationId, productCode, languageCodes = [] } = container ? container.dataset : event.detail;
        const parsedLanguageCodes = typeof languageCodes === 'string' ? JSON.parse(languageCodes) : languageCodes;
        const languages = parsedLanguageCodes.map((languageCode) => ({
            languageCode,
            name: adminUiLanguages[languageCode].name,
        }));

        return {
            contentId: parseInt(contentId, 10),
            locationId: parseInt(locationId, 10),
            productCode,
            languages,
        };
    };
    const createMenu = async ({ triggerElement, container, contentId, locationId, productCode, languages }) => {
        triggerElement.dataset.isMenuAttached = 1;

        const mainContainer = container.closest('.ibexa-embedded-item-actions');
        const menuLoader = mainContainer.querySelector('.ibexa-embedded-item-actions__loader-container');
        const askForLanguagesData = Object.keys(languages).length !== 1;
        const languagesData = askForLanguagesData
            ? await getLanguagesData({
                  contentId,
                  initialFunc: showLoader.bind(null, { triggerElement, menuLoader }),
                  callbackFunc: hideLoader.bind(null, { menuLoader }),
              })
            : languages;
        const menuItems = generateMenuTreeItems({ contentId, locationId, productCode, languages: languagesData });
        const menuInstance = new ibexa.core.MultilevelPopupMenu({
            container,
            triggerElement,
        });

        menuInstance.init();
        menuInstance.generateMenu({
            triggerElement,
            ...MENU_PROPS,
            ...menuItems,
        });

        triggerElement.click();
    };
    const showLoader = ({ triggerElement, menuLoader }) => {
        Popper.createPopper(triggerElement, menuLoader, {
            placement: MENU_PROPS.placement,
            modifiers: [
                {
                    name: 'flip',
                    enabled: true,
                    options: {
                        fallbackPlacements: MENU_PROPS.fallbackPlacements,
                    },
                },
            ],
        });

        menuLoader.classList.remove('ibexa-popup-menu--hidden');
    };
    const hideLoader = ({ menuLoader }) => {
        menuLoader.classList.add('ibexa-popup-menu--hidden');
    };

    actionsMenuTriggerBtns.forEach((actionsMenuTriggerBtn) => {
        actionsMenuTriggerBtn.addEventListener(
            'click',
            (event) => {
                const isMenuAttached = !!parseInt(actionsMenuTriggerBtn.dataset.isMenuAttached, 10);

                if (!isMenuAttached) {
                    event.preventDefault();

                    const menuMainContainer = actionsMenuTriggerBtn.closest('.ibexa-embedded-item-actions');
                    const menuContainer = menuMainContainer.querySelector('.ibexa-embedded-item-actions__menu');
                    const menuData = getMenuData({ container: menuContainer });

                    createMenu({
                        triggerElement: actionsMenuTriggerBtn,
                        container: menuContainer,
                        ...menuData,
                    });
                }
            },
            false,
        );
    });

    doc.body.addEventListener('ibexa-embedded-item:create-dynamic-menu', (event) => {
        const menuData = getMenuData({ event });
        const { menuTriggerElement, menuContainer } = event.detail;

        menuTriggerElement.addEventListener(
            'click',
            () => {
                const isMenuAttached = !!parseInt(menuTriggerElement.dataset.isMenuAttached, 10);

                if (!isMenuAttached) {
                    event.preventDefault();

                    createMenu({
                        triggerElement: menuTriggerElement,
                        container: menuContainer,
                        ...menuData,
                    });
                }
            },
            false,
        );
    });

    emdedItemsUpdateChannel.addEventListener('message', (event) => {
        updateNodes(event.data.contentId);
    });
})(window, document, window.ibexa, window.Routing, window.Translator, window.Popper);
