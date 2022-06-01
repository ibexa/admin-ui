(function (global, doc, ibexa) {
    const parseAll = () => {
        const middleEllipsisContainers = [...doc.querySelectorAll('.ibexa-middle-ellipsis')];

        middleEllipsisContainers.forEach((middleEllipsisContainer) => {
            const partStart = middleEllipsisContainer.querySelector('.ibexa-middle-ellipsis__name--start');

            middleEllipsisContainer.classList.toggle('ibexa-middle-ellipsis--ellipsized', partStart.scrollWidth > partStart.offsetWidth);
            ibexa.helpers.tooltips.parse(middleEllipsisContainer);
        });
    };

    ibexa.addConfig('helpers.ellipsis.middle', {
        parseAll,
    });
})(window, window.document, window.ibexa);
