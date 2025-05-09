(function (global, doc, ibexa, React, ReactDOMClient, Translator) {
    const btns = doc.querySelectorAll('.ibexa-btn--udw-add');
    const submitButton = doc.querySelector('#content_location_add_add');
    const form = doc.querySelector('form[name="content_location_add"]');

    if (!form) {
        return;
    }

    const input = form.querySelector('#content_location_add_new_locations');
    const udwContainer = doc.getElementById('react-udw');
    let udwRoot = null;
    const closeUDW = () => udwRoot.unmount();
    const onConfirm = (items) => {
        closeUDW();

        input.value = items[0].id;
        submitButton.click();
    };
    const onCancel = () => closeUDW();
    const openUDW = (event) => {
        event.preventDefault();
        event.stopPropagation();

        const config = JSON.parse(event.currentTarget.dataset.udwConfig);
        const title = Translator.trans(/*@Desc("Select Location")*/ 'add_location.title', {}, 'ibexa_universal_discovery_widget');

        udwRoot = ReactDOMClient.createRoot(udwContainer);
        udwRoot.render(
            React.createElement(ibexa.modules.UniversalDiscovery, {
                onConfirm,
                onCancel,
                containersOnly: true,
                title,
                multiple: false,
                ...config,
            }),
        );
    };

    btns.forEach((btn) => btn.addEventListener('click', openUDW, false));
})(window, window.document, window.ibexa, window.React, window.ReactDOMClient, window.Translator);
