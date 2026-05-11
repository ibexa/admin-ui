import React, { useMemo } from 'react';
import PropTypes from 'prop-types';

import { AssetsProvider, TranslatorProvider } from '@ids-components/context';

import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { getIconPath } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/icon.helper';

const DesignSystemProvider = ({ children }) => {
    const Translator = getTranslator();
    const assetsContextValue = useMemo(
        () => ({
            getIconPath: (iconName, iconSet) => getIconPath(iconName, iconSet, false),
        }),
        [],
    );
    const translatorContextValue = useMemo(
        () => ({
            trans: (key, parameters, domain) => Translator.trans(key, parameters ?? {}, domain),
        }),
        [Translator],
    );

    return (
        <AssetsProvider value={assetsContextValue}>
            <TranslatorProvider value={translatorContextValue}>{children}</TranslatorProvider>
        </AssetsProvider>
    );
};

DesignSystemProvider.propTypes = {
    children: PropTypes.node.isRequired,
};

export default DesignSystemProvider;
