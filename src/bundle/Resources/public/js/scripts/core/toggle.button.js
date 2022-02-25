(function (global, doc, eZ) {
    class ToggleButton {
        constructor(config) {
            this.toggleNode = config.toggleNode;
            this.inputsSelector = config?.inputsSelector || 'input';

            this.toggleState = this.toggleState.bind(this);
            this.addFocus = this.addFocus.bind(this);
            this.removeFocus = this.removeFocus.bind(this);
            this.init = this.init.bind(this);
        }

        toggleState(event) {
            event.preventDefault();

            const toggler = event.currentTarget;

            if (toggler.classList.contains('ibexa-toggle--is-disabled')) {
                return;
            }

            const isChecked = toggler.classList.toggle('ibexa-toggle--is-checked');

            if (toggler.classList.contains('ibexa-toggle--radio')) {
                const valueToSet = isChecked ? 1 : 0;

                toggler.querySelector(`.form-check input[value="${valueToSet}"]`).checked = true;
            } else {
                const toggleInput = toggler.querySelector('.ibexa-toggle__input');

                toggleInput.checked = isChecked;
                toggleInput.dispatchEvent(new Event('change'));
            }
        }

        addFocus(event) {
            event.preventDefault();

            const toggler = event.currentTarget.closest('.ibexa-toggle');

            if (toggler.classList.contains('ibexa-toggle--is-disabled')) {
                return;
            }

            toggler.classList.add('ibexa-toggle--is-focused');
        }

        removeFocus(event) {
            event.preventDefault();

            const toggler = event.currentTarget.closest('.ibexa-toggle');

            if (toggler.classList.contains('ibexa-toggle--is-disabled')) {
                return;
            }

            toggler.classList.remove('ibexa-toggle--is-focused');
        }

        init() {
            const toggleInput = this.toggleNode.querySelector(this.inputsSelector);

            this.toggleNode.addEventListener('click', this.toggleState, false);
            toggleInput.addEventListener('focus', this.addFocus, false);
            toggleInput.addEventListener('blur', this.removeFocus, false);
        }
    }

    eZ.addConfig('core.ToggleButton', ToggleButton);
})(window, window.document, window.eZ);
