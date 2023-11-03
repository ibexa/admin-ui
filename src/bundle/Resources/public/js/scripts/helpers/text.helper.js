const { document: doc } = window;

const escapeHTML = (string) => {
    const stringTempNode = doc.createElement('div');

    stringTempNode.appendChild(doc.createTextNode(string));

    return stringTempNode.innerHTML;
};

export { escapeHTML };
