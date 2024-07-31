(function (global, doc, ibexa, React, ReactDOM, Translator, Routing) {
    const btns = doc.querySelectorAll('.ibexa-btn--udw-browse');
    const udwContainer = doc.getElementById('react-udw');
    let udwRoot = null;
    const closeUDW = () => udwRoot.unmount();
    const onConfirm = (items) => {
        closeUDW();

        global.location.href = Routing.generate('ibexa.content.view', {
            contentId: items[0].ContentInfo.Content._id,
            locationId: items[0].id,
        });
    };
    const onCancel = () => closeUDW();
    const openUDW = (event) => {
        event.preventDefault();

        const config = JSON.parse(event.currentTarget.dataset.udwConfig);
        const title = Translator.trans(/*@Desc("Browse content")*/ 'browse.title', {}, 'ibexa_universal_discovery_widget');

        udwRoot = ReactDOM.createRoot(udwContainer);
        udwRoot.render(
            React.createElement(ibexa.modules.UniversalDiscovery, {
                onConfirm,
                onCancel,
                title,
                multiple: false,
                ...config,
            }),
        );
    };

    btns.forEach((btn) => btn.addEventListener('click', openUDW, false));
})(window, window.document, window.ibexa, window.React, window.ReactDOM, window.Translator, window.Routing);
