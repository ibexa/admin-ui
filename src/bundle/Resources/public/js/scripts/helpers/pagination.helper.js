(function (global, doc, ibexa) {
    /**
     * Computes array with pagination pages.
     *
     * Example 1: [ 1, "...", 5, 6, 7, 8, 9, 10 ] (for: proximity = 2; pagesNumber = 10; activePageIndex = 7)
     * Example 2: [ 1, "...", 3, 4, 5, 6, 7, "...", 10 ] (for: proximity = 2; pagesNumber = 10; activePageIndex = 5)
     * Example 3: [ 1, "...", 8, 9, 10, 11, 12, "...", 20 ] (for: proximity = 2; pagesNumber = 20; activePageIndex = 10)
     *
     * @param {Object} params
     * @param {Number} params.proximity
     * @param {Number} params.activePageIndex
     * @param {Number} params.pagesCount
     * @param {String} params.separator
     *
     * @returns {Array}
     */
    const computePages = ({ proximity = 2, activePageIndex, pagesCount, separator = '...' }) => {
        const pages = [];
        let wasSeparator = false;

        for (let i = 1; i <= pagesCount; i++) {
            const isFirstPage = i === 1;
            const isLastPage = i === pagesCount;
            const isInRange = i >= activePageIndex + 1 - proximity && i <= activePageIndex + 1 + proximity;

            if (isFirstPage || isLastPage || isInRange) {
                pages.push(i);
                wasSeparator = false;
            } else if (!wasSeparator) {
                pages.push(separator);
                wasSeparator = true;
            }
        }

        return pages;
    };

    ibexa.addConfig('helpers.pagination', {
        computePages,
    });
})(window, window.document, window.ibexa);
