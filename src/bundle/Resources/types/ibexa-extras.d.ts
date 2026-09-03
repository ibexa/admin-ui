declare global {
    interface Ibexa {
        iconPaths?: IbexaIconPathsConfig;
        richText: IbexaRichText;
        autocomplete: IbexaAutocomplete;
        quickAction: IbexaQuickAction;
        errors: IbexaErrors;
    }

    interface IbexaQuickAction {
        registerButton(config: IbexaQuickActionButtonConfig): void;
        unregisterButton(id: string): void;
        recalculateButtonsLayout(): void;
    }

    interface IbexaQuickActionButtonConfig {
        id: string;
        container: HTMLElement;
        priority: number;
        zIndex?: number;
        checkVisibility?: () => boolean;
        extraBottomPadding?: number;
        [key: string]: unknown;
    }

    interface IbexaErrors {
        emailRegexp: RegExp;
        urlRegexp: RegExp;
        emptyField: string;
        invalidEmail: string;
        invalidUrl: string;
        tooLong: string;
        tooShort: string;
        isNotInteger: string;
        isNotFloat: string;
        isLess: string;
        isGreater: string;
        invalidFileSize: string;
        invalidFileType: string;
        provideLatitudeValue: string;
        provideLongitudeValue: string;
        addressNotFound: string;
        notSamePasswords: string;
        invalidValue: string;
        outOfRangeValue: string;
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

    type IbexaAutocompleteContentRenderer = (result: IbexaAutocompleteResult, searchText: string) => string;

    interface IbexaAutocompleteResult {
        locationId: number;
        contentId: number;
        name: string;
        contentTypeIdentifier: string;
        pathString: string;
        parentLocations: { locationId: number; name: string }[];
    }
}

export {};
