import assetsLibraryWidget from '@ibexa-assets-library-widget/src/bundle/Resources/public/js/assets.library.widget';

(function (global, doc, React, ReactDOM) {
    let udwRoot = null;
    const container = doc.getElementById('react-udw');
    const triggerElement = doc.querySelector('.ibexa-open-image-picker');
    const closeUDW = () => udwRoot.unmount();
    const openUdw = async () => {
        const config = {
            ...JSON.parse(triggerElement.dataset.udwConfig),
            title: 'test image picker',
            activeTab: 'image_picker',
            rootLocationId: 51,
            onConfirm: () => {
                console.log('confirm');
            },
            onCancel: closeUDW,
            restInfo: {
                token: '0b3b96f4c5fcd.bHTsJMDym1W3FJTlAFn0aOClYQuQlNkzlO340R02Vec.CwSKcaKzqj7QbKOVQi63GrPoD2f405hhrJ2yiUd9DYg_F7pM8abIAdxA-w',
                siteaccess: 'admin',
            },
        };

        udwRoot = ReactDOM.createRoot(container);
        udwRoot.render(React.createElement(assetsLibraryWidget, config));
    };

    triggerElement.addEventListener('click', openUdw, false);
    // setTimeout(() => {
    //     openUdw();
    // }, 100);
})(window, window.document, window.React, window.ReactDOM);
