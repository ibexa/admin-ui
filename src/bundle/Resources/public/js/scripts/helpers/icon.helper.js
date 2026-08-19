import { getAdminUiConfig } from './context.helper';

const getIconPath = (iconName, iconSet, useLegacyNames = true) => {
    const adminUiConfig = getAdminUiConfig();
    const { defaultIconSet, iconSets, iconAliases } = adminUiConfig.iconPaths;
    const path = useLegacyNames ? iconAliases[iconName] || iconName : iconName;

    if (!iconSet) {
        iconSet = defaultIconSet;
    }

    const iconSetPath = iconSets[iconSet];

    return `${iconSetPath}#${path}`;
};

export { getIconPath };
