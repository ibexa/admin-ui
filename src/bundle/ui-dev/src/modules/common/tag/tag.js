import React from 'react';
import PropTypes from 'prop-types';
import Icon from '../icon/icon';
import { createCssClassNames } from '../helpers/css.class.names';

const Tag = ({ content, onRemove = () => {}, isDeletable = true, extraClasses = '' }) => {
    const className = createCssClassNames({
        'ids-chip': true,
        'ibexa-tag': true,
        'ibexa-tag--deletable': isDeletable,
        [extraClasses]: true,
    });

    return (
        <div className={className}>
            <div className="ids-chip__content ibexa-tag__content">{content}</div>
            <button type="button" className="ids-chip__delete ibexa-tag__remove-btn" onClick={onRemove}>
                <Icon name="circle-close" extraClasses="ibexa-icon--small-medium" />
            </button>
        </div>
    );
};

Tag.propTypes = {
    content: PropTypes.string.isRequired,
    onRemove: PropTypes.func,
    isDeletable: PropTypes.bool,
    extraClasses: PropTypes.string,
};

export default Tag;
