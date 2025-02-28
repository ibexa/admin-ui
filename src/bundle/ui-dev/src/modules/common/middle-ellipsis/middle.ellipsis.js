import React from 'react';
import PropTypes from 'prop-types';

import { parse as parseMiddleEllipsis } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/middle.ellipsis';

const MiddleEllipsis = ({ name }) => {
    return (
        <span className="ibexa-middle-ellipsis" title={name} ref={(node) => parseMiddleEllipsis(node)}>
            <span className="ibexa-middle-ellipsis__name ibexa-middle-ellipsis__name--start">
                <span className="ibexa-middle-ellipsis__name-ellipsized">{name}</span>
            </span>
            <span className="ibexa-middle-ellipsis__separator">...</span>
            <span className="ibexa-middle-ellipsis__name ibexa-middle-ellipsis__name--end">
                <span className="ibexa-middle-ellipsis__name-ellipsized">{name}</span>
            </span>
        </span>
    );
};

MiddleEllipsis.propTypes = {
    name: PropTypes.string.isRequired,
};

export default MiddleEllipsis;
