(function (global, doc, ibexa) {
    const SPACE_KEY = ' ';

    class ToggleButton {
        constructor(config) {
            this.toggleNode = config.toggleNode;
            this.inputsSelector = config?.inputsSelector || (this.toggleNode.classList.contains('ids-toggle') ? '.ids-toggle__source input' : 'input');
            this.checkedClass = this.toggleNode.classList.contains('ids-toggle') ? 'ids-toggle--checked' : 'ibexa-toggle--is-checked';
            this.disabledClass = this.toggleNode.classList.contains('ids-toggle') ? 'ids-toggle--disabled' : 'ibexa-toggle--is-disabled';
            this.focusedClass = this.toggleNode.classList.contains('ids-toggle') ? 'ids-toggle--focused' : 'ibexa-toggle--is-focused';

            this.toggleState = this.toggleState.bind(this);
            this.addFocus = this.addFocus.bind(this);
            this.removeFocus = this.removeFocus.bind(this);
            this.toggleStateOnSpacePressed = this.toggleStateOnSpacePressed.bind(this);
            this.init = this.init.bind(this);
        }

        toggleState(event) {
            event.preventDefault();

            const toggler = event.currentTarget;

            if (toggler.classList.contains(this.disabledClass)) {
                return;
            }

            const isChecked = toggler.classList.toggle(this.checkedClass);

            if (toggler.classList.contains('ibexa-toggle--radio')) {
                const valueToSet = isChecked ? 1 : 0;

                toggler.querySelector(`.form-check input[value="${valueToSet}"]`).checked = true;
            } else {
                const toggleInput = toggler.querySelector(this.inputsSelector);

                toggleInput.checked = isChecked;
                toggleInput.dispatchEvent(new Event('change'));
            }
        }

        addFocus(event) {
            event.preventDefault();

            const toggler = event.currentTarget.closest('.ibexa-toggle, .ids-toggle');

            if (toggler.classList.contains(this.disabledClass)) {
                return;
            }

            toggler.classList.add(this.focusedClass);
        }

        removeFocus(event) {
            event.preventDefault();

            const toggler = event.currentTarget.closest('.ibexa-toggle, .ids-toggle');

            if (toggler.classList.contains(this.disabledClass)) {
                return;
            }

            toggler.classList.remove(this.focusedClass);
        }

        toggleStateOnSpacePressed(event) {
            if (event.key === SPACE_KEY) {
                event.preventDefault();

                this.toggleState(event);
            }
        }

        init() {
            const toggleInput = this.toggleNode.querySelector(this.inputsSelector);

            this.toggleNode.addEventListener('click', this.toggleState, false);
            this.toggleNode.addEventListener('keyup', this.toggleStateOnSpacePressed, true);
            toggleInput.addEventListener('focus', this.addFocus, false);
            toggleInput.addEventListener('blur', this.removeFocus, false);
        }
    }

    ibexa.addConfig('core.ToggleButton', ToggleButton);
})(window, window.document, window.ibexa);
