(function (global, doc) {
    const previewNode = doc.querySelector('.ibexa-location-preview');
    const previewSiteaccessDropdownInput = previewNode.querySelector('.ibexa-location-preview__siteaccess .ibexa-dropdown .ibexa-dropdown__source .ibexa-input');
    const previewIframeWrapperNode = previewNode.querySelector('.ibexa-location-preview__iframe-wrapper');
    const previewIframe = previewIframeWrapperNode.querySelector('.ibexa-location-preview__iframe');
    const handleSiteaccessChange = (event) => {
        previewIframe.src = event.target.value;
    }
    const resizeIframe = () => {
        const currentPreviewWidth = previewIframeWrapperNode.offsetWidth;
        const currentIframeWidth = previewIframe.offsetWidth;
        const scaleValue = currentPreviewWidth / currentIframeWidth;

        previewIframe.style.scale = scaleValue;

        const newPreviewNodeHeight = previewIframe.getBoundingClientRect().height;

        previewIframeWrapperNode.style.height = `${newPreviewNodeHeight}px`;
    }
    const blockEventsInsideIframe = () => {
        const documentHTML = previewIframe.contentWindow.document.documentElement;

        documentHTML.style.pointerEvents = 'none';
    }
    const handleIframeLoad = () => {
        blockEventsInsideIframe();
    }
    const resizeObserver = new ResizeObserver(() => {
        resizeIframe();
    });

    resizeObserver.observe(previewIframeWrapperNode);
    previewIframe.addEventListener('load', handleIframeLoad, false);
    previewSiteaccessDropdownInput.addEventListener('change', handleSiteaccessChange, false);
})(window, window.document);
