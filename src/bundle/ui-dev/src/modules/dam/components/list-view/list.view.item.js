import React from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import SelectionToggler from '../selection.toggler/selection.toggler';
import Thumbnail from '../../../common/thumbnail/thumbnail';

const { ibexa, Translator } = window;
const { formatShortDateTime } = ibexa.helpers.timezone;

const ListViewItem = ({ item }) => {
    const className = createCssClassNames({
        'ibexa-table__row ibexa-table__row--selectable': true,
        'ibexa-table__row--selected': item.isSelected,
    });

    return (
        <tr className={className} onClick={item.onClick}>
            <td className="ibexa-table__cell ibexa-table__cell--has-icon">
                <SelectionToggler itemId={item.id} isSelected={item.isSelected} />
            </td>
            <td className="ibexa-table__cell">
                <div className="ibexa-table__thumbnail">
                {/* //src={item.thumbnail} alt="" /> */}
                <Thumbnail
                    thumbnailData={item.thumbnail}
                    iconExtraClasses="ibexa-icon--extra-large"
                    contentTypeIconPath={item.iconPath}
                />
                </div>
            </td>
            <td className="ibexa-table__cell">{item.name}</td>
            <td className="ibexa-table__cell">{item.fileFormat}</td>
            <td className="ibexa-table__cell">{item.size}</td>
            <td className="ibexa-table__cell">
                {item.dimensionX} x {item.dimensionY}
            </td>
            <td className="ibexa-table__cell">{formatShortDateTime(item.created)}</td>
            <td className="ibexa-table__cell">{formatShortDateTime(item.updated)}</td>
        </tr>
    );
};

ListViewItem.propTypes = {
    item: PropTypes.object.isRequired,
};

ListViewItem.defaultProps = {};

export default ListViewItem;
