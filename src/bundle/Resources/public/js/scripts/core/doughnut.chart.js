(function (global, doc, ibexa) {
    class DoughnutChart extends ibexa.core.BaseChart {
        constructor(data) {
            super(data);

            this.type = 'doughnut';
        }

        getType() {
            return this.type;
        }
    }

    ibexa.addConfig('core.chart.Doughnut', DoughnutChart);
})(window, window.document, window.ibexa);
