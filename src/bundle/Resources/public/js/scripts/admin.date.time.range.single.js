(function (global, doc, ibexa) {
    const { DateTimeRangeSingle } = ibexa.core;
    const containers = doc.querySelectorAll('.ibexa-date-time-range-single');

    containers.forEach((container) => {
        const dateTimeRangeSingle = new DateTimeRangeSingle({
            container,
        });

        dateTimeRangeSingle.init();
    });
})(window, window.document, window.ibexa);
