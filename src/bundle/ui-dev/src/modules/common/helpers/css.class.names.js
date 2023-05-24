export const createCssClassNames = (classes) => {
    if (Object.prototype.toString.call(classes) !== '[object Object]') {
        return '';
    }

    return Object.entries(classes)
        .reduce((total, [name, condition]) => {
            if (condition) {
                return `${total} ${name}`;
            }

            return total;
        }, '')
        .trim();
};
