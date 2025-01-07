(function (global, doc, ibexa, React, ReactDOM, Translator) {
    const CLASS_FIELD_SINGLE = 'ibexa-field-edit--ezobjectrelation';
    const SELECTOR_FIELD_MULTIPLE = '.ibexa-field-edit--ezobjectrelationlist';
    const SELECTOR_FIELD_SINGLE = '.ibexa-field-edit--ezobjectrelation';
    const SELECTOR_INPUT = '.ibexa-data-source__input';
    const SELECTOR_BTN_ADD = '.ibexa-relations__table-action--create';
    const SELECTOR_ROW = '.ibexa-relations__item';
    const EVENT_CUSTOM = 'validateInput';

    class EzObjectRelationListValidator extends ibexa.BaseFieldValidator {
        /**
         * Validates the input
         *
         * @method validateInput
         * @param {Event} event
         * @returns {Object}
         * @memberof EzObjectRelationListValidator
         */
        validateInput({ currentTarget }) {
            const isRequired = currentTarget.required;
            const isEmpty = !currentTarget.value.length;
            const hasCorrectValues = currentTarget.value.split(',').every((id) => !isNaN(parseInt(id, 10)));
            const fieldContainer = currentTarget.closest(SELECTOR_FIELD_MULTIPLE) || currentTarget.closest(SELECTOR_FIELD_SINGLE);
            const label = fieldContainer.querySelector('.ibexa-field-edit__label').innerHTML;
            const result = { isError: false };

            if (isRequired && isEmpty) {
                result.isError = true;
                result.errorMessage = ibexa.errors.emptyField.replace('{fieldName}', label);
            } else if (!isEmpty && !hasCorrectValues) {
                result.isError = true;
                result.errorMessage = ibexa.errors.invalidValue.replace('{fieldName}', label);
            }

            return result;
        }
    }

    [...doc.querySelectorAll(SELECTOR_FIELD_MULTIPLE), ...doc.querySelectorAll(SELECTOR_FIELD_SINGLE)].forEach((fieldContainer) => {
        const validator = new EzObjectRelationListValidator({
            classInvalid: 'is-invalid',
            fieldContainer,
            eventsMap: [
                {
                    selector: SELECTOR_INPUT,
                    eventName: 'blur',
                    callback: 'validateInput',
                    errorNodeSelectors: ['.ibexa-form-error'],
                },
                {
                    isValueValidator: false,
                    selector: SELECTOR_INPUT,
                    eventName: EVENT_CUSTOM,
                    callback: 'validateInput',
                    errorNodeSelectors: ['.ibexa-form-error'],
                },
            ],
        });
        const udwContainer = doc.getElementById('react-udw');
        const sourceInput = fieldContainer.querySelector(SELECTOR_INPUT);
        const relationsContainer = fieldContainer.querySelector('.ibexa-relations__list');
        const relationsWrapper = fieldContainer.querySelector('.ibexa-relations__wrapper');
        const relationsCTA = fieldContainer.querySelector('.ibexa-relations__cta');
        const addBtn = fieldContainer.querySelector(SELECTOR_BTN_ADD);
        const trashBtn = fieldContainer.querySelector('.ibexa-relations__table-action--remove');
        const isSingle = fieldContainer.classList.contains(CLASS_FIELD_SINGLE);
        const selectedItemsLimit = isSingle ? 1 : parseInt(relationsContainer.dataset.limit, 10);
        const relationsTable = relationsWrapper.querySelector('.ibexa-table');
        const startingLocationId =
            relationsContainer.dataset.defaultLocation !== '0' ? parseInt(relationsContainer.dataset.defaultLocation, 10) : null;
        let udwRoot = null;
        const closeUDW = () => udwRoot.unmount();
        const renderRows = (items) => {
            items.forEach((item, index) => {
                relationsContainer.insertAdjacentHTML('beforeend', renderRow(item, index));

                const { escapeHTML } = ibexa.helpers.text;
                const itemNodes = relationsContainer.querySelectorAll('.ibexa-relations__item');
                const itemNode = itemNodes[itemNodes.length - 1];
                const contentId = escapeHTML(item.ContentInfo.Content._id);
                const locationId = item.id;
                const { VersionInfo } = item.ContentInfo.Content.CurrentVersion.Version;
                const currentVersionNo = VersionInfo.versionNo;
                const languageCodes = VersionInfo.VersionTranslationInfo.Language.map((language) => language.languageCode);
                const itemActionsMenuContainer = itemNode.querySelector('.ibexa-embedded-item-actions__menu');
                const itemActionsTriggerElement = itemNode.querySelector('.ibexa-embedded-item-actions__menu-trigger-btn');
                const itemNodeNameCell = itemNode.querySelector('.ibexa-relations__item-name');

                itemNode.dataset.contentId = contentId;
                itemNode.dataset.locationId = locationId;
                itemNode.querySelector('.ibexa-relations__table-action--remove-item').addEventListener('click', removeItem, false);

                itemNodeNameCell.dataset.ibexaUpdateContentId = contentId;
                itemNodeNameCell.dataset.ibexaUpdateSourceDataPath = 'Content.Name';

                doc.body.dispatchEvent(
                    new CustomEvent('ibexa-embedded-item:create-dynamic-menu', {
                        detail: {
                            contentId,
                            locationId,
                            languageCodes,
                            versionNo: currentVersionNo,
                            menuTriggerElement: itemActionsTriggerElement,
                            menuContainer: itemActionsMenuContainer,
                        },
                    }),
                );
            });

            ibexa.helpers.tooltips.parse();
        };
        const updateInputValue = (items) => {
            sourceInput.value = items.join();
            sourceInput.dispatchEvent(new CustomEvent(EVENT_CUSTOM));
        };
        const onConfirm = (items) => {
            const itemsWithoutDuplicate = excludeDuplicatedItems(items);

            renderRows(itemsWithoutDuplicate);
            attachRowsEventHandlers();

            selectedItems = [...selectedItems, ...itemsWithoutDuplicate.map((item) => item.ContentInfo.Content._id)];

            updateInputValue(selectedItems);
            closeUDW();
            updateFieldState();
            updateAddBtnState();
            relationsTable.dispatchEvent(new CustomEvent('ibexa-refresh-main-table-checkbox'));
        };
        const openUDW = (event) => {
            event.preventDefault();
            const selectedItemsRow = fieldContainer.querySelectorAll(SELECTOR_ROW);
            const config = JSON.parse(event.currentTarget.dataset.udwConfig);
            const limit = parseInt(event.currentTarget.dataset.limit, 10);
            const selectedLocations = [...selectedItemsRow].reduce((locationsIds, selectedItemRow) => {
                const { locationId } = selectedItemRow.dataset;
                const parsedLocationId = parseInt(locationId, 10);

                return isNaN(parsedLocationId) ? locationsIds : [...locationsIds, parsedLocationId];
            }, []);
            const title =
                limit === 1
                    ? Translator.trans(
                          /*@Desc("Select a Content item")*/ 'ezobjectrelationlist.title.single',
                          {},
                          'ibexa_universal_discovery_widget',
                      )
                    : Translator.trans(
                          /*@Desc("Select Content item(s)")*/ 'ezobjectrelationlist.title.multi',
                          {},
                          'ibexa_universal_discovery_widget',
                      );

            udwRoot = ReactDOM.createRoot(udwContainer);
            udwRoot.render(
                React.createElement(ibexa.modules.UniversalDiscovery, {
                    onConfirm,
                    onCancel: closeUDW,
                    title,
                    startingLocationId,
                    selectedLocations,
                    ...config,
                    multiple: isSingle ? false : selectedItemsLimit !== 1,
                    multipleItemsLimit: selectedItemsLimit > 1 ? selectedItemsLimit - selectedItems.length : selectedItemsLimit,
                }),
            );
        };
        const excludeDuplicatedItems = (items) => items.filter((item) => !selectedItems.includes(item.ContentInfo.Content._id));
        const renderRow = (item, index) => {
            const { escapeHTML } = ibexa.helpers.text;
            const { formatShortDateTime } = ibexa.helpers.timezone;
            const contentTypeName = ibexa.helpers.contentType.getContentTypeName(item.ContentInfo.Content.ContentTypeInfo.identifier);
            const contentName = escapeHTML(item.ContentInfo.Content.TranslatedName);
            const contentId = escapeHTML(item.ContentInfo.Content._id);
            const { rowTemplate } = relationsWrapper.dataset;

            return rowTemplate
                .replace('{{ content_id }}', contentId)
                .replace('{{ content_name }}', contentName)
                .replace('{{ content_type_name }}', contentTypeName)
                .replace('{{ published_date }}', formatShortDateTime(item.ContentInfo.Content.publishedDate))
                .replace('{{ order }}', selectedItems.length + index + 1);
        };
        const updateFieldState = () => {
            const tableHideMethod = selectedItems.length ? 'removeAttribute' : 'setAttribute';
            const ctaHideMethod = selectedItems.length ? 'setAttribute' : 'removeAttribute';

            relationsTable[tableHideMethod]('hidden', true);

            if (trashBtn) {
                trashBtn[tableHideMethod]('hidden', true);
            }

            if (addBtn) {
                addBtn[tableHideMethod]('hidden', true);
            }

            relationsCTA[ctaHideMethod]('hidden', true);
        };
        const updateAddBtnState = () => {
            if (!addBtn) {
                return;
            }

            const forceDisabled = addBtn.classList.contains('ibexa-relations__table-action--disabled');
            const methodName =
                !forceDisabled && (!selectedItemsLimit || selectedItems.length < selectedItemsLimit) ? 'removeAttribute' : 'setAttribute';

            addBtn[methodName]('disabled', true);
        };
        const updateTrashBtnState = (event) => {
            if (
                !trashBtn ||
                ((!event.target.hasAttribute('type') || event.target.type !== 'checkbox') && event.currentTarget !== trashBtn)
            ) {
                return;
            }

            const anySelected = findCheckboxes().some((item) => item.checked === true);
            const methodName = anySelected ? 'removeAttribute' : 'setAttribute';

            trashBtn[methodName]('disabled', true);
        };
        const removeItems = (event) => {
            event.preventDefault();

            const removedItems = [];

            relationsContainer.querySelectorAll('input:checked').forEach((input) => {
                removedItems.push(parseInt(input.value, 10));

                input.closest('tr').remove();
            });

            selectedItems = selectedItems.filter((item) => !removedItems.includes(item));

            updateInputValue(selectedItems);
            updateFieldState();
            updateAddBtnState();
            relationsTable.dispatchEvent(new CustomEvent('ibexa-refresh-main-table-checkbox'));
        };
        const removeItem = (event) => {
            const row = event.target.closest('.ibexa-relations__item');
            const contentId = parseInt(row.dataset.contentId, 10);

            row.remove();

            selectedItems = selectedItems.filter((item) => contentId !== item);

            updateInputValue(selectedItems);
            updateFieldState();
            updateAddBtnState();
            relationsTable.dispatchEvent(new CustomEvent('ibexa-refresh-main-table-checkbox'));
        };
        const findOrderInputs = () => {
            return [...relationsContainer.querySelectorAll('.ibexa-relations__order-input')];
        };
        const findCheckboxes = () => {
            return [...relationsContainer.querySelectorAll('[type="checkbox"]')];
        };
        const attachRowsEventHandlers = () => {
            const isFirefox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;

            findOrderInputs().forEach((item) => {
                item.addEventListener('blur', updateSelectedItemsOrder, false);

                if (isFirefox) {
                    item.addEventListener('change', focusOnElement, false);
                }
            });
        };
        const focusOnElement = (event) => {
            if (doc.activeElement !== event.target) {
                event.target.focus();
            }
        };
        const emptyRelationsContainer = () => {
            while (relationsContainer.lastChild) {
                relationsContainer.removeChild(relationsContainer.lastChild);
            }
        };
        const updateSelectedItemsOrder = (event) => {
            event.preventDefault();

            const inputs = findOrderInputs().reduce((total, input) => {
                return [
                    ...total,
                    {
                        order: parseInt(input.value, 10),
                        row: input.closest(SELECTOR_ROW),
                    },
                ];
            }, []);

            inputs.sort((a, b) => a.order - b.order);

            const fragment = inputs.reduce((frag, item) => {
                frag.appendChild(item.row);

                return frag;
            }, doc.createDocumentFragment());

            emptyRelationsContainer();
            relationsContainer.appendChild(fragment);
            attachRowsEventHandlers();

            selectedItems = inputs.map((item) => parseInt(item.row.dataset.contentId, 10));
            updateInputValue(selectedItems);
        };
        let selectedItems = [...fieldContainer.querySelectorAll(SELECTOR_ROW)].map((row) => parseInt(row.dataset.contentId, 10));

        updateAddBtnState();
        attachRowsEventHandlers();

        [...fieldContainer.querySelectorAll(SELECTOR_BTN_ADD), ...fieldContainer.querySelectorAll('.ibexa-relations__cta-btn')].forEach(
            (btn) => btn.addEventListener('click', openUDW, false),
        );

        [...fieldContainer.querySelectorAll('.ibexa-relations__table-action--remove-item')].forEach((btn) =>
            btn.addEventListener('click', removeItem, false),
        );

        if (trashBtn) {
            trashBtn.addEventListener('click', removeItems, false);
            trashBtn.addEventListener('click', updateTrashBtnState, false);
        }

        relationsContainer.addEventListener('change', updateTrashBtnState, false);

        validator.init();

        ibexa.addConfig('fieldTypeValidators', [validator], true);
    });
})(window, window.document, window.ibexa, window.React, window.ReactDOM, window.Translator);
