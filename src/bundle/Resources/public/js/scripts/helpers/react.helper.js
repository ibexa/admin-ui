import { getRootDOMElement } from './context.helper';

const createDynamicRoot = ({ contextDOMElement = getRootDOMElement(), id } = {}) => {
    if (id && window.document.getElementById(id) !== null) {
        console.warn(`You're creating second root element with ID "${id}". IDs should be unique inside a document.`);
    }

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
