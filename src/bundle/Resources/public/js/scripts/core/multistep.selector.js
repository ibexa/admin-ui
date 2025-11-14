(function (global, doc, ibexa) {
    class StepSelector {
        constructor(container, { customDropdown } = {}) {
            this.container = container;
            this.dropdownInitialContainer = this.container.querySelector('.ibexa-multistep-selector__dropdown-initial');
            this.dropdownContainer = this.container.querySelector('.ibexa-multistep-selector__dropdown');
            this.dropdownTemplate = this.container.querySelector('template');
            this.filledTemplate = null;
            this.value = [];
            this.DropdownClass = customDropdown ?? ibexa.core.Dropdown;

            this.createDropdown = this.createDropdown.bind(this);
            this.loadData = this.loadData.bind(this);
        }

        fillSourceOptions(options) {
            const { escapeHTML } = ibexa.helpers.text;
            const sourceInput = this.filledTemplate.querySelector('.ibexa-dropdown__source .ibexa-input');

            options.forEach(({ id, name }) => {
                const nameHtmlEscaped = escapeHTML(name);
                const idHtmlEscaped = escapeHTML(id);
                const optionNode = doc.createElement('option');

                optionNode.value = idHtmlEscaped;
                optionNode.textContent = nameHtmlEscaped;

                sourceInput.appendChild(optionNode);
            });
        }

        fillListOptions(options) {
            const { escapeHTML } = ibexa.helpers.text;
            const { dangerouslyInsertAdjacentHTML } = ibexa.helpers.dom;
            const itemsList = this.filledTemplate.querySelector('.ibexa-dropdown__items-list');
            const itemsListFragment = doc.createDocumentFragment();
            const { template: itemTemplate } = itemsList.dataset;

            options.forEach(({ id, name }) => {
                const nameHtmlEscaped = escapeHTML(name);
                const idHtmlEscaped = escapeHTML(id);
                const itemsContainer = doc.createElement('ul');
                const itemRendered = itemTemplate.replace('{{ value }}', idHtmlEscaped).replaceAll('{{ label }}', nameHtmlEscaped);

                dangerouslyInsertAdjacentHTML(itemsContainer, 'beforeend', itemRendered);
                itemsListFragment.append(itemsContainer.querySelector('li'));
            });

            itemsList.append(itemsListFragment);
        }

        createDropdown(options = [], values = []) {
            this.filledTemplate = this.dropdownTemplate.content.cloneNode(true);

            this.fillSourceOptions(options);
            this.fillListOptions(options);
            this.toggleDropdown(true);

            this.dropdownContainer.innerHTML = '';
            this.dropdownContainer.appendChild(this.filledTemplate);
            this.filledTemplate = null;
            this.dropdownInstance = new this.DropdownClass({
                container: this.dropdownContainer.querySelector('.ibexa-dropdown'),
            });

            this.dropdownInstance.init();

            this.value = values;
            values.forEach((value) => {
                const element = this.dropdownInstance.itemsContainer.querySelector(`.ibexa-dropdown__item[data-value="${value}"]`);

                if (!element) {
                    return;
                }

                this.dropdownInstance.onSelect(element, true);
            });

            this.bindOnChangeListener();
        }

        toggleDropdown(showFinal) {
            const initialDropdown = this.container.querySelector('.ibexa-multistep-selector__dropdown-initial');
            const finalDropdown = this.container.querySelector('.ibexa-multistep-selector__dropdown');

            if (showFinal) {
                initialDropdown.setAttribute('hidden', true);
                finalDropdown.removeAttribute('hidden');
            } else {
                finalDropdown.setAttribute('hidden', true);
                initialDropdown.removeAttribute('hidden');
            }
        }

        toggleLoader(showLoader) {
            const placeholder = this.dropdownInitialContainer.querySelector('.ibexa-dropdown__selected-placeholder');
            const loader = this.dropdownInitialContainer.querySelector('.ibexa-dropdown__loader-wrapper');

            if (showLoader) {
                placeholder.setAttribute('hidden', true);
                loader.removeAttribute('hidden');
            } else {
                loader.setAttribute('hidden', true);
                placeholder.removeAttribute('hidden');
            }
        }

        loadData(requestPromise, values = []) {
            this.reset();
            this.toggleDropdown(false);
            this.toggleLoader(true);

            requestPromise().then((response) => {
                this.toggleLoader(false);

                if (response.length === 0) {
                    return;
                }

                this.createDropdown(response, values);
            });
        }

        addOnChangeListener(callback) {
            this.bindOnChangeListener = () => {
                this.dropdownInstance.sourceInput.addEventListener('change', (event) => {
                    const selectedValues = [...event.target.selectedOptions].map((option) => option.value);

                    this.value = selectedValues;
                    callback({ selectedValues });
                });
            };
        }

        reset() {
            this.value = [];
            this.toggleDropdown(false);
            this.toggleLoader(false);
        }

        init() {}
    }

    class MultistepSelector {
        constructor(container, steps, { customDropdown, initialValue, callback } = {}) {
            this.container = container;
            this.callback = callback;
            this.initialValue = initialValue ?? [];

            this.steps = steps.map((step) => {
                const stepContainer = this.container.querySelector(`.ibexa-multistep-selector__step[data-step-id="${step.id}"]`);

                return {
                    ...step,
                    instance: new StepSelector(stepContainer, { customDropdown }),
                };
            });
        }

        init() {
            this.steps.forEach((step, key) => {
                const nextStep = this.steps[key + 1];
                const futureSteps = this.steps.slice(key + 2);

                step.instance.init();

                step.instance.addOnChangeListener((params) => {
                    if (nextStep) {
                        nextStep.instance.loadData(() => nextStep.loadData(params));
                    }

                    futureSteps.forEach((futureStep) => futureStep.instance.reset());

                    if (this.callback) {
                        const output = this.steps.map(({ instance }) => instance.value);

                        this.callback(output);
                    }
                });
            });

            if (this.steps[0]) {
                const firstStepInitialValue = this.initialValue[0] || [];

                this.steps[0].instance.loadData(() => this.steps[0].loadData(), firstStepInitialValue);
            }

            this.initialValue.forEach((payloadValues, key) => {
                const step = this.steps[key + 1];
                const stepValues = this.initialValue[key + 1];

                if (!step) {
                    return;
                }

                step.instance.loadData(() => step.loadData({ selectedValues: payloadValues }), stepValues);
            });
        }
    }

    ibexa.addConfig('core.MultistepSelector', MultistepSelector);
})(window, window.document, window.ibexa);
