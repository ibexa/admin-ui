(function (global, doc, ibexa, bootstrap) {
    class AutocompleteInput {
        constructor(config = {}) {
            this.container = config.container.classList.contains('ibexa-autocomplete-input')
                ? config.container
                : config.container.querySelector('.ibexa-autocomplete-input');

            if (!this.container) {
                throw new Error('No valid container provided');
            }

            this.sourceInput = this.container.querySelector(config.selectorSource ?? '.ibexa-autocomplete-input__source .ibexa-input');
            this.inputField = this.container.querySelector('.ibexa-autocomplete-input__input');
            this.anchorItems = this.container.querySelector('.ibexa-autocomplete-input__anchor-items');
            this.itemsContainer = this.container.querySelector('.ibexa-autocomplete-input__items-wrapper');
            this.itemsListContainer = this.itemsContainer.querySelector('.ibexa-autocomplete-input__items');
            this.getData = config.getData.bind(null, this);

            this.itemsPopoverContent = this.itemsPopoverContent.bind(this);
            this.handleTextInput = this.handleTextInput.bind(this);
            this.onInteractionOutside = this.onInteractionOutside.bind(this);
            this.onPopoverShow = this.onPopoverShow.bind(this);
            this.onPopoverHide = this.onPopoverHide.bind(this);
            this.handleClearInput = this.handleClearInput.bind(this);

            ibexa.helpers.objectInstances.setInstance(this.container, this);
        }

        onInteractionOutside(event) {
            if (this.itemsPopover.tip.contains(event.target)) {
                return;
            }

            this.itemsPopover.hide();
        }

        onPopoverShow() {
            doc.body.addEventListener('click', this.onInteractionOutside, false);
        }

        onPopoverHide() {
            this.isPopoverVisible = false;

            doc.body.removeEventListener('click', this.onInteractionOutside, false);
        }

        getItemsContainerHeight() {
            const DROPDOWN_MARGIN = 32;
            const documentElementHeight = global.innerHeight;
            const { top, bottom } = this.inputField.getBoundingClientRect();
            const topHeight = top;
            const bottomHeight = documentElementHeight - bottom;

            return Math.max(topHeight, bottomHeight) - DROPDOWN_MARGIN;
        }

        itemsPopoverContent() {
            const { width } = this.inputField.getBoundingClientRect();

            this.itemsContainer.style['max-height'] = `${this.getItemsContainerHeight()}px`;
            this.itemsContainer.style.width = `${width}px`;

            return this.itemsContainer;
        }

        togglePopoverVisibility(isVisible = false) {
            if (this.isPopoverVisible !== isVisible) {
                const toggleMethod = isVisible ? 'show' : 'hide';

                this.isPopoverVisible = isVisible;

                this.itemsPopover[toggleMethod]();
            }
        }

        renderTemplateData(data, template) {
            const templateRendered = template
                .replace('{{ value }}', data.value)
                .replace('{{ label }}', data.label)
                .replace('{{ selected_label }}', data.selectedLabel);

            return templateRendered;
        }

        selectItem(item) {
            const { value, selectedLabel } = item.dataset;

            this.sourceInput.value = value;
            this.inputField.value = selectedLabel;

            this.itemsPopover.hide();
        }

        bindItemEvents(item) {
            item.addEventListener('click', () => this.selectItem(item), false);
        }

        updatePopoverContent(data) {
            const { template } = this.itemsListContainer.dataset;

            this.itemsListContainer.innerHTML = '';

            const fragment = doc.createDocumentFragment();

            data.forEach((dataItem) => {
                const container = doc.createElement('div');
                const templateRendered = this.renderTemplateData(dataItem, template);

                container.insertAdjacentHTML('beforeend', templateRendered);

                const newItem = container.firstChild;

                this.bindItemEvents(newItem);

                fragment.append(newItem);
            });

            this.itemsListContainer.append(fragment);
        }

        handleClearInput() {
            if (this.inputField.value === '') {
                this.sourceInput.value = '';

                this.itemsPopover.hide();
            }
        }

        handleTextInput(event) {
            this.sourceInput.value = '';

            this.getData(event.target.value);
        }

        init() {
            this.itemsPopover = new bootstrap.Popover(
                this.anchorItems,
                {
                    html: true,
                    placement: 'bottom',
                    customClass: 'ibexa-autocomplete-input-popover',
                    content: this.itemsPopoverContent,
                    container: 'body',
                },
                { dropdown: this },
            );
            this.itemsPopover._element.removeAttribute('data-bs-original-title');
            this.itemsPopover._element.removeAttribute('title');
            this.itemsPopover._element.addEventListener('shown.bs.popover', this.onPopoverShow);
            this.itemsPopover._element.addEventListener('hidden.bs.popover', this.onPopoverHide);

            this.inputField.addEventListener('keyup', this.handleTextInput, false);
            this.inputField.addEventListener('input', this.handleClearInput, false);
        }
    }

    ibexa.addConfig('core.AutocompleteInput', AutocompleteInput);
})(window, window.document, window.ibexa, window.bootstrap);
