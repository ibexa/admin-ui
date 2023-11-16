(function (global, doc, ibexa) {
    const barDefaultOptions = {
        scales: {
            x: {
                display: true,
                grid: {
                    display: false,
                },
            },
        },
    };

    class BarChart extends ibexa.core.BaseChart {
        constructor(data, options = {}) {
            super(data, {
                ...barDefaultOptions,
                ...options,
            });

            this.type = 'bar';
        }

        getType() {
            return this.type;
        }
    }

    ibexa.addConfig('core.chart.BarChart', BarChart);
})(window, window.document, window.ibexa);
