import React from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../helpers/css.class.names';

const TableBodyCell = ({
    extraClasses = '',
    children = null,
    hasCheckbox = false,
    hasActionBtns = false,
    hasIcon = false,
    isCloseLeft = false,
    isCenterContent = false,
}) => {
    const className = createCssClassNames({
        'ibexa-table__cell': true,
        'ibexa-table__cell--has-checkbox': hasCheckbox,
        'ibexa-table__cell--has-action-btns': hasActionBtns,
        'ibexa-table__cell--has-icon': hasIcon,
        'ibexa-table__cell--close-left': isCloseLeft,
        'ibexa-table__cell--content-center': isCenterContent,
        [extraClasses]: true,
    });
    const wrapChildrenIfNeeded = (childrenToWrap) => {
        if (hasActionBtns) {
            return <div className="ibexa-table__cell-btns-wrapper">{childrenToWrap}</div>;
        }

        return childrenToWrap;
    };

    return <td className={className}>{wrapChildrenIfNeeded(children)}</td>;
};

TableBodyCell.propTypes = {
    extraClasses: PropTypes.string,
    children: PropTypes.element,
    hasCheckbox: PropTypes.bool,
    hasActionBtns: PropTypes.bool,
    hasIcon: PropTypes.bool,
    isCloseLeft: PropTypes.bool,
    isCenterContent: PropTypes.bool,
};

export default TableBodyCell;
