import { getAdminUiConfig, getRestInfo } from './context.helper';

const getIconPath = (path, iconSet) => {
    const { instanceUrl } = getRestInfo();
    const adminUiConfig = getAdminUiConfig();

    if (window.origin !== instanceUrl) {
        return `/ibexa-icons.svg#${path}`;
    }

    if (!iconSet) {
        iconSet = adminUiConfig.iconPaths.defaultIconSet;
    }

    const iconSetPath = adminUiConfig.iconPaths.iconSets[iconSet];

    return `${iconSetPath}#${path}`;
};

export { getIconPath };
