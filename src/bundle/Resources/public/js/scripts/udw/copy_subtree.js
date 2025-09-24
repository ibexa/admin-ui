(function (global, doc, ibexa, React, ReactDOMClient, Translator) {
    const btns = doc.querySelectorAll('.ibexa-btn--udw-copy-subtree');
    const form = doc.querySelector('form[name="location_copy_subtree"]');
    const input = form.querySelector('#location_copy_subtree_new_parent_location');
    const udwContainer = doc.querySelector('#react-udw');
    let udwRoot = null;
    const closeUDW = () => udwRoot.unmount();
    const onConfirm = (items) => {
        closeUDW();

        input.value = items[0].id;
        form.submit();
    };
    const onCancel = () => closeUDW();
    const openUDW = (event) => {
        event.preventDefault();

        const title = Translator.trans(/* @Desc("Select Location") */ 'subtree.title', {}, 'ibexa_universal_discovery_widget');
        const config = JSON.parse(event.currentTarget.dataset.udwConfig);

        udwRoot = ReactDOMClient.createRoot(udwContainer);
        udwRoot.render(
            React.createElement(ibexa.modules.UniversalDiscovery, {
                onConfirm,
                onCancel,
                title,
                multiple: false,
                containersOnly: true,
                ...config,
            }),
        );
    };

    btns.forEach((btn) => btn.addEventListener('click', openUDW, false));
})(window, window.document, window.ibexa, window.React, window.ReactDOMClient, window.Translator);
