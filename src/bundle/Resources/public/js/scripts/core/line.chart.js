(function (global, doc, ibexa) {
    const MAX_NUMBER_OF_LABELS = 16;
    const lineDefaultOptions = {
        elements: {
            point: {
                radius: 2,
            },
        },
        scales: {
            x: {
                display: true,
                grid: {
                    display: false,
                },
                ticks: {
                    maxRotation: 0,
                    autoSkip: false,
                    callback: function (value, index, ticks) {
                        const label = this.getLabelForValue(value);
                        const labelsInterval = Math.max(Math.ceil(ticks.length / MAX_NUMBER_OF_LABELS), 1);
                        const shouldDisplayLabel = !(index % labelsInterval);

                        return shouldDisplayLabel ? label : null;
                    },
                },
            },
            y: {
                display: true,
            },
        },
    };

    class LineChart extends ibexa.core.BaseChart {
        constructor(data, options = {}) {
            super(data, {
                ...lineDefaultOptions,
                ...options,
            });

            this.type = 'line';
        }

        getType() {
            return this.type;
        }

        setData(data) {
            super.setData(data);

            this.labelsInterval = Math.max(Math.ceil(this.labels.length / MAX_NUMBER_OF_LABELS), 1);
        }
    }

    ibexa.addConfig('core.chart.LineChart', LineChart);
})(window, window.document, window.ibexa);
