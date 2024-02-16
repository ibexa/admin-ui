(function (global, doc, ibexa, ChartDataLabels) {
    const IBEXA_WHITE = '#fff';
    const IBEXA_COLOR_BASE_DARK = '#878b90';
    const dataLabelsMap = new Map();
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
                display: (context) => {
                    const { dataIndex } = context;
                    const isVisible = dataLabelsMap.get(dataIndex);

                    return isVisible;
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
            this.chartNode = data.ref;
            this.canvas = this.chartNode.querySelector('.ibexa-chart__canvas');
            this.legendNode = this.chartNode.querySelector('.ibexa-chart-legend');

            this.initialize(this.chartNode);
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

            const beforeInitPlugin = [
                {
                    beforeInit: (chart) => {
                        const { itemTemplate } = this.legendNode.dataset;
                        const fragment = doc.createDocumentFragment();
                        const data = chart.data.datasets[0];
                        data.legend.forEach((legendItem, index) => {
                            dataLabelsMap.set(index, true);
                            const container = doc.createElement('div');
                            const renderedItemTemplate = itemTemplate
                                .replace('{{ checked_color }}', data.backgroundColor[index])
                                .replace('{{ dataset_index }}', index)
                                .replace('{{ label }}', legendItem);

                            container.insertAdjacentHTML('beforeend', renderedItemTemplate);

                            const checkboxNode = container.querySelector('.ibexa-chart-legend__item-wrapper');

                            checkboxNode.querySelector('input').checked = true;
                            fragment.append(checkboxNode);
                        });

                        this.legendNode.appendChild(fragment);

                        return fragment;
                    },
                },
            ];

            this.plugins = [...beforeInitPlugin, ...doughnutPlugins, ...this.plugins];
        }

        setLegendCheckboxBackground(checkbox) {
            const { checkedColor } = checkbox.dataset;
            const { checked } = checkbox;

            if (checked) {
                checkbox.style.backgroundColor = checkedColor;
                checkbox.style.borderColor = checkedColor;
            } else {
                checkbox.style.backgroundColor = IBEXA_WHITE;
                checkbox.style.borderColor = IBEXA_COLOR_BASE_DARK;
            }
        }

        updateDataLabels(checkbox, datasetIndex) {
            const { checked } = checkbox;

            if (typeof datasetIndex !== 'number') {
                return;
            }

            if (checked) {
                dataLabelsMap.set(datasetIndex, true);
            } else {
                dataLabelsMap.set(datasetIndex, false);
            }
        }

        setLegendCheckboxes() {
            if (!this.legendNode) {
                return;
            }

            this.legendNode.querySelectorAll('.ibexa-input--legend-item-checkbox').forEach((checkbox) => {
                this.setLegendCheckboxBackground(checkbox);
                checkbox.addEventListener('change', (event) => {
                    const datasetIndex = parseInt(event.currentTarget.dataset.datasetIndex, 10);

                    this.setLegendCheckboxBackground(event.currentTarget);
                    this.updateDataLabels(event.currentTarget, datasetIndex);

                    const chartMethod = event.currentTarget.checked ? 'show' : 'hide';

                    this.chart[chartMethod](0, datasetIndex);
                });
            });
        }

        callbackAfterRender() {
            this.setLegendCheckboxes();
        }
    }

    ibexa.addConfig('core.chart.DoughnutChart', DoughnutChart);
})(window, window.document, window.ibexa, window.ChartDataLabels);
