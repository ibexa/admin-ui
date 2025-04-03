(function (global, doc, ibexa) {
    class Taggify {
        constructor(config) {
            this.container = config.container;
            this.acceptKeys = config.acceptKeys ?? ['Enter'];
            this.inputNode = config.inputNode ?? this.container.querySelector('.ibexa-taggify__input');
            this.listNode = config.listNode ?? this.container.querySelector('.ibexa-taggify__list');
            this.tagsPattern = config.tagsPattern ?? null;
            this.tags = config.tags ?? new Set();

            this.attachEventsToTag = this.attachEventsToTag.bind(this);
            this.handleInputKeyUp = this.handleInputKeyUp.bind(this);
            this.addElementAttributes = this.addElementAttributes.bind(this);
        }

        afterTagsUpdate() {}

        isAcceptKeyPressed(key) {
            return this.acceptKeys.includes(key);
        }

        addElementAttributes(element, attrs) {
            const getValue = (value) => (typeof value === 'object' ? JSON.stringify(value) : value);

            Object.entries(attrs).forEach(([attrKey, attrValue]) => {
                if (attrKey === 'dataset') {
                    Object.entries(attrValue).forEach(([datasetKey, datasetValue]) => {
                        element.dataset[datasetKey] = getValue(datasetValue);
                    });
                } else {
                    if (element.hasAttribute(attrKey)) {
                        console.warn(`Element already has the attribute named ${attrKey}`);

                        return;
                    }

                    element.setAttribute(attrKey, getValue(attrValue));
                }
            });
        }

        addTag(name, value, attrs = {}, tooltipAttrs = {}) {
            const tagTemplate = this.listNode.dataset.template;
            const renderedTemplate = tagTemplate.replace('{{ name }}', name).replace('{{ value }}', value);
            const div = doc.createElement('div');

            div.insertAdjacentHTML('beforeend', renderedTemplate);

            const tag = div.querySelector('.ibexa-taggify__list-tag');
            const tagNameNode = tag.querySelector('.ibexa-taggify__list-tag-name');

            this.addElementAttributes(tag, attrs);
            this.addElementAttributes(tagNameNode, tooltipAttrs);
            this.attachEventsToTag(tag, value);
            this.listNode.insertBefore(tag, this.inputNode);
            this.tags.add(value);
            this.afterTagsUpdate();
        }

        removeTag(tag, value) {
            this.tags.delete(value);

            tag.remove();

            this.afterTagsUpdate();
        }

        attachEventsToTag(tag, value) {
            const removeBtn = tag.querySelector('.ibexa-taggify__btn--remove');

            removeBtn.addEventListener('click', () => this.removeTag(tag, value), false);
        }

        handleInputKeyUp(event) {
            if (this.tagsPattern && !this.tagsPattern.test(this.inputNode.value)) {
                return;
            }

            if (this.isAcceptKeyPressed(event.key) && this.inputNode.value && !this.tags.has(this.inputNode.value)) {
                this.addTag(this.inputNode.value, this.inputNode.value);

                this.inputNode.value = '';
            }
        }

        init() {
            this.inputNode.addEventListener('keyup', this.handleInputKeyUp, false);
        }
    }

    ibexa.addConfig('core.Taggify', Taggify);
})(window, window.document, window.ibexa);
