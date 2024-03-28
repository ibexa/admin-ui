import React, { useRef } from 'react';
import PropTypes from 'prop-types';

const TooltipPopupComponent = ({
    title,
    subtitle,
    children,
    onConfirm,
    confirmBtnAttrs,
    confirmLabel,
    onClose,
    closeBtnAttrs,
    closeLabel,
    visible,
}) => {
    const contentRef = useRef();
    const attrs = {
        className: 'c-tooltip-popup',
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
                    <button className="btn ibexa-btn ibexa-btn--tertiary" type="button" onClick={onClose} {...closeBtnAttrs}>
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
};

TooltipPopupComponent.defaultProps = {
    subtitle: '',
    onClose: () => {},
    onConfirm: () => {},
    confirmLabel: '',
    closeLabel: '',
    confirmBtnAttrs: {},
    closeBtnAttrs: {},
};

export default TooltipPopupComponent;
