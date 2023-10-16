import React, { useContext } from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../../../common/helpers/css.class.names';

const ListViewHeader = () => {
    return (
        <thead>
            <tr className="ibexa-table__head-row">
                <th className="ibexa-table__header-cell">
                    {/* <input className="ibexa-input ibexa-input--radio" type="radio" /> */}
                </th>
                <th className="ibexa-table__header-cell">
                    <span className="ibexa-table__header-cell-text-wrapper">Thumbnail</span>
                </th>
                <th className="ibexa-table__header-cell">
                    <span className="ibexa-table__header-cell-text-wrapper">Name</span>
                </th>
                <th className="ibexa-table__header-cell">
                    <span className="ibexa-table__header-cell-text-wrapper">File format</span>
                </th>
                <th className="ibexa-table__header-cell">
                    <span className="ibexa-table__header-cell-text-wrapper">Size</span>
                </th>
                <th className="ibexa-table__header-cell">
                    <span className="ibexa-table__header-cell-text-wrapper">Dimensions</span>
                </th>
                <th className="ibexa-table__header-cell">
                    <span className="ibexa-table__header-cell-text-wrapper">Created</span>
                </th>
                <th className="ibexa-table__header-cell">
                    <span className="ibexa-table__header-cell-text-wrapper">Updated</span>
                </th>
            </tr>
        </thead>
    );
};

ListViewHeader.propTypes = {
    location: PropTypes.object.isRequired,
    version: PropTypes.object,
};

ListViewHeader.defaultProps = {
    version: {},
};

export default ListViewHeader;
