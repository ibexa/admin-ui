import React from 'react';
import PropTypes from 'prop-types';

import { isExternalInstance } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { createCssClassNames } from '../helpers/css.class.names';

import UrlIcon from './urlIcon';
import InculdedIcon from './inculdedIcon';

const Icon = ({ extraClasses = '', name = null, customPath = '', useIncludedIcon = false, defaultIconName = 'about-info' }) => {
    const cssClass = createCssClassNames({
        'ibexa-icon': true,
        [extraClasses]: true,
    });

    const isIconIncluded = useIncludedIcon || isExternalInstance();

    return (
        <>
            {isIconIncluded ? (
                <InculdedIcon cssClass={cssClass} name={name} defaultIconName={defaultIconName} />
            ) : (
                <UrlIcon cssClass={cssClass} name={name} customPath={customPath} />
            )}
        </>
    );
};

Icon.propTypes = {
    extraClasses: PropTypes.string,
    name: PropTypes.string,
    customPath: PropTypes.string,
    useIncludedIcon: PropTypes.bool,
    defaultIconName: PropTypes.string,
};

export default Icon;
