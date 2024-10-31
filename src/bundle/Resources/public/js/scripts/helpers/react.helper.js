const createDynamicRoot = (contextDOMElement = window.document.body, id) => {
    const rootDOMElement = document.createElement('div');

    rootDOMElement.classList.add('ibexa-react-root');

    if (id) {
        rootDOMElement.id = id;
    }

    contextDOMElement.appendChild(rootDOMElement);

    const reactRoot = window.ReactDOM.createRoot(rootDOMElement);

    return reactRoot;
};

const removeDynamicRoot = (reactRoot) => {
    const rootDOMElement = reactRoot._internalRoot?.containerInfo;

    reactRoot.unmount();
    rootDOMElement?.remove();
};

export { createDynamicRoot, removeDynamicRoot };
