const createDynamicRoot = (contextDOMElement = window.document.body, id) => {
    const rootDOMElement = document.createElement('div');

    rootDOMElement.classList.add('ibexa-react-root');

    if (id) {
        rootDOMElement.id = id;
    }

    contextDOMElement.appendChild(rootDOMElement);

    const reactRoot = window.ReactDOM.createRoot(rootDOMElement);

    return { reactRoot, rootDOMElement };
};

const removeDynamicRoot = (rootDOMElement) => {
    rootDOMElement.remove();
};

export { createDynamicRoot, removeDynamicRoot };
