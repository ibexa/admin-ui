import React, { useContext } from 'react';
import PropTypes from 'prop-types';
import SelectionToggler from '../selection.toggler/selection.toggler';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import Thumbnail from '../../../common/thumbnail/thumbnail';

const GridViewItem = ({ itemId, thumbnail, iconPath, title, detailA, detailB, isSelected, onClick, onDoubleClick }) => {
    // ibexa-grid-view-item__image ibexa-grid-view-item__image--none
    const className = createCssClassNames({
        'ibexa-grid-view-item': true,
        'ibexa-grid-view-item--selected': isSelected,
    });

    return (
        <div className={className} onClick={onClick} onDoubleClick={onDoubleClick}>
            <div className="ibexa-grid-view-item__image-wrapper">
                <Thumbnail
                    thumbnailData={thumbnail}
                    iconExtraClasses="ibexa-icon--extra-large"
                    contentTypeIconPath={iconPath}
                />
            </div>
            <div className="ibexa-grid-view-item__footer">
                <div className="ibexa-grid-view-item__title" title={title}>
                    {title}
                </div>
                {detailA && <div className="ibexa-grid-view-item__detail-a">{ detailA }</div>}
                {detailB && <div className="ibexa-grid-view-item__detail-b">{ detailB }</div>}
            </div>
            <div className="ibexa-grid-view-item__checkbox">
                <SelectionToggler itemId={itemId} isSelected={isSelected} isHidden={false} />
            </div>
        </div>
    );
};

GridViewItem.propTypes = {
    itemId: PropTypes.any.isRequired,
    thumbnail: PropTypes.object.isRequired,
    iconPath: PropTypes.string.isRequired,
    title: PropTypes.object.isRequired,
    detailA: PropTypes.object,
    detailB: PropTypes.object,
    isSelected: PropTypes.bool,
    onClick: PropTypes.func,
    onDoubleClick: PropTypes.func,
};

GridViewItem.defaultProps = {
    detailA: null,
    detailB: null,
    isSelected: false,
    onClick: () => {},
    onDoubleClick: () => {},
};

export default GridViewItem;
