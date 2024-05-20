import * as middleEllipsisHelper from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/middle.ellipsis';

(function (global, doc, ibexa) {
    class TagViewSelect {
        constructor(config) {
            this.inputSelector = config.inputSelector || 'input';
            this.container = config.container || config.fieldContainer.querySelector('.ibexa-tag-view-select');

            if (!this.container) {
                throw new Error("Field Container doesn't exist!"); // eslint-disable-line quotes
            }

            this.listContainer = this.container.querySelector('.ibexa-tag-view-select__selected-list');
            this.inputField = this.container.querySelector(this.inputSelector);
            this.selectBtn = this.container.querySelector('.ibexa-tag-view-select__btn-select-path');
            this.isSingleSelect = this.container.dataset.isSingleSelect === '1';
            this.canBeEmpty = this.container.dataset.canBeEmpty === '1';
            this.inputSeparator = config.seperator || ',';
            this.selectedItemTemplate = this.listContainer.dataset.template;

            this.addItems = this.addItems.bind(this);
            this.addItem = this.addItem.bind(this);
            this.removeItems = this.removeItems.bind(this);
            this.removeItem = this.removeItem.bind(this);
            this.toggleDeleteButtons = this.toggleDeleteButtons.bind(this);
            this.attachDeleteEvents = this.attachDeleteEvents.bind(this);
            this.adjustButtonLabel = this.adjustButtonLabel.bind(this);

            middleEllipsisHelper.parse();
            this.attachDeleteEvents();

            this.disabledObserver = new MutationObserver((mutationsList) => {
                const isDisabled = mutationsList[0].target.hasAttribute('disabled');

                this.toggleDisabledState(isDisabled);
            });

            this.disabledObserver.observe(this.container, {
                attributeFilter: ['disabled'],
                attributeOldValue: true,
            });
        }

        toggleDisabledState(isDisabled) {
            const removeBtns = this.listContainer.querySelectorAll('.ibexa-tag-view-select__selected-item-tag-remove-btn');

            removeBtns.forEach((btn) => btn.toggleAttribute('disabled', isDisabled));
            this.inputField.toggleAttribute('disabled', isDisabled);
            this.selectBtn.toggleAttribute('disabled', isDisabled);
        }

        addItems(items, forceRecreate) {
            if (this.isSingleSelect) {
                this.inputField.value = items[0]?.id ?? '';
                this.listContainer.textContent = '';
            } else {
                const newItemsIds = items.map((item) => item.id);

                if (this.inputField.value !== '' && !forceRecreate) {
                    newItemsIds.unshift(this.inputField.value);
                }

                this.inputField.value = newItemsIds.join(this.inputSeparator);
            }

            if (forceRecreate) {
                this.listContainer.textContent = '';
            }

            items.forEach((item) => {
                const { id, name } = item;
                const itemTemplate = this.selectedItemTemplate.replace('{{ id }}', id).replaceAll('{{ name }}', name);
                const range = doc.createRange();
                const itemHtmlWidget = range.createContextualFragment(itemTemplate);
                const deleteButton = itemHtmlWidget.querySelector('.ibexa-tag-view-select__selected-item-tag-remove-btn');

                deleteButton.toggleAttribute('disabled', false);
                deleteButton.addEventListener('click', () => this.removeItem(String(id)), false);
                this.listContainer.append(itemHtmlWidget);
            });

            this.inputField.dispatchEvent(new Event('change'));
            middleEllipsisHelper.parse();
            this.toggleDeleteButtons();
            this.adjustButtonLabel();
        }

        addItem(id, name, forceRecreate) {
            this.addItems([{ id, name }], forceRecreate);
        }

        removeItems(itemsIds) {
            const prevSelectedIds = this.inputField.value.split(this.inputSeparator);
            const nextSelectedIds = prevSelectedIds.filter((savedId) => !itemsIds.includes(savedId));
            this.inputField.value = nextSelectedIds.join(this.inputSeparator);

            itemsIds.forEach((itemId) => {
                this.listContainer.querySelector(`[data-id="${itemId}"]`).remove();
            });

            this.inputField.dispatchEvent(new Event('change'));
            this.toggleDeleteButtons();
            this.adjustButtonLabel();
        }

        removeItem(id) {
            this.removeItems([id]);
        }

        toggleDeleteButtons() {
            const selectedItems = [...this.listContainer.querySelectorAll('[data-id]')];
            const hideDeleteButtons = !this.canBeEmpty && selectedItems.length === 1;

            selectedItems.forEach((selectedItem) =>
                selectedItem
                    .querySelector('.ibexa-tag-view-select__selected-item-tag-remove-btn')
                    .toggleAttribute('hidden', hideDeleteButtons),
            );
        }

        attachDeleteEvents() {
            const selectedItems = [...this.listContainer.querySelectorAll('[data-id]')];

            selectedItems.forEach((selectedItem) => {
                const { id } = selectedItem.dataset;
                const deleteButton = selectedItem.querySelector('.ibexa-tag-view-select__selected-item-tag-remove-btn');

                deleteButton.addEventListener('click', () => this.removeItem(String(id)), false);
            });
        }

        adjustButtonLabel() {
            const selectedItems = [...this.listContainer.querySelectorAll('[data-id]')];
            const buttonLabelSelect = this.container.querySelector('.ibexa-tag-view-select__btn-label--select');
            const buttonLabelChange = this.container.querySelector('.ibexa-tag-view-select__btn-label--change');
            const hasButtonChangeLabel = this.isSingleSelect && selectedItems.length > 0;

            buttonLabelSelect.toggleAttribute('hidden', hasButtonChangeLabel);
            buttonLabelChange.toggleAttribute('hidden', !hasButtonChangeLabel);
        }
    }

    ibexa.addConfig('core.TagViewSelect', TagViewSelect);
})(window, window.document, window.ibexa);
