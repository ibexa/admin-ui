(function (global, doc, ibexa) {
    const SELECTOR_PLACEHOLDER = '.ibexa-draggable__placeholder';
    const SELECTOR_PREVENT_DRAG = '.ibexa-draggable__prevent-drag';

    class Draggable {
        constructor(config) {
            this.draggedItem = null;
            this.placeholder = null;
            this.onDragOverTimeout = null;
            this.itemsContainer = config.itemsContainer;
            this.selectorItem = config.selectorItem;
            this.selectorPlaceholder = config.selectorPlaceholder || SELECTOR_PLACEHOLDER;
            this.selectorPreventDrag = config.selectorPreventDrag || SELECTOR_PREVENT_DRAG;

            this.onDragStart = this.onDragStart.bind(this);
            this.onDragEnd = this.onDragEnd.bind(this);
            this.onDragOver = this.onDragOver.bind(this);
            this.onDrop = this.onDrop.bind(this);
            this.addPlaceholder = this.addPlaceholder.bind(this);
            this.removePlaceholder = this.removePlaceholder.bind(this);
            this.attachEventHandlersToItem = this.attachEventHandlersToItem.bind(this);
            this.getPlaceholderNode = this.getPlaceholderNode.bind(this);
        }

        attachEventHandlersToItem(item) {
            item.ondragstart = this.onDragStart;
            item.ondragend = this.onDragEnd;

            const preventedNode = item.querySelector(this.selectorPreventDrag);

            if (preventedNode) {
                preventedNode.draggable = true;
                preventedNode.ondragstart = (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                };
            }
        }

        getPlaceholderNode(target) {
            return target.closest(`${this.selectorItem}:not(${this.selectorPlaceholder})`);
        }

        getPlaceholderPosition(item, event) {
            return event.clientY;
        }

        onDragStart(event) {
            event.dataTransfer.dropEffect = 'move';
            event.dataTransfer.setData('text/html', event.currentTarget);

            setTimeout(() => {
                event.target.style.setProperty('display', 'none');
            }, 0);
            this.draggedItem = event.currentTarget;
        }

        onDragEnd() {
            this.draggedItem.style.removeProperty('display');
        }

        onDragOver(event) {
            const item = this.getPlaceholderNode(event.target);

            if (!item) {
                return false;
            }

            const positionY = this.getPlaceholderPosition(item, event);

            this.removePlaceholder();
            this.addPlaceholder(item, positionY);

            return true;
        }

        onDrop() {
            this.itemsContainer.insertBefore(this.draggedItem, this.itemsContainer.querySelector(this.selectorPlaceholder));
            this.removePlaceholder();
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
