(function (global, doc, ibexa) {
    class StepSelector {
        constructor(container, apiUrl) {
            this.container = container;
            this.apiUrl = apiUrl;
            this.dropdownInitialContainer = this.container.querySelector('.ibexa-multistep-selector__dropdown-initial');
            this.dropdownContainer = this.container.querySelector('.ibexa-multistep-selector__dropdown');
            this.dropdownTemplate = this.container.querySelector('template');
            this.filledTemplate = null;

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

        createDropdown(options = []) {
            this.filledTemplate = this.dropdownTemplate.content.cloneNode(true);

            this.fillSourceOptions(options);
            this.fillListOptions(options);
            this.toggleDropdown(true);

            this.dropdownContainer.innerHTML = '';
            this.dropdownContainer.appendChild(this.filledTemplate);
            this.filledTemplate = null;
            this.dropdownInstance = new ibexa.core.Dropdown({
                container: this.dropdownContainer.querySelector('.ibexa-dropdown'),
            });

            this.dropdownInstance.init();
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

        loadData(requestPromise) {
            this.toggleDropdown(false);
            this.toggleLoader(true);

            requestPromise().then((response) => {
                this.toggleLoader(false);
                this.createDropdown(response);
            });
        }

        addOnChangeListener(callback) {
            this.bindOnChangeListener = () => {
                this.dropdownInstance.sourceInput.addEventListener('change', (event) => {
                    const selectedValues = [...event.target.selectedOptions].map((option) => option.value);
                    callback({ selectedValues });
                });
            };
        }

        reset() {
            this.toggleDropdown(false);
            this.toggleLoader(false);
        }

        init() {}
    }

    class MultistepSelector {
        constructor(container, steps) {
            this.container = container;

            this.steps = steps.map((step) => {
                const stepContainer = this.container.querySelector(`.ibexa-multistep-selector__step[data-step-id="${step.id}"]`);

                return {
                    ...step,
                    instance: new StepSelector(stepContainer),
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
                });
            });

            if (this.steps[0]) {
                this.steps[0].instance.loadData(() => this.steps[0].loadData());
            }
        }
    }

    ibexa.addConfig('core.MultistepSelector', MultistepSelector);
})(window, window.document, window.ibexa);
