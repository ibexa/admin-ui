(function (global, doc, ibexa) {
    const { escapeHTML, escapeHTMLAttribute } = ibexa.helpers.text;
    const { dangerouslyInsertAdjacentHTML } = ibexa.helpers.dom;

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
            this.handleInputBlur = this.handleInputBlur.bind(this);
            this.addElementAttributes = this.addElementAttributes.bind(this);
        }

        afterTagsUpdate() {}

        isAcceptKeyPressed(key) {
            return this.acceptKeys.includes(key);
        }

        removeAcceptKey(name) {
            if (this.acceptKeys.includes(name.at(-1))) {
                return name.slice(0, -1);
            }

            return name;
        }

        addElementAttributes(element, attrs) {
            const getValue = (value) => (typeof value === 'object' ? JSON.stringify(value) : value);

            Object.entries(attrs).forEach(([attrKey, attrValue]) => {
                if (attrKey === 'dataset') {
                    Object.entries(attrValue).forEach(([datasetKey, datasetValue]) => {
                        element.dataset[datasetKey] = getValue(datasetValue);
                    });
                } else if (element.hasAttribute(attrKey)) {
                    console.warn(`Element already has the attribute named ${attrKey}`);
                } else {
                    element.setAttribute(attrKey, getValue(attrValue));
                }
            });
        }

        addTags(tagsData) {
            const newTagsData = tagsData.reduce((processedTags, tagData) => {
                const { value } = tagData;
                const processedTagsValues = new Set(processedTags.map((tag) => tag.value));

                if (!value || this.tags.has(value) || processedTagsValues.has(value)) {
                    return processedTags;
                }

                processedTags.push(tagData);

                return processedTags;
            }, []);

            if (newTagsData.length === 0) {
                return;
            }

            const tagTemplate = this.listNode.dataset.template;

            newTagsData.forEach(({ name, value, attrs = {}, tooltipAttrs = {} }) => {
                const nameHtmlEscaped = escapeHTML(name);
                const valueHtmlAttributeEscaped = escapeHTMLAttribute(value);
                const renderedTemplate = tagTemplate
                    .replace('{{ name }}', nameHtmlEscaped)
                    .replace('{{ value }}', valueHtmlAttributeEscaped);
                const div = doc.createElement('div');

                dangerouslyInsertAdjacentHTML(div, 'beforeend', renderedTemplate);

                const tag = div.querySelector('.ibexa-taggify__list-tag');
                const tagNameNode = tag.querySelector('.ibexa-taggify__list-tag-name');

                this.addElementAttributes(tag, { ...attrs, dataset: { ...attrs.dataset, value } });
                this.addElementAttributes(tagNameNode, tooltipAttrs);
                this.attachEventsToTag(tag, value);
                this.listNode.insertBefore(tag, this.inputNode);
                this.tags.add(value);
            });

            this.afterTagsUpdate();
        }

        addTag(name, value, attrs = {}, tooltipAttrs = {}) {
            this.addTags([
                {
                    name,
                    value,
                    attrs,
                    tooltipAttrs,
                },
            ]);
        }

        removeTagWithValue(value) {
            const tag = this.listNode.querySelector(`.ibexa-taggify__list-tag[data-value="${value}"]`);

            if (tag) {
                this.removeTag(tag, value);
            }
        }

        removeTag(tag, value) {
            this.tags.delete(value);

            tag.remove();

            this.afterTagsUpdate();
        }

        removeAllTags() {
            this.tags.forEach((tag) => {
                const tagNode = this.listNode.querySelector(`.ibexa-taggify__list-tag[data-value="${tag}"]`);

                if (tagNode) {
                    tagNode.remove();
                }
            });

            this.tags.clear();
            this.afterTagsUpdate();
        }

        attachEventsToTag(tag, value) {
            const removeBtn = tag.querySelector('.ibexa-taggify__btn--remove');

            removeBtn.addEventListener('click', () => this.removeTag(tag, value), false);
        }

        handleInputBlur() {
            const inputValue = this.inputNode.value.trim();

            this.addTag(inputValue, inputValue);
            this.inputNode.value = '';
        }

        handleInputKeyUp(event) {
            if (this.tagsPattern && !this.tagsPattern.test(this.inputNode.value)) {
                return;
            }

            if (this.isAcceptKeyPressed(event.key)) {
                const nameWithoutAcceptKey = this.removeAcceptKey(this.inputNode.value);

                this.addTag(nameWithoutAcceptKey, nameWithoutAcceptKey);

                this.inputNode.value = '';
            }
        }

        init() {
            this.inputNode.addEventListener('keyup', this.handleInputKeyUp, false);
            this.inputNode.addEventListener('blur', this.handleInputBlur, false);
        }
    }

    ibexa.addConfig('core.Taggify', Taggify);
})(window, window.document, window.ibexa);
