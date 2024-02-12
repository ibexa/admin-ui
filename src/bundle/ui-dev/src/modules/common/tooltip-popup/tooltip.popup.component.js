import React, { useRef } from 'react';
import PropTypes from 'prop-types';
import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

const TooltipPopupComponent = (props) => {
    const contentRef = useRef();
    const attrs = {
        className: 'c-tooltip-popup',
        hidden: !props.visible,
    };

    return (
        <div {...attrs}>
            <div className="c-tooltip-popup__header">
                <h1 className="c-tooltip-popup__title">{props.title}</h1>
                {props.subtitle && <div className="c-tooltip-popup__subtitle">{props.subtitle}</div>}
            </div>
            <div className="c-tooltip-popup__content" ref={contentRef}>
                {props.children}
            </div>
            {props.showFooter && (
                <div className="c-tooltip-popup__footer">
                    {props.onConfirm && (
                        <button
                            className="btn ibexa-btn ibexa-btn--primary"
                            type="button"
                            onClick={props.onConfirm}
                            {...props.confirmBtnAttrs}
                        >
                            {props.confirmLabel}
                        </button>
                    )}
                    {props.onClose && (
                        <button
                            className="btn ibexa-btn ibexa-btn--tertiary"
                            type="button"
                            onClick={props.onClose}
                            {...props.closeBtnAttrs}
                        >
                            {props.closeLabel}
                        </button>
                    )}
                </div>
            )}
        </div>
    );
};

TooltipPopupComponent.propTypes = {
    title: PropTypes.string.isRequired,
    subtitle: PropTypes.string,
    children: PropTypes.node.isRequired,
    visible: PropTypes.bool.isRequired,
    onClose: PropTypes.func,
    onConfirm: PropTypes.func,
    showFooter: PropTypes.bool,
    confirmLabel: PropTypes.string,
    closeLabel: PropTypes.string,
    confirmBtnAttrs: PropTypes.object,
    closeBtnAttrs: PropTypes.object,
};

TooltipPopupComponent.defaultProps = {
    subtitle: '',
    onClose: () => {},
    onConfirm: () => {},
    showFooter: true,
    confirmLabel: () => {
        const Translator = getTranslator();

        return Translator.trans(/*@Desc("Confirm")*/ 'tooltip.confirm_label', {}, 'ibexa_content');
    },
    closeLabel: () => {
        const Translator = getTranslator();

        return Translator.trans(/*@Desc("Close")*/ 'tooltip.close_label', {}, 'ibexa_content');
    },
    confirmBtnAttrs: {},
    closeBtnAttrs: {},
};

export default TooltipPopupComponent;
