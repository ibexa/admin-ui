import React from 'react';
import PropTypes from 'prop-types';
import { Button, ButtonType } from '@ids-components/components/Button';

const ActionButton = ({ disabled, onClick, label = null, title = null, type }) => {
    const handleClick = () => {
        if (!disabled) {
            onClick();
        }
    };

    return (
        <Button
            type={ButtonType.TertiaryAlt}
            icon={type}
            onClick={handleClick}
            disabled={disabled}
            title={title}
            className={`c-action-btn${type ? ` c-action-btn--${type}` : ''}`}
        >
            {label || null}
        </Button>
    );
};

ActionButton.propTypes = {
    label: PropTypes.string,
    title: PropTypes.string,
    disabled: PropTypes.bool.isRequired,
    type: PropTypes.string.isRequired,
    onClick: PropTypes.func.isRequired,
};

export default ActionButton;
