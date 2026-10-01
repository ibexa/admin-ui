import { escapeHTML } from './text.helper';

const safelySetInnerHTML = (node: HTMLElement, text: string): void => {
    // eslint-disable-next-line no-param-reassign
    node.innerHTML = escapeHTML(text);
};

const dangerouslySetInnerHTML = (node: HTMLElement, text: string): void => {
    // eslint-disable-next-line no-param-reassign
    node.innerHTML = text;
};

const dangerouslyInsertAdjacentHTML = (node: HTMLElement, position: InsertPosition, text: string): void => {
    const escapedText = text;

    node.insertAdjacentHTML(position, escapedText);
};

const dangerouslyAppend = (node: HTMLElement, nodeOrText: Node | string): void => {
    node.append(nodeOrText);
};

const notNullQuerySelector = function <T extends HTMLElement>(node: HTMLElement | Document, selector: string): T {
    const selectedNode = node.querySelector<T>(selector);

    if (!selectedNode) {
        throw new Error(`Element for selector '${selector}' not found!`);
    }

    return selectedNode;
};

export { safelySetInnerHTML, dangerouslySetInnerHTML, dangerouslyInsertAdjacentHTML, dangerouslyAppend, notNullQuerySelector };
