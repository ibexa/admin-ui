import React from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../helpers/css.class.names';

const TableHeadCell = ({
    extraClasses = '',
    wrapperExtraClasses = '',
    children = null,
    sortColumnName = null,
    hasCheckbox = false,
    hasIcon = false,
    isCloseLeft = false,
    isCenterContent = false,
}) => {
    const className = createCssClassNames({
        'ibexa-table__header-cell': true,
        'ibexa-table__header-cell--checkbox': hasCheckbox,
        'ibexa-table__header-cell--has-icon': hasIcon,
        'ibexa-table__header-cell--close-left': isCloseLeft,
        'ibexa-table__header-cell--content-center': isCenterContent,
        [extraClasses]: true,
    });
    const cellTextWrapperClassName = createCssClassNames({
        'ibexa-table__header-cell-text-wrapper': true,
        [`ibexa-table__sort-column--${sortColumnName}`]: sortColumnName,
        [wrapperExtraClasses]: true,
    });
    const renderWrapper = (content) => {
        if (hasCheckbox) {
            return <div className="ibexa-table__header-cell-checkbox-wrapper">{content}</div>;
        }

        return <span className={cellTextWrapperClassName}>{content}</span>;
    };

    return <th className={className}>{renderWrapper(children)}</th>;
};

TableHeadCell.propTypes = {
    extraClasses: PropTypes.string,
    wrapperExtraClasses: PropTypes.string,
    children: PropTypes.element,
    sortColumnName: PropTypes.string,
    hasCheckbox: PropTypes.bool,
    hasIcon: PropTypes.bool,
    isCloseLeft: PropTypes.bool,
    isCenterContent: PropTypes.bool,
};

export default TableHeadCell;
