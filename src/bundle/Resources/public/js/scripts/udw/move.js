(function (global, doc, ibexa, React, ReactDOM, Translator) {
    const btns = doc.querySelectorAll('.ibexa-btn--udw-move');
    const form = doc.querySelector('form[name="location_move"]');

    if (!form) {
        return;
    }

    const input = form.querySelector('#location_move_new_parent_location');
    const udwContainer = doc.getElementById('react-udw');
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

        const config = JSON.parse(event.currentTarget.dataset.udwConfig);
        const title = Translator.trans(/*@Desc("Select destination")*/ 'move.title', {}, 'universal_discovery_widget');

        udwRoot = ReactDOM.createRoot(udwContainer);
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
})(window, window.document, window.ibexa, window.React, window.ReactDOM, window.Translator);
