import React from 'react';
import PropTypes from 'prop-types';

import ContentEditButton from '../content-edit-button/content.edit.button';

const SelectedItemEditButton = ({ location, permissions }) => {
    const hasAccess = permissions && permissions.edit.hasAccess;

    return (
        <div className="c-selected-item-edit-button">
            <ContentEditButton version={location.ContentInfo.Content.CurrentVersion.Version} location={location} isDisabled={!hasAccess} />
        </div>
    );
};

SelectedItemEditButton.propTypes = {
    location: PropTypes.object.isRequired,
    permissions: PropTypes.object.isRequired,
};

export const SelectedItemEditMenuButton = {
    id: 'content-edit-button',
    priority: 30,
    component: SelectedItemEditButton,
};

export default SelectedItemEditButton;
