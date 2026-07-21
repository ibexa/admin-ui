declare global {
    interface Ibexa {
        iconPaths?: IbexaIconPathsConfig;
        richText: IbexaRichText;
        autocomplete: IbexaAutocomplete;
    }

    interface IbexaRichText {
        alloyEditor: {
            callbacks: {
                selectContent: (config: IbexaSelectContentConfig) => void;
            };
        };
    }

    interface IbexaSelectContentConfig {
        onConfirm?: (...args: unknown[]) => void;
        onCancel?: (...args: unknown[]) => void;
        onItemsConfirm?: (...args: unknown[]) => void;
        [key: string]: unknown;
    }

    interface IbexaAutocomplete {
        renderers: {
            content: IbexaAutocompleteContentRenderer;
        };
    }

    type IbexaAutocompleteContentRenderer = (
        result: IbexaAutocompleteResult,
        searchText: string,
    ) => string;

    interface IbexaAutocompleteResult {
        locationId: number;
        contentId: number;
        name: string;
        contentTypeIdentifier: string;
        pathString: string;
        parentLocations: Array<{ locationId: number; name: string }>;
    }
}

export {};
