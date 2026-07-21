declare global {
    interface Ibexa {
        modules: IbexaModules;
    }

    interface IbexaModules {
        ContentTree: typeof import('@ibexa-admin-ui-modules/content-tree/content.tree.module').default;
        MultiFileUpload: typeof import('@ibexa-admin-ui-modules/multi-file-upload/multi.file.upload.module').default;
        SubItems: typeof import('@ibexa-admin-ui-modules/sub-items/sub.items.module').default;
        UniversalDiscovery: typeof import('@ibexa-admin-ui-modules/universal-discovery/universal.discovery.module').default;
    }
}

export {};
