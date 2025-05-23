import React, { useRef } from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../helpers/css.class.names';

const TooltipPopupComponent = ({
    title,
    subtitle = '',
    children,
    onConfirm = () => {},
    confirmBtnAttrs = {},
    confirmLabel = '',
    onClose = () => {},
    closeBtnAttrs = {},
    closeLabel = '',
    visible,
    extraClasses = '',
}) => {
    const contentRef = useRef();
    const className = createCssClassNames({
        'c-tooltip-popup': true,
        [extraClasses]: true,
    });
    const attrs = {
        className,
        hidden: !visible,
    };

    return (
        <div {...attrs}>
            <div className="c-tooltip-popup__header">
                <h1 className="c-tooltip-popup__title">{title}</h1>
                {subtitle && <div className="c-tooltip-popup__subtitle">{subtitle}</div>}
            </div>
            <div className="c-tooltip-popup__content" ref={contentRef}>
                {children}
            </div>
            <div className="c-tooltip-popup__footer">
                {confirmLabel && (
                    <button className="btn ibexa-btn ibexa-btn--primary" type="button" onClick={onConfirm} {...confirmBtnAttrs}>
                        {confirmLabel}
                    </button>
                )}
                {closeLabel && (
                    <button className="btn ibexa-btn ibexa-btn--ghost" type="button" onClick={onClose} {...closeBtnAttrs}>
                        {closeLabel}
                    </button>
                )}
            </div>
        </div>
    );
};

TooltipPopupComponent.propTypes = {
    title: PropTypes.string.isRequired,
    children: PropTypes.node.isRequired,
    visible: PropTypes.bool.isRequired,
    subtitle: PropTypes.string,
    onClose: PropTypes.func,
    onConfirm: PropTypes.func,
    confirmLabel: PropTypes.string,
    closeLabel: PropTypes.string,
    confirmBtnAttrs: PropTypes.object,
    closeBtnAttrs: PropTypes.object,
    extraClasses: PropTypes.string,
};

export default TooltipPopupComponent;
