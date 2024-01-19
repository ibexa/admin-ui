import React, { useState } from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import { parse as parseTooltip } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/tooltips.helper';

const Collapsible = ({ isInitiallyExpanded, title, children }) => {
    const [isExpanded, setIsExpanded] = useState(isInitiallyExpanded);
    const className = createCssClassNames({
        'c-filters__collapsible': true,
        'c-filters__collapsible--hidden': !isExpanded,
    });
    const toggleCollapsed = () => setIsExpanded((prevState) => !prevState);
    const initTooltipsRef = (node) => {
        parseTooltip(node);
    };

    return (
        <div className={className} ref={initTooltipsRef}>
            <div className="c-filters__collapsible-title" onClick={toggleCollapsed}>
                {title}
            </div>
            <div className="c-filters__collapsible-content">
                <div className="c-filters__collapsible-content-wrapper">{children}</div>
            </div>
        </div>
    );
};

Collapsible.propTypes = {
    title: PropTypes.node.isRequired,
    children: PropTypes.node.isRequired,
    isInitiallyExpanded: PropTypes.bool,
};

Collapsible.defaultProps = {
    isInitiallyExpanded: false,
};

export default Collapsible;
