(function (global, doc, ibexa) {
    const SELECTOR_PLACEHOLDER = '.ibexa-draggable__placeholder';
    const SELECTOR_PREVENT_DRAG = '.ibexa-draggable__prevent-drag';
    const TIMEOUT_REMOVE_HIGHLIGHT = 3000;

    class Draggable {
        constructor(config) {
            this.draggedItem = null;
            this.placeholder = null;
            this.onDragOverTimeout = null;
            this.itemsContainer = config.itemsContainer;
            this.selectorItem = config.selectorItem;
            this.selectorPlaceholder = config.selectorPlaceholder || SELECTOR_PLACEHOLDER;
            this.selectorPreventDrag = config.selectorPreventDrag || SELECTOR_PREVENT_DRAG;
            this.selectorItemContent = `${this.selectorItem}__content`;
            this.itemMainClass = this.selectorItem.slice(1);
            this.highlightClass = `${this.itemMainClass}--highlighted`;
            this.draggingOutClass = `${this.itemMainClass}--is-dragging-out`;
            this.removingClass = `${this.itemMainClass}--is-removing`;
            this.removedClass = `${this.itemMainClass}--removed`;

            this.onDragStart = this.onDragStart.bind(this);
            this.onDragEnd = this.onDragEnd.bind(this);
            this.onDragOver = this.onDragOver.bind(this);
            this.onDrop = this.onDrop.bind(this);
            this.addPlaceholder = this.addPlaceholder.bind(this);
            this.removePlaceholder = this.removePlaceholder.bind(this);
            this.attachEventHandlersToItem = this.attachEventHandlersToItem.bind(this);
            this.getPlaceholderNode = this.getPlaceholderNode.bind(this);
            this.toggleNonInteractive = this.toggleNonInteractive.bind(this);
            this.removeHighlight = this.removeHighlight.bind(this);
            this.triggerHighlight = this.triggerHighlight.bind(this);
        }

        attachEventHandlersToItem(item) {
            item.ondragstart = this.onDragStart;
            item.ondragend = this.onDragEnd;
            item.addEventListener('ibexa-drag-and-drop:start-removing', () => {
                item.classList.add(this.removingClass);
            });
            item.addEventListener('ibexa-drag-and-drop:end-removing', (event) => {
                item.classList.add(this.removedClass);

                item.addEventListener('animationend', () => {
                    item.remove();
                    event.detail.callback();
                });
            });

            const preventedNode = item.querySelector(this.selectorPreventDrag);

            if (preventedNode) {
                preventedNode.draggable = true;
                preventedNode.ondragstart = (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                };
            }
        }

        triggerHighlight(item) {
            item.classList.add(this.highlightClass);

            global.setTimeout(() => {
                this.removeHighlight();
            }, TIMEOUT_REMOVE_HIGHLIGHT);
        }

        removeHighlight() {
            const highlightedItem = doc.querySelector(`.${this.highlightClass}`);

            highlightedItem?.classList.remove(this.highlightClass);
        }

        getPlaceholderNode(event) {
            const { target, clientY } = event;

            const itemNode = target.closest(`${this.selectorItem}:not(${this.selectorPlaceholder})`);

            if (itemNode) {
                return itemNode;
            }

            const items = [...this.itemsContainer.querySelectorAll(this.selectorItem)];
            items.reverse();

            const insertAfterItem = items.find((item) => {
                const { top } = item.getBoundingClientRect();

                return top <= clientY;
            });

            return insertAfterItem;
        }

        getPlaceholderPositionTop(item, event) {
            return event.clientY;
        }

        toggleNonInteractive(state) {
            [...this.itemsContainer.querySelectorAll(this.selectorItem)].forEach((el) => {
                el.classList.toggle(`${this.itemMainClass}--is-non-interactive`, state);
            });
        }

        onDragStart(event) {
            event.dataTransfer.dropEffect = 'move';
            event.dataTransfer.setData('text/html', event.currentTarget);

            setTimeout(() => {
                event.target.closest(this.selectorItem).classList.add(this.draggingOutClass);
                this.toggleNonInteractive(true);
            }, 0);
            this.draggedItem = event.currentTarget;
        }

        onDragEnd() {
            this.itemsContainer.querySelector(`.${this.draggingOutClass}`).classList.remove(this.draggingOutClass);
            this.toggleNonInteractive(false);
        }

        onDragOver(event) {
            const item = this.getPlaceholderNode(event);

            if (!item) {
                return false;
            }

            const positionY = this.getPlaceholderPositionTop(item, event);

            this.removePlaceholder();
            this.addPlaceholder(item, positionY);

            return true;
        }

        onDrop() {
            this.itemsContainer.insertBefore(this.draggedItem, this.itemsContainer.querySelector(this.selectorPlaceholder));
            this.removePlaceholder();
            this.triggerHighlight(this.draggedItem);
        }

        addPlaceholder(element, positionY) {
            const container = doc.createElement('div');
            const rect = element.getBoundingClientRect();
            const middlePositionY = rect.top + rect.height / 2;
            const where = positionY <= middlePositionY ? element : element.nextSibling;

            container.insertAdjacentHTML('beforeend', this.itemsContainer.dataset.placeholder);

            this.placeholder = container.querySelector(this.selectorPlaceholder);

            this.itemsContainer.insertBefore(this.placeholder, where);
        }

        removePlaceholder() {
            if (this.placeholder) {
                this.placeholder.remove();
            }
        }

        init() {
            this.itemsContainer.ondragover = this.onDragOver;
            this.itemsContainer.addEventListener('drop', this.onDrop, false);

            doc.body.addEventListener('dragover', (event) => {
                if (!this.itemsContainer.contains(event.target)) {
                    this.removePlaceholder();
                } else {
                    event.preventDefault();
                }
            });

            this.itemsContainer.querySelectorAll(this.selectorItem).forEach(this.attachEventHandlersToItem);
        }

        reinit() {
            this.itemsContainer.removeEventListener('drop', this.onDrop);

            this.init();
        }
    }

    ibexa.addConfig('core.Draggable', Draggable);
})(window, window.document, window.ibexa);
