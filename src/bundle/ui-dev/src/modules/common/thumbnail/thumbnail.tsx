import React from 'react';
import Icon from '../icon/icon';

interface ThumbnailProps {
    thumbnailData: {
        mimeType: string;
        resource: string;
    };
    iconExtraClasses?: string;
    contentTypeIconPath?: string;
}

const Thumbnail = ({ thumbnailData, iconExtraClasses, contentTypeIconPath }: ThumbnailProps) => {
    const renderContentTypeIcon = (): JSX.Element | null => {
        if (!contentTypeIconPath) {
            return null;
        }

        return (
            <div className="c-thumbnail__icon-wrapper">
                <Icon extraClasses="ibexa-icon--small" customPath={contentTypeIconPath} />
            </div>
        );
    };

    if (thumbnailData.mimeType === 'image/svg+xml') {
        return (
            <div className="c-thumbnail">
                <Icon extraClasses={iconExtraClasses} customPath={thumbnailData.resource} />
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
