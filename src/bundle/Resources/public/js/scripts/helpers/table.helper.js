const { document: doc } = window;

const onChangeHandler = (activeClass, event) => {
    const { checked } = event.target;
    const action = checked ? 'add' : 'remove';
    const parentRow = event.target.closest('tr');

    parentRow.classList[action](activeClass);
};
const parseCheckbox = (checkboxSelector, activeClass) => {
    doc.querySelectorAll(checkboxSelector).forEach((checkboxNode) => {
        checkboxNode.addEventListener('change', onChangeHandler.bind(this, activeClass), false);
    });
};

export { parseCheckbox };
