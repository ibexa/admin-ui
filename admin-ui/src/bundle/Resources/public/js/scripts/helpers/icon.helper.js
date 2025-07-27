import { getAdminUiConfig } from './context.helper';

const getIconPath = (aliasPath, iconSet) => {
    const adminUiConfig = getAdminUiConfig();
    const { defaultIconSet, iconSets, iconAliases } = adminUiConfig.iconPaths;
    const path = iconAliases[aliasPath] || aliasPath;

    if (!iconSet) {
        iconSet = defaultIconSet;
    }

    const iconSetPath = iconSets[iconSet];

    return `${iconSetPath}#${path}`;
};

export { getIconPath };
