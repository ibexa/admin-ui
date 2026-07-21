declare global {
    interface Ibexa {
        fieldTypeValidators: IbexaBaseFieldValidator[];
        BaseFieldValidator: IbexaBaseFieldValidatorConstructor;
        BaseFileFieldValidator: IbexaBaseFileFieldValidatorConstructor;
        BasePreviewField: IbexaBasePreviewFieldConstructor;
        MultiInputFieldValidator: IbexaMultiInputFieldValidatorConstructor;
    }

    interface IbexaFieldValidatorConfig {
        classInvalid: string;
        eventsMap: unknown[];
        fieldSelector: string;
        fieldContainer?: HTMLElement;
        [key: string]: unknown;
    }

    interface IbexaValidationResult {
        isError: boolean;
        errorMessage?: string;
    }

    interface IbexaBaseFieldValidator {
        fieldsToValidate: unknown[];
        init(): void;
        reinit(): void;
        isValid(): boolean;
        cancelErrors(): IbexaValidationResult;
        validateField(config: unknown, event: Event): IbexaValidationResult;
        toggleInvalidState(isError: boolean, config: unknown, input: HTMLElement): void;
        [key: string]: unknown;
    }

    interface IbexaBaseFieldValidatorConstructor {
        new (config: IbexaFieldValidatorConfig): IbexaBaseFieldValidator;
    }

    interface IbexaBaseFileFieldValidator extends IbexaBaseFieldValidator {
        validateInput(event: Event): IbexaValidationResult;
        validateFileSize(): IbexaValidationResult;
        showFileSizeError(): IbexaValidationResult;
        showFileTypeError(): IbexaValidationResult;
    }

    interface IbexaBaseFileFieldValidatorConstructor {
        new (config: IbexaFieldValidatorConfig): IbexaBaseFileFieldValidator;
    }

    interface IbexaMultiInputFieldValidator extends IbexaBaseFieldValidator {
        containerSelectors: string[];
    }

    interface IbexaMultiInputFieldValidatorConstructor {
        new (
            config: IbexaFieldValidatorConfig & { containerSelectors: string[] },
        ): IbexaMultiInputFieldValidator;
    }

    interface IbexaBasePreviewFieldConfig {
        fieldContainer: HTMLElement;
        allowedFileTypes: string;
        fileTypeAccept: string;
        validator: IbexaBaseFieldValidator;
    }

    interface IbexaBasePreviewField {
        init(): void;
        showPreview(event?: Event): void;
        hidePreview(): void;
        resetInputField(): void;
        checkCanDrop(file: File): boolean;
        [key: string]: unknown;
    }

    interface IbexaBasePreviewFieldConstructor {
        new (config: IbexaBasePreviewFieldConfig): IbexaBasePreviewField;
    }
}

export {};
