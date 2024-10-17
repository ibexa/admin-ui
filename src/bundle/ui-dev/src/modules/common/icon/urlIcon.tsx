import React from 'react';
import { getIconPath } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/icon.helper';

interface UrlIconProps {
    cssClass?: string;
    name?: string | null;
    customPath?: string | null;
}

const UrlIcon = ({ cssClass = '', name = null, customPath = null }: UrlIconProps) => {
    const linkHref = customPath ?? getIconPath(name);

    return (
        <svg className={cssClass}>
            <use xlinkHref={linkHref} />
        </svg>
    );
};

export default UrlIcon;
