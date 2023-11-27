import { getAdminUiConfig } from './context.helper';

const getIconPath = (path, iconSet) => {
    const adminUiConfig = getAdminUiConfig();

    if (!iconSet) {
        iconSet = adminUiConfig.defaultIconSet;
    }

    const iconSetPath = adminUiConfig.iconSets[iconSet];
    return `${iconSetPath}#${path}`;
};

export { getIconPath };
