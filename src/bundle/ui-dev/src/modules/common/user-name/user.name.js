import React from 'react';
import PropTypes from 'prop-types';

import Thumbnail from '../thumbnail/thumbnail';
const { Routing, ibexa } = window;

const isUserProfileEnabled = (contentTypeIdentifier) => {
    const config = ibexa.adminUiConfig.userProfile;

    if (config.enabled) {
        return config.contentTypes.includes(contentTypeIdentifier);
    }

    return false;
};

const UserName = ({ userId, name, thumbnail, contentTypeIdentifier }) => {
    if (isUserProfileEnabled(contentTypeIdentifier)) {
        const profileUrl = Routing.generate('ibexa.user.profile.view', { userId: userId });

        return (
            <a href={profileUrl} className="c-user-name ibexa-user-name">
                <span className="c-user-name__thumbnail ibexa-user-name__thumbnail">
                    <Thumbnail
                        thumbnailData={{ mimeType: thumbnail.mimeType, resource: thumbnail.uri }}
                        iconExtraClasses="ibexa-icon--small-medium"
                    />
                </span>
                <span className="c-user-name__text ibexa-user-name__text">{name}</span>
            </a>
        );
    }

    return (
        <div className="c-user-name ibexa-user-name">
            <span className="c-user-name__thumbnail ibexa-user-name__thumbnail">
                <Thumbnail
                    thumbnailData={{ mimeType: thumbnail.mimeType, resource: thumbnail.uri }}
                    iconExtraClasses="ibexa-icon--small-medium"
                />
            </span>
            <span className="c-user-name__text ibexa-user-name__text">{name}</span>
        </div>
    );
};

UserName.propTypes = {
    userId: PropTypes.string.isRequired,
    name: PropTypes.string.isRequired,
    thumbnail: PropTypes.shape({
        mimeType: PropTypes.string.isRequired,
        uri: PropTypes.string.isRequired,
    }).isRequired,
    contentTypeIdentifier: PropTypes.string.isRequired,
};

export default UserName;
