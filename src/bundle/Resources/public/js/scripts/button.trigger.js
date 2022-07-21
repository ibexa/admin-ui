(function (global, doc) {
    doc.addEventListener(
        'DOMContentLoaded',
        () => {
            const buttons = doc.querySelectorAll('.ibexa-btn--trigger');
            const trigger = (event) => {
                event.preventDefault();

                const button = event.currentTarget;
                const triggerTargetElement = doc.querySelector(button.dataset.click);

                triggerTargetElement.click();
            };

            buttons.forEach((button) => button.addEventListener('click', trigger, false));
        },
        false,
    );
})(window, document);
