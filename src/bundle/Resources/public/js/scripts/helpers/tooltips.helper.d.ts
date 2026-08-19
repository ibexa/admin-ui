interface TooltipsDefaultsParams {
    delay: {
        show: number;
        hide: number;
    };
    placement: string;
    trigger: string;
    useHtml: boolean;
    template: (extraClass?: string) => string;
}

declare const TOOLTIPS_DEFAULTS_PARAMS: TooltipsDefaultsParams;

declare const parse: (baseElement?: HTMLElement | Document) => void;
declare const hideAll: (baseElement?: HTMLElement | Document) => void;
declare const observe: (baseElement?: HTMLElement | Document) => void;

export { parse, hideAll, observe, TOOLTIPS_DEFAULTS_PARAMS };
