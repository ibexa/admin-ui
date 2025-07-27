import { escapeHTML } from './text.helper';

const safelySetInnerHTML = (node, text) => {
    node.innerHTML = escapeHTML(text);
};

const dangerouslySetInnerHTML = (node, text) => {
    node.innerHTML = text;
};

const dangerouslyInsertAdjacentHTML = (node, position, text) => {
    const escapedText = text;

    node.insertAdjacentHTML(position, escapedText);
};

const dangerouslyAppend = (node, nodeOrText) => {
    node.append(nodeOrText);
};

export { safelySetInnerHTML, dangerouslySetInnerHTML, dangerouslyInsertAdjacentHTML, dangerouslyAppend };
