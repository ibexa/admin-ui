declare global {
    interface Ibexa {
        modules: IbexaModules;
    }

    interface IbexaModules {
        ContentTree: import('react').ComponentType<IbexaContentTreeProps>;
        MultiFileUpload: typeof import('@ibexa-admin-ui-modules/multi-file-upload/multi.file.upload.module').default;
        SubItems: typeof import('@ibexa-admin-ui-modules/sub-items/sub.items.module').default;
        UniversalDiscovery: typeof import('@ibexa-admin-ui-modules/universal-discovery/universal.discovery.module').default;
    }

    interface IbexaContentTreeProps {
        currentLocationPath: string;
        userId: number;
        restInfo: IbexaContentTreeRestInfo;
        rootLocationId?: number;
    }

    interface IbexaContentTreeRestInfo {
        token: string;
        siteaccess: string;
    }
}

export {};
