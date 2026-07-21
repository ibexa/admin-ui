declare global {
    interface Ibexa {
        adminUiConfig: IbexaAdminUiConfig;
    }

    interface IbexaAdminUiConfig {
        autosave: IbexaAutosaveConfig;
        backOfficeLanguage: string;
        backOfficePath: string;
        contentEditFormTemplates: IbexaContentEditFormTemplate[];
        contentTree: IbexaContentTreeConfig;
        contentTreeWidget: IbexaContentTreeWidgetConfig;
        contentTypes: Record<string, IbexaContentTypeData[]>;
        damWidget: IbexaDamWidgetConfig;
        dateFormat: IbexaDateFormatConfig;
        focusMode: boolean;
        iconPaths: IbexaIconPathsConfig;
        imageAssetMapping: IbexaImageAssetMappingConfig;
        imageVariations: Record<string, IbexaImageVariation>;
        languages: IbexaLanguagesConfig;
        locations: IbexaLocationsConfig;
        multiFileUpload: IbexaMultiFileUploadConfig;
        notifications: IbexaNotificationsConfig;
        sections: Record<string, string>;
        sortFieldMappings: Record<string, string>;
        sortOrderMappings: IbexaSortOrderMappings;
        subItems: IbexaSubItemsConfig;
        suggestions: IbexaSuggestionsConfig;
        timezone: string;
        universalDiscoveryWidget: IbexaUniversalDiscoveryWidgetConfig;
        user: IbexaAdminUiConfigUser;
        userContentTypes: string[];
        userProfile: IbexaUserProfileConfig;
    }

    interface IbexaAutosaveConfig {
        enabled: boolean;
        interval: number;
    }

    interface IbexaContentEditFormTemplate {
        template: string;
        priority: number;
    }

    interface IbexaContentTreeConfig {
        loadMoreLimit: number;
        childrenLoadMaxLimit: number;
        treeMaxDepth: number;
        allowedContentTypes: string[];
        ignoredContentTypes: string[];
        treeRootLocationId: number;
        contextualTreeRootLocationIds: Record<string, number>;
    }

    interface IbexaContentTreeWidgetConfig {
        secondaryItemActions: unknown[];
    }

    interface IbexaContentTypeData {
        id: number;
        identifier: string;
        name: string | null;
        isContainer: boolean;
        thumbnail: string;
        href: string;
        isHidden: boolean;
    }

    interface IbexaDamWidgetConfig {
        image: IbexaDamImageConfig;
        folder: IbexaDamFolderConfig;
    }

    interface IbexaDamImageConfig {
        showImageFilters: boolean;
        aggregations: Record<string, Record<string, string>>;
        enableMultipleDownload: boolean;
        mappings: Record<string, IbexaDamImageMapping>;
        contentTypeIdentifiers: string[];
        fieldDefinitionIdentifiers: string[];
    }

    interface IbexaDamImageMapping {
        imageFieldIdentifier: string;
        nameSchemaIdentifiers: string[];
    }

    interface IbexaDamFolderConfig {
        contentTypeIdentifier: string;
        nameSchemaIdentifiers: string[];
    }

    interface IbexaDateFormatConfig {
        fullDateTime: string;
        fullDate: string | null;
        fullTime: string | null;
        shortDateTime: string;
        shortDate: string | null;
        shortTime: string | null;
    }

    interface IbexaIconPathsConfig {
        iconSets: Record<string, string>;
        defaultIconSet: string;
        iconAliases: Record<string, string>;
    }

    interface IbexaImageAssetMappingConfig {
        contentTypeIdentifier: string;
        contentFieldIdentifier: string;
        nameFieldIdentifier: string;
        parentLocationId: number;
    }

    interface IbexaImageVariation {
        reference: string | null;
        filters: Array<{ name: string; params: unknown[] }>;
        [key: string]: unknown;
    }

    interface IbexaLanguagesConfig {
        mappings: Record<string, IbexaLanguage>;
        priority: string[];
    }

    interface IbexaLanguage {
        name: string;
        id: number;
        languageCode: string;
        enabled: boolean;
    }

    interface IbexaLocationsConfig {
        media: number;
        contentStructure: number;
        users: number;
    }

    interface IbexaMultiFileUploadConfig {
        locationMappings: Record<string, IbexaUploadLocationMapping>;
        defaultMappings: IbexaUploadMapping[];
        fallbackContentType: IbexaUploadContentType;
        maxFileSize: number;
    }

    interface IbexaUploadLocationMapping {
        contentTypeIdentifier: string;
        mimeTypeFilter: string[];
        mappings: IbexaUploadMapping[];
    }

    interface IbexaUploadMapping {
        mimeTypes: string[];
        contentTypeIdentifier: string;
        contentFieldIdentifier: string;
        nameFieldIdentifier: string;
        maxFileSize: number;
    }

    interface IbexaUploadContentType {
        contentTypeIdentifier: string;
        contentFieldIdentifier: string;
        nameFieldIdentifier: string;
        maxFileSize: number;
    }

    interface IbexaNotificationsConfig {
        error: IbexaNotificationSetting;
        warning: IbexaNotificationSetting;
        info: IbexaNotificationSetting;
        success: IbexaNotificationSetting;
    }

    interface IbexaNotificationSetting {
        timeout: number;
    }

    interface IbexaSortOrderMappings {
        ASC: string;
        DESC: string;
    }

    interface IbexaSubItemsConfig {
        limit: number;
    }

    interface IbexaSuggestionsConfig {
        minQueryLength: number;
        resultLimit: number;
    }

    interface IbexaUniversalDiscoveryWidgetConfig {
        startingLocationId: number | null;
        tabs: unknown[];
        topMenuActions: unknown[];
        selectedItemActions: unknown[];
        contentTypesLoaders?: unknown[];
    }

    interface IbexaAdminUiConfigUser {
        user: IbexaSerializedUser | null;
        profile_picture_field: IbexaSerializedField | null;
    }

    interface IbexaSerializedUser {
        login: string;
        email: string;
        enabled: boolean;
        [key: string]: unknown;
    }

    interface IbexaSerializedField {
        id: number | null;
        fieldDefIdentifier: string;
        value: unknown;
        languageCode: string | null;
        fieldTypeIdentifier: string;
    }

    interface IbexaUserProfileConfig {
        enabled: boolean;
        contentTypes: string[];
    }
}

export {};
