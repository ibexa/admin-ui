(function (global, doc, ibexa) {
    const inputTypeToPreventSubmit = [
        'checkbox',
        'color',
        'date',
        'datetime-local',
        'email',
        'file',
        'image',
        'month',
        'number',
        'radio',
        'range',
        'reset',
        'search',
        'select-one',
        'select-multiple',
        'tel',
        'text',
        'time',
        'url',
    ];
    const ENTER_KEY = 'Enter';

    const preventSubmitOnEnter = (form) => {
        form.addEventListener(
            'keydown',
            (event) => {
                const activeElementType = typeof doc.activeElement.type !== 'undefined' ? doc.activeElement.type.toLowerCase() : '';

                if (event.key === ENTER_KEY && inputTypeToPreventSubmit.includes(activeElementType)) {
                    event.preventDefault();
                }
            },
            false,
        );
    };

    ibexa.addConfig('helpers.form', {
        preventSubmitOnEnter,
    });
})(window, window.document, window.ibexa);
