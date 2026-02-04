const { document: doc } = window;

const escapeHTML = (str: string): string => {
    const stringTempNode = doc.createElement('div');

    stringTempNode.appendChild(doc.createTextNode(str));

    return stringTempNode.innerHTML;
};

const escapeHTMLAttribute = (str: string | null): string => {
    if (str === null) {
        return '';
    }

    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
};

export { escapeHTML, escapeHTMLAttribute };
