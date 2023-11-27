import { getAdminUiConfig } from './context.helper';

const getIconPath = (path, iconSet) => {
    const adminUiConfig = getAdminUiConfig();

    if (!iconSet) {
        iconSet = adminUiConfig.iconPaths.defaultIconSet;
    }

    const iconSetPath = adminUiConfig.iconPaths.iconSets[iconSet];
    return `${iconSetPath}#${path}`;
};

export { getIconPath };
