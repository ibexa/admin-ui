import assetsLibraryWidget from '@ibexa-assets-library-widget/src/bundle/Resources/public/js/assets.library.widget';

(function (global, doc, React, ReactDOM) {
    let udwRoot = null;

    const imagePickerBtn = doc.querySelector('.ibexa-open-image-picker');
    const udwBtn = doc.querySelector('.ibexa-relations__cta-btn');

    const container = doc.getElementById('react-udw');
    const closeUDW = () => udwRoot.unmount();
    const openUdw = async (event) => {
        const triggerElement = event.currentTarget;
        const configUDW = {
            ...JSON.parse(triggerElement.dataset.udwConfig),
            title: triggerElement.classList.contains('ibexa-open-image-picker') ? 'Image picker' : 'UDW',
        };

        const config = {
            ...configUDW,
            multiple: true,
            // activeTab: 'image_picker',
            // rootLocationId: 51,
            onConfirm: () => {
                console.log('confirm');
            },
            onCancel: closeUDW,
            restInfo: {
                token: 'd841ca.RfcZs2jLlEue-t8KFM7BfoA2BqMHpC06UOuqerrXQ4A.BpBS-17zyyqsqed-V7SNSOhvWelS7hkOadnEI_y4DbYfj2zEPPzBCf2ltA',
                siteaccess: 'admin',
            },
        };

        udwRoot = ReactDOM.createRoot(container);
        udwRoot.render(React.createElement(assetsLibraryWidget, config));
    };

    imagePickerBtn.addEventListener('click', openUdw, false);
    udwBtn.addEventListener('click', openUdw, false);
    // setTimeout(() => {
    //     openUdw();
    // }, 100);
})(window, window.document, window.React, window.ReactDOM);
