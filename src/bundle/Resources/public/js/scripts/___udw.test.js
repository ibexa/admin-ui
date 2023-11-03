import assetsLibraryWidget from '../../../../../../../assets-library-widget/src/bundle/Resources/public/js/assets.library.widget';

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
                token: 'f72e8ac375def2c346d5d3e7057a.u0r1YnqgMyL6zbfVE5Mxdi3yA6TaXKZTmAALD7BzFTM.iH-qKyzHa3Oshv2ya6Z7B36xZfeqFskQwGYmTOo0cGDSL6RSKNV6dL6j1A',
                siteaccess: 'admin',
            },
        };

        udwRoot = ReactDOM.createRoot(container);
        udwRoot.render(React.createElement(assetsLibraryWidget, config));
    };

    triggerElement.addEventListener('click', openUdw, false);
    setTimeout(() => {
        openUdw();
    }, 100);
})(window, window.document, window.React, window.ReactDOM);
