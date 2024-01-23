(function (global, doc, ibexa, ChartDataLabels) {
    const doughnutOptions = {
        plugins: {
            datalabels: {
                color: '#FFFFFF',
                font: {
                    weight: 'bold',
                    size: 18,
                },
                formatter: (value, context) => {
                    const sum = context.dataset.data.reduce((acc, curValue) => acc + curValue, 0);
                    const percentage = (value / sum) * 100;

                    return `${Math.floor(percentage)}%`;
                },
            },
        },
    };
    const doughnutPlugins = [ChartDataLabels];

    class DoughnutChart extends ibexa.core.BaseChart {
        constructor(data, options = {}, plugins = []) {
            super(data, options, plugins);

            this.setOptions(options);
            this.type = 'doughnut';

            this.initialize(data.ref);
        }

        initialize(ref) {
            ref.classList.add('ibexa-chart--doughnut');
        }

        getType() {
            return this.type;
        }

        setOptions(options) {
            super.setOptions(options);

            this.options.plugins = {
                ...this.options.plugins,
                ...doughnutOptions.plugins,
            };
        }

        setPlugins(plugins) {
            super.setPlugins(plugins);

            this.plugins = [...doughnutPlugins, ...this.plugins];
        }
    }

    ibexa.addConfig('core.chart.DoughnutChart', DoughnutChart);
})(window, window.document, window.ibexa, window.ChartDataLabels);
