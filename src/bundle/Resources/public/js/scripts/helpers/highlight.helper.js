import { escapeHTML } from './text.helper';

const highlightText = (searchText, string, template) => {
    const stringLowerCase = string.toLowerCase();
    const searchTextLowerCase = searchText.toLowerCase();
    const matches = stringLowerCase.matchAll(searchTextLowerCase);
    const stringArray = [];
    let previousIndex = 0;

    for (const match of matches) {
        const endOfSearchTextIndex = match.index + searchText.length;
        const renderedTemplate = template.replace('{{ highlightText }}', escapeHTML(string.slice(match.index, endOfSearchTextIndex)));

        stringArray.push(escapeHTML(string.slice(previousIndex, match.index)));
        stringArray.push(renderedTemplate);

        previousIndex = match.index + searchText.length;
    }

    stringArray.push(escapeHTML(string.slice(previousIndex)));

    return stringArray.join('');
};

export { highlightText };
