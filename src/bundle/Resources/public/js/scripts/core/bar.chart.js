(function(global, doc, eZ) {
    const MAX_NUMBER_OF_LABELS = 16;
    const barDefaultOptions = {

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

        setData(data) {
            super.setData(data);

            this.labelsInterval = Math.max(Math.ceil(this.labels.length / MAX_NUMBER_OF_LABELS), 1);
        }
    }

    eZ.addConfig('core.chart.BarChart', BarChart);
})(window, window.document, window.eZ, window.Chart);