(function (global, doc) {
    const SELECTOR_FILTER = '.ibexa-sidebar-filter-range';
    const filtersNode = doc.querySelectorAll(SELECTOR_FILTER);

    class RangeFilter extends global.ibexa.BaseSidebarFilter {
        constructor(config) {
            super(config);

            this.minValue = parseInt(this.wrapper.dataset.minValue, 10);
            this.maxValue = parseInt(this.wrapper.dataset.maxValue, 10);
            this.step = parseInt(this.wrapper.dataset.step, 10);

            this.startRangeValue = this.minValue;
            this.endRangeValue = this.maxValue;

            this.clearAllBtn = this.wrapper.querySelector('.ibexa-sidebar-filter__clear-all-btn');
            this.sliderInpustWrapper = this.wrapper.querySelector('.ibexa-sidebar-filter-range__slider-inputs');
            this.sliderNode = this.wrapper.querySelector('.ibexa-sidebar-filter-range__slider--range');
            this.selectedSliderRangeNode = this.wrapper.querySelector('.ibexa-sidebar-filter-range__slider--seleted-range');
            this.sliderInputEnd = this.wrapper.querySelector('.ibexa-sidebar-filter-range__slider-input--end');
            this.sliderInputStart = this.wrapper.querySelector('.ibexa-sidebar-filter-range__slider-input--start');
            this.sliderInputEnd = this.wrapper.querySelector('.ibexa-sidebar-filter-range__slider-input--end');
            this.manualInputStart = this.wrapper.querySelector('.ibexa-sidebar-filter-range__manual-input--start');
            this.manualInputEnd = this.wrapper.querySelector('.ibexa-sidebar-filter-range__manual-input--end');

            this.init = this.init.bind(this);
            this.clearFilter = this.clearFilter.bind(this);
            this.startSliderRangeMoving = this.startSliderRangeMoving.bind(this);
            this.endSliderRangeMoving = this.endSliderRangeMoving.bind(this);
            this.setStartSliderValue = this.setStartSliderValue.bind(this);
            this.setEndSliderValue = this.setEndSliderValue.bind(this);
            this.setSelectedRangeCords = this.setSelectedRangeCords.bind(this);
        }

        startSliderRangeMoving(event) {
            this.startRangeValue = parseInt(this.sliderInputStart.value);
            this.endRangeValue = parseInt(this.sliderInputEnd.value);

            if (this.startRangeValue > this.endRangeValue) {
                this.sliderInputEnd.value = this.startRangeValue;
                this.sliderInputEnd.dispatchEvent(new Event('input'));
            }

            this.manualInputStart.value = this.sliderInputStart.value;
            this.setSelectedRangeCords();
        }

        endSliderRangeMoving() {
            this.startRangeValue = parseInt(this.sliderInputStart.value);
            this.endRangeValue = parseInt(this.sliderInputEnd.value);

            if (this.endRangeValue < this.startRangeValue) {
                this.sliderInputStart.value = this.endRangeValue;
                this.sliderInputStart.dispatchEvent(new Event('input'));
            }

            this.manualInputEnd.value = this.sliderInputEnd.value;
            this.setSelectedRangeCords();
        }

        setStartSliderValue(manualInputValue) {
            this.sliderInputStart.value = manualInputValue ?? this.manualInputStart.value;
            this.sliderInputStart.dispatchEvent(new Event('input'));
            this.setSelectedRangeCords();
        }

        setEndSliderValue(manualInputValue) {
            this.sliderInputEnd.value = manualInputValue ?? this.manualInputEnd.value;
            this.sliderInputEnd.dispatchEvent(new Event('input'));
            this.setSelectedRangeCords();
        }

        setSelectedRangeCords() {
            const startDotPositionPercent = ((this.startRangeValue - this.minValue) * 100) / (this.maxValue - this.minValue);
            const endDotPositionPercent = ((this.endRangeValue - this.minValue) * 100) / (this.maxValue - this.minValue);
            const rangeSliderWidth = endDotPositionPercent - startDotPositionPercent;

            this.selectedSliderRangeNode.style.width = `${rangeSliderWidth}%`;
            this.selectedSliderRangeNode.style.left = `${startDotPositionPercent}%`;
        }

        clearFilter() {
            this.manualInputStart.value = this.minValue;
            this.manualInputEnd.value = this.maxValue;
            this.setStartSliderValue(this.minValue);
            this.setEndSliderValue(this.maxValue)
        }

        init() {
            super.init();

            const sliderInputsWrapperWidth = this.sliderInpustWrapper.offsetWidth;

            this.clearAllBtn.addEventListener('click', this.clearFilter, false);
            this.sliderInputStart.addEventListener('input', this.startSliderRangeMoving, false);
            this.sliderInputEnd.addEventListener('input', this.endSliderRangeMoving, false);
            this.manualInputStart.addEventListener('input', () => this.setStartSliderValue(), false);
            this.manualInputEnd.addEventListener('input', () => this.setEndSliderValue(), false);

            this.sliderInputStart.style.width = `${sliderInputsWrapperWidth}px`;
            this.sliderInputEnd.style.width = `${sliderInputsWrapperWidth}px`;
            this.sliderNode.style.width = `${sliderInputsWrapperWidth}px`;

            this.setSelectedRangeCords();
        }
    }

    filtersNode.forEach((filterNode) => {
        const filter = new RangeFilter({
            wrapper: filterNode
        });

        filter.init();
    });
})(window, window.document);
