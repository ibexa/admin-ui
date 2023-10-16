import React, { useState } from 'react';

import { createCssClassNames } from '../../../common/helpers/css.class.names';

const FiltersGroup = ({ isInitiallyExpanded, title, children }) => {
    const [isExpanded, setIsExpanded] = useState(isInitiallyExpanded);
    const className = createCssClassNames({
        'c-dwb-filters__collapsible': true,
        'c-dwb-filters__collapsible--hidden': !isExpanded,
    });
    const toggleCollapsed = () => setIsExpanded((prevState) => !prevState);

    return (
        <div className={className}>
            <div className="c-dwb-filters__collapsible-title" onClick={toggleCollapsed}>
                {title}
            </div>
            <div className="c-dwb-filters__collapsible-content">
                <div className="c-dwb-filters__collapsible-content-wrapper">{children}</div>
            </div>
        </div>
    );
};

FiltersGroup.propTypes = {
    title: PropTypes.node.isRequired,
    children: PropTypes.node.isRequired,
    isInitiallyExpanded: PropTypes.bool,
};

FiltersGroup.defaultProps = {
    isInitiallyExpanded: false,
};

export default FiltersGroup;
