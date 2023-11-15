(function (global, doc, ibexa) {
    class DoughnutChart extends ibexa.core.BaseChart {
        constructor(data, options = {}, plugins = []) {
            super(data, options, plugins);

            this.type = 'doughnut';
        }

        getType() {
            return this.type;
        }
    }

    ibexa.addConfig('core.chart.DoughnutChart', DoughnutChart);
})(window, window.document, window.ibexa);
