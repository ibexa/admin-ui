(function(global, doc, eZ) {
    const barDefaultOptions = {
        scales: {
            xAxes: [
                {
                    display: true,
                    gridLines: {
                        display: false,
                    },
                },
            ],
        },
    };

    class BarChart extends eZ.core.BaseChart {
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

    eZ.addConfig('core.chart.BarChart', BarChart);
})(window, window.document, window.eZ);
