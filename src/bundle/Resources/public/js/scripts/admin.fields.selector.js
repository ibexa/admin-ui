(function (global, doc, ibexa, Translator) {
    const token = doc.querySelector('meta[name="CSRF-Token"]').content;
    const siteaccess = doc.querySelector('meta[name="SiteAccess"]').content;
    const currentLanguageCode = ibexa.adminUiConfig.languages.priority[0];
    const fieldsSelectorNodes = doc.querySelectorAll('.ibexa-fields-selector');
    const DROPDOWN_ROUTINE = {
        CHANGE_ANY_ITEM: 'changeAnyItem',
        CHANGE_ITEM: 'changeItem',
    };
    const ANY_ITEM = {
        id: '*',
        name: Translator.trans(/*@Desc("Any")*/ 'ibexa.fields_selector.any_item', {}, 'forms'),
    };
    let contentTypeGroups = [];
    const getNameForContentType = (names) => {
        const currentLanguageNames = names.value.find(({ _languageCode }) => _languageCode === currentLanguageCode);

        return currentLanguageNames ? currentLanguageNames['#text'] : '';
    };
    const getGroupPattern = (items) => {
        if (items[0] === '*') {
            return '*';
        }

        if (items.length === 1) {
            return items[0];
        }

        return `{${items.join(',')}}`;
    };
    const parsePattern = (pattern) => {
        const output = pattern.split('/').map((part) => {
            if (part === '') {
                return null;
            }

            if (part.startsWith('{') && part.endsWith('}')) {
                const trimmedPart = part.slice(1, -1);

                return trimmedPart.split(',');
            }

            return [part];
        });

        return output.filter(Boolean);
    };
    const loadContentTypeGroups = () => {
        const request = new Request('/api/ibexa/v2/content/typegroups', {
            method: 'GET',
            mode: 'same-origin',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
            },
        });

        return fetch(request)
            .then(ibexa.helpers.request.getJsonFromResponse)
            .then((response) => {
                contentTypeGroups = response.ContentTypeGroupList.ContentTypeGroup.map(({ id, identifier }) => ({
                    id,
                    name: identifier,
                }));

                contentTypeGroups.unshift(ANY_ITEM);

                return contentTypeGroups;
            });
    };
    const loadContentTypes = (params) => {
        const bodyRequest = {
            ViewInput: {
                identifier: 'ContentTypeView',
                ContentTypeQuery: {
                    Query: {},
                },
            },
        };

        if (params.selectedValues.length === 0) {
            return Promise.resolve([]);
        }

        if (params.selectedValues[0] !== '*') {
            bodyRequest.ViewInput.ContentTypeQuery.Query.ContentTypeGroupIdCriterion = params.selectedValues;
        }

        const request = new Request('/api/ibexa/v2/content/types/view', {
            method: 'POST',
            mode: 'same-origin',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/vnd.ibexa.api.ContentTypeView+json',
                'Content-Type': 'application/vnd.ibexa.api.ContentTypeViewInput+json',
                'X-Siteaccess': siteaccess,
                'X-CSRF-Token': token,
            },
            body: JSON.stringify(bodyRequest),
        });

        return fetch(request)
            .then(ibexa.helpers.request.getJsonFromResponse)
            .then((response) => {
                const contentTypes = response.ContentTypeList.ContentType.map(({ identifier, names }) => ({
                    id: identifier,
                    name: getNameForContentType(names),
                }));

                contentTypes.unshift(ANY_ITEM);

                return contentTypes;
            });
    };
    const loadFields = (params) => {
        if (params.selectedValues.length === 0) {
            return Promise.resolve([]);
        }

        return Promise.resolve([
            ANY_ITEM,
            { id: 'name', name: 'Name' },
            { id: 'description', name: 'Description' },
            { id: 'price', name: 'Price' },
        ]);
    };

    class DropdownWithAllItem extends ibexa.core.Dropdown {
        getAllItems() {
            return [...this.itemsListContainer.querySelectorAll('.ibexa-dropdown__item')].slice(1);
        }

        isAnyItem(element) {
            const value = this.getValueFromElement(element, false);

            return value === ANY_ITEM.id;
        }

        fitItems() {
            if (this.dropdownRoutine === DROPDOWN_ROUTINE.CHANGE_ITEM) {
                return;
            }

            super.fitItems();
        }

        onSelectSetSelectionInfoState(element, ...restArgs) {
            if (this.isAnyItem(element)) {
                return;
            }

            super.onSelectSetSelectionInfoState(element, ...restArgs);
        }

        onSelectSetCurrentSelectedValueState(element, selected, ...restArgs) {
            if (this.dropdownRoutine === DROPDOWN_ROUTINE.CHANGE_ITEM) {
                return;
            }

            super.onSelectSetCurrentSelectedValueState(element, selected, ...restArgs);
        }

        getNumberOfSelectedItems() {
            let numberOfSelectedItems = this.getSelectedItems().length;

            if (this.getSelectedItems()[0]?.value === ANY_ITEM.id) {
                numberOfSelectedItems -= 1;
            }

            return numberOfSelectedItems;
        }

        updateAnyItemState() {
            const anyItemElement = this.itemsListContainer.querySelector(`.ibexa-dropdown__item[data-value="${ANY_ITEM.id}"]`);
            const anyItemLabel = anyItemElement.querySelector('.ibexa-dropdown__item-label');
            const anyItemCheckbox = anyItemElement.querySelector('.ibexa-input--checkbox');
            const numberOfSelectedItems = this.getNumberOfSelectedItems();

            this.dropdownRoutine = DROPDOWN_ROUTINE.CHANGE_ANY_ITEM;

            if (numberOfSelectedItems === 0) {
                anyItemLabel.textContent = ANY_ITEM.name;
            } else {
                anyItemLabel.textContent = Translator.trans(
                    /*@Desc("Any (%count% selected)")*/ 'ibexa.fields_selector.any_item_selected',
                    {
                        count: numberOfSelectedItems,
                    },
                    'forms',
                );
            }

            if (numberOfSelectedItems === 0) {
                anyItemCheckbox.indeterminate = false;
            } else if (numberOfSelectedItems === this.getAllItems().length) {
                anyItemCheckbox.indeterminate = false;
                this.onSelect(anyItemElement, true);
            } else {
                this.onSelect(anyItemElement, false);
                anyItemCheckbox.indeterminate = true;
            }

            this.dropdownRoutine = null;
        }

        deselectOption(...args) {
            super.deselectOption(...args);

            this.updateAnyItemState();
        }

        onSelect(element, selected, ...restArgs) {
            super.onSelect(element, selected, ...restArgs);

            if (this.isAnyItem(element)) {
                if (this.dropdownRoutine === DROPDOWN_ROUTINE.CHANGE_ANY_ITEM) {
                    return;
                }

                const allItems = this.getAllItems();

                this.dropdownRoutine = DROPDOWN_ROUTINE.CHANGE_ITEM;

                allItems.forEach((item) => {
                    const value = this.getValueFromElement(item);
                    const { selected: itemSelected } = this.sourceInput.querySelector(`[value=${value}]`);

                    if (selected && !itemSelected) {
                        this.onSelect(item, true, ...restArgs);
                    } else if (!selected && itemSelected) {
                        this.onSelect(item, false, ...restArgs);
                    }
                });

                this.dropdownRoutine = null;
                this.fitItems();
                this.fireValueChangedEvent();

                return;
            }

            this.updateAnyItemState();
        }
    }

    const initFieldsSelector = (node) => {
        const sourceInput = node.querySelector('.ibexa-multistep-selector__source .ibexa-input');
        const multistepSelectorInstance = new ibexa.core.MultistepSelector(
            node,
            [
                {
                    id: 'ct-group',
                    loadData: loadContentTypeGroups,
                },
                {
                    id: 'ct',
                    loadData: loadContentTypes,
                },
                {
                    id: 'field',
                    loadData: loadFields,
                },
            ],
            {
                customDropdown: DropdownWithAllItem,
                initialValue: parsePattern(sourceInput.value),
                callback: (values) => {
                    const output = values.map((item) => getGroupPattern(item)).join('/');

                    sourceInput.value = output;
                },
            },
        );

        multistepSelectorInstance.init();
    };

    fieldsSelectorNodes.forEach((node) => {
        initFieldsSelector(node);
    });
})(window, window.document, window.ibexa, window.Translator);
