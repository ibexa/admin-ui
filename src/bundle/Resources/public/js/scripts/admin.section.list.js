(function (global, doc, ibexa, React, ReactDOM) {
    const btns = doc.querySelectorAll('.ibexa-btn--open-udw');
    const udwContainer = doc.getElementById('react-udw');
    let udwRoot = null;
    const closeUDW = () => udwRoot.unmount();
    const onConfirm = (form, content) => {
        const field = form.querySelector(`#${form.getAttribute('name')}_locations_location`);

        field.value = content.map((item) => item.id).join();

        closeUDW();
        form.submit();
    };
    const onCancel = () => closeUDW();
    const openUDW = (event) => {
        event.preventDefault();

        const form = doc.querySelector('form[name="section_content_assign"]');
        const btn = event.target.closest('a');
        const config = JSON.parse(btn.dataset.udwConfig);

        form.action = btn.dataset.formAction;
        doc.querySelector('#section_content_assign_section').value = btn.dataset.sectionId;

        udwRoot = ReactDOM.createRoot(udwContainer);
        udwRoot.render(
            React.createElement(ibexa.modules.UniversalDiscovery, {
                onConfirm: onConfirm.bind(this, form),
                onCancel,
                ...config,
            }),
        );
    };

    btns.forEach((btn) => btn.addEventListener('click', openUDW, false));
})(window, window.document, window.ibexa, window.React, window.ReactDOM);
