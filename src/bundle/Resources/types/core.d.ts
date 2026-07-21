declare global {
    interface Ibexa {
        core: IbexaCore;
    }

    type IbexaCoreWidget = new (...args: any[]) => any;

    interface IbexaCore {
        AdaptiveItems: IbexaCoreWidget;
        Backdrop: IbexaCoreWidget;
        BaseChart: IbexaCoreWidget;
        DateTimePicker: IbexaCoreWidget;
        DateTimeRangeSingle: IbexaCoreWidget;
        Draggable: IbexaCoreWidget;
        Dropdown: IbexaCoreWidget;
        MultilevelPopupMenu: IbexaCoreWidget;
        PopupMenu: IbexaCoreWidget;
        SlugValueInputAutogenerator: IbexaCoreWidget;
        SplitBtn: IbexaCoreWidget;
        Storage: IbexaCoreWidget;
        SuggestionTaggify: IbexaCoreWidget;
        Taggify: IbexaCoreWidget;
        TagViewSelect: IbexaCoreWidget;
        ToggleButton: IbexaCoreWidget;
        chart: IbexaCoreCharts;
    }

    interface IbexaCoreCharts {
        BarChart: IbexaCoreWidget;
        DoughnutChart: IbexaCoreWidget;
        LineChart: IbexaCoreWidget;
        PieChart: IbexaCoreWidget;
    }
}

export {};
