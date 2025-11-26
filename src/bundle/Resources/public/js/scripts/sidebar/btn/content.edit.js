(function (global, doc) {
    const editButtons = doc.querySelectorAll('.ibexa-btn--edit');

    editButtons.forEach((editButton) => {
        const languageRadioOption = doc.querySelector(
            `.ibexa-extra-actions--edit.ibexa-extra-actions--prevent-show[data-actions="${editButton.dataset.actions}"] .ibexa-input--radio`,
        );

        if (!languageRadioOption) {
            return;
        }

        editButton.addEventListener(
            'click',
            () => {
                languageRadioOption.checked = true;
                languageRadioOption.dispatchEvent(
                    new CustomEvent('change', {
                        detail: {
                            sendImmediately: true,
                        },
                    }),
                );
            },
            false,
        );
    });
})(window, window.document);
