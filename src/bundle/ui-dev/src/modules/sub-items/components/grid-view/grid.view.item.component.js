import React from 'react';
import PropTypes from 'prop-types';
import Icon from '../../../common/icon/icon';
import { getContentTypeIconUrl, getContentTypeName } from '@ibexa-admin-ui-helpers/content.type.helper';

const GridViewItemComponent = ({ item, generateLink }) => {
    const { id: locationId, contentInfo, contentThumbnail, contentType } = item;
    const imageClassName = 'ibexa-grid-view-item__image';
    const contentTypeIdentifier = contentType.ContentType.identifier;
    const contentTypeIconUrl = getContentTypeIconUrl(contentTypeIdentifier);
    const contentTypeName = getContentTypeName(contentTypeIdentifier);
    let image = null;

    if (!contentThumbnail.Thumbnail || contentThumbnail.Thumbnail.mimeType === 'image/svg+xml') {
        image = (
            <div className={`${imageClassName} ${imageClassName}--none`}>
                <Icon customPath={contentTypeIconUrl} extraClasses="ibexa-icon--extra-large" />
            </div>
        );
    } else {
        const { uri, alternativeText } = contentThumbnail.Thumbnail;

        image = <img className={imageClassName} src={uri} alt={alternativeText} />;
    }

    return (
        <a className="ibexa-grid-view-item" href={generateLink(locationId, contentInfo.ContentInfo.id)}>
            <div className="ibexa-grid-view-item__image-wrapper">{image}</div>
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
