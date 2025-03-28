import React from 'react';

import Icon from '../icon/icon';
import ThumbnailProps from './thumbnail.types';

const Thumbnail = ({ thumbnailData, iconExtraClasses, contentTypeIconPath }: ThumbnailProps) => {
    const renderContentTypeIcon = (): React.JSX.Element | null => {
        if (!contentTypeIconPath) {
            return null;
        }

        return (
            <div className="c-thumbnail__icon-wrapper">
                <Icon customPath={contentTypeIconPath} extraClasses="ibexa-icon--small" />
            </div>
        );
    };

    if (thumbnailData.mimeType === 'image/svg+xml') {
        return (
            <div className="c-thumbnail">
                <Icon customPath={thumbnailData.resource} extraClasses={iconExtraClasses} />
            </div>
        );
    }

    return (
        <div className="c-thumbnail">
            {renderContentTypeIcon()}
            <img className="c-thumbnail__image" src={thumbnailData.resource} />
        </div>
    );
};

export default Thumbnail;
