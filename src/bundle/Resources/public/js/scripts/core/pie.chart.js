(function (global, doc, ibexa) {
    class PieChart extends ibexa.core.BaseChart {
        constructor(data) {
            super(data);

            this.type = 'pie';
        }

        getType() {
            return this.type;
        }
    }

    ibexa.addConfig('core.chart.PieChart', PieChart);
})(window, window.document, window.ibexa);
