import React from 'react';
import PropTypes from 'prop-types';

import Thumbnail from '../thumbnail/thumbnail';

const UserName = ({ name, thumbnail }) => {
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
    name: PropTypes.string.isRequired,
    thumbnail: PropTypes.shape({
        mimeType: PropTypes.string.isRequired,
        uri: PropTypes.string.isRequired,
    }).isRequired,
};

export default UserName;
