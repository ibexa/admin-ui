import React from 'react';
import PropTypes from 'prop-types';
import Icon from '../../../common/icon/icon';
import { createCssClassNames } from '../../../common/helpers/css.class.names';

import { getContentTypeIconUrl, getContentTypeName } from '@ibexa-admin-ui-helpers/content.type.helper';

const GridViewItemComponent = ({ item, generateLink }) => {
    const { id: locationId, contentInfo, contentThumbnail, contentType } = item;
    const contentTypeIdentifier = contentType.ContentType.identifier;
    const contentTypeIconUrl = getContentTypeIconUrl(contentTypeIdentifier);
    const contentTypeName = getContentTypeName(contentTypeIdentifier);
    const isSVGThumbnail = !contentThumbnail.Thumbnail || contentThumbnail.Thumbnail.mimeType === 'image/svg+xml';
    const thumbnailClassName = createCssClassNames({
        'ibexa-grid-view-item__image': true,
        'ibexa-grid-view-item__image--none': isSVGThumbnail,
    });
    const renderThumbnail = () => {
        if (isSVGThumbnail) {
            return (
                <div className={thumbnailClassName}>
                    <Icon customPath={contentTypeIconUrl} extraClasses="ibexa-icon--extra-large" />
                </div>
            );
        }

        return <img className={contentThumbnail.Thumbnail.uri} src={uri} alt={contentInfo.ContentInfo.name} />;
    }

    return (
        <a className="ibexa-grid-view-item" href={generateLink(locationId, contentInfo.ContentInfo.id)}>
            <div className="ibexa-grid-view-item__image-wrapper">{renderThumbnail()}</div>
            <div className="ibexa-grid-view-item__footer">
                <div className="ibexa-grid-view-item__title" title={contentInfo.ContentInfo.name}>
                    {contentInfo.ContentInfo.name}
                </div>
                <div className="ibexa-grid-view-item__detail-a">
                    <Icon customPath={contentTypeIconUrl} extraClasses="ibexa-icon--small" />
                    {contentTypeName}
                </div>
            </div>
        </a>
    );
};

GridViewItemComponent.propTypes = {
    item: PropTypes.object.isRequired,
    generateLink: PropTypes.func.isRequired,
};

export default GridViewItemComponent;
