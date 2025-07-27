(function (global, doc) {
    const formErrorNodes = doc.querySelectorAll('.ibexa-form-error');
    const observersList = [];
    const observerConfig = {
        childList: true,
        subtree: true,
    };
    const toggleHelperNode = (errorNode) => {
        const helperNode = errorNode.parentElement.querySelector('.ibexa-form-help');

        if (!helperNode) {
            return;
        }

        const isErrorVisible = !!errorNode.innerText;

        helperNode.hidden = isErrorVisible;
    };
    const errorNodeChanged = (mutationList) => {
        toggleHelperNode(mutationList[0].target);
    };
    const init = (nodesToObserver) => {
        nodesToObserver.forEach((node) => {
            const observer = new MutationObserver(errorNodeChanged);

            observer.observe(node, observerConfig);
            observersList.push(observer);
        });
    };

    init(formErrorNodes);
})(window, window.document);
