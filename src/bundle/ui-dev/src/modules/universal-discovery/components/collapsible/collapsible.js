import React, { useState } from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import { parse as parseTooltip } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/tooltips.helper';

const Collapsible = ({ isInitiallyExpanded = false, title, children }) => {
    const [isExpanded, setIsExpanded] = useState(isInitiallyExpanded);
    const className = createCssClassNames({
        'c-collapsible': true,
        'c-collapsible--hidden': !isExpanded,
    });
    const toggleCollapsed = () => setIsExpanded((prevState) => !prevState);
    const initTooltipsRef = (node) => {
        parseTooltip(node);
    };

    return (
        <div className={className} ref={initTooltipsRef}>
            <div className="c-collapsible__title" onClick={toggleCollapsed}>
                {title}
            </div>
            <div className="c-collapsible__content">
                <div className="c-collapsible__content-wrapper">{children}</div>
            </div>
        </div>
    );
};

Collapsible.propTypes = {
    title: PropTypes.node.isRequired,
    children: PropTypes.node.isRequired,
    isInitiallyExpanded: PropTypes.bool,
};

export default Collapsible;
