const MIN_SEARCH_LENGTH = 3;

export const showItem = (item, filterText) => {
    if (filterText.length < MIN_SEARCH_LENGTH) {
        return true;
    }

    const itemLabelLowerCase = item.label.toLowerCase();
    const filterTextLowerCase = filterText.toLowerCase();

    return itemLabelLowerCase.indexOf(filterTextLowerCase) === 0;
};
