import React from 'react';
import PropTypes from 'prop-types';
import { Button, ButtonType } from '@ids-components/components/Button';

import { createCssClassNames } from '../../../common/helpers/css.class.names';

const ActionButton = ({ disabled, onClick, label = null, title = null, type }) => {
    const className = createCssClassNames({
        'c-action-btn': true,
        [`c-action-btn--${type}`]: Boolean(type),
    });

    const handleClick = () => {
        if (!disabled) {
            onClick();
        }
    };

    return (
        <Button type={ButtonType.TertiaryAlt} icon={type} onClick={handleClick} disabled={disabled} title={title} className={className}>
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
