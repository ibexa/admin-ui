(function (global, doc) {
    const previewNode = doc.querySelector('.ibexa-location-preview');

    if (!previewNode) {
        return;
    }

    const previewIframeWrapperNode = previewNode.querySelector('.ibexa-location-preview__iframe-wrapper');
    const previewIframe = previewIframeWrapperNode.querySelector('.ibexa-location-preview__iframe');
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
})(window, window.document);
