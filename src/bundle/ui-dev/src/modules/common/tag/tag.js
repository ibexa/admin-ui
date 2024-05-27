import React from 'react';
import PropTypes from 'prop-types';
import Icon from '../icon/icon';
import { createCssClassNames } from '../helpers/css.class.names';

const Tag = ({ content, onRemove, isDeletable, extraClasses }) => {
    const className = createCssClassNames({
        'ibexa-tag': true,
        'ibexa-tag--deletable': isDeletable,
        [extraClasses]: true,
    });

    return (
        <div className={className}>
            <div className="ibexa-tag__content">{content}</div>
            <button type="button" className="ibexa-tag__remove-btn" onClick={onRemove}>
                <Icon name="circle-close" extraClasses="ibexa-icon--small" />
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

Tag.defaultProps = {
    extraClasses: '',
    onRemove: () => {},
    isDeletable: true,
};

export default Tag;
