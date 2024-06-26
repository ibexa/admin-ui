import React, { useEffect, useRef } from 'react';
import PropTypes from 'prop-types';
import Icon from '../icon/icon';

import { createCssClassNames } from '@ibexa-admin-ui/src/bundle/ui-dev/src/modules/common/helpers/css.class.names';
import {
    getTranslator,
    getBootstrap,
    getRootDOMElement,
} from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

const CLASS_NON_SCROLLABLE = 'ibexa-non-scrollable';
const CLASS_MODAL_OPEN = 'modal-open';
const MODAL_CONFIG = {
    backdrop: 'static',
    keyboard: true,
};
const MODAL_SIZE_CLASS = {
    small: 'modal-sm',
    medium: '',
    large: 'modal-lg',
};

const Popup = ({
    isVisible,
    onClose,
    children,
    title,
    subtitle,
    hasFocus,
    noKeyboard,
    actionBtnsConfig,
    size,
    noHeader,
    noCloseBtn,
    extraClasses,
    showTooltip,
}) => {
    const rootDOMElement = getRootDOMElement();
    const modalRef = useRef(null);
    const Translator = getTranslator();
    const bootstrap = getBootstrap();

    useEffect(() => {
        rootDOMElement.classList.toggle(CLASS_MODAL_OPEN, isVisible);
        rootDOMElement.classList.toggle(CLASS_NON_SCROLLABLE, isVisible);

        if (isVisible) {
            showPopup();
            modalRef.current.addEventListener('hidden.bs.modal', onClose);
        }
    }, [isVisible]);

    if (!isVisible) {
        return null;
    }

    const modalClasses = createCssClassNames({
        'c-popup modal fade': true,
        'c-popup--no-header': noHeader,
        [extraClasses]: extraClasses,
    });
    const closeBtnLabel = Translator.trans(/*@Desc("Close")*/ 'popup.close.label', {}, 'ibexa_universal_discovery_widget');
    const hidePopup = () => {
        bootstrap.Modal.getOrCreateInstance(modalRef.current).hide();
        rootDOMElement.classList.remove(CLASS_MODAL_OPEN, CLASS_NON_SCROLLABLE);
    };
    const showPopup = () => {
        const bootstrapModal = bootstrap.Modal.getOrCreateInstance(modalRef.current, {
            ...MODAL_CONFIG,
            keyboard: !noKeyboard,
            focus: hasFocus,
        });
        const initializedBackdropRootElement = bootstrapModal._backdrop._config.rootElement;

        if (initializedBackdropRootElement !== rootDOMElement) {
            bootstrapModal._backdrop._config.rootElement = rootDOMElement;
        }

        bootstrapModal.show();
    };
    const handleOnClick = (event, onClick) => {
        modalRef.current.removeEventListener('hidden.bs.modal', onClose);
        hidePopup();
        onClick(event);
    };
    const renderCloseBtn = () => {
        if (noCloseBtn) {
            return null;
        }

        return (
            <button
                type="button"
                className="close c-popup__btn--close"
                data-bs-dismiss="modal"
                aria-label={closeBtnLabel}
                onClick={hidePopup}
            >
                <Icon name="discard" extraClasses="ibexa-icon--small" />
            </button>
        );
    };

    return (
        <div ref={modalRef} className={modalClasses} tabIndex={hasFocus ? -1 : undefined}>
            <div className={`modal-dialog c-popup__dialog ${MODAL_SIZE_CLASS[size]}`} role="dialog">
                <div className="modal-content c-popup__content">
                    {noHeader
                        ? renderCloseBtn()
                        : title && (
                              <div className="modal-header c-popup__header">
                                  <h3 className="modal-title c-popup__headline" title={showTooltip ? title : null}>
                                      <span className="c-popup__title">{title}</span>
                                      {subtitle && <span className="c-popup__subtitle">{subtitle}</span>}
                                  </h3>
                                  {renderCloseBtn()}
                              </div>
                          )}
                    <div className="modal-body c-popup__body">{children}</div>
                    <div className="modal-footer c-popup__footer">
                        {actionBtnsConfig.map(({ className, onClick, disabled = false, label, ...extraProps }) => (
                            <button
                                key={label}
                                type="button"
                                className={`btn ibexa-btn ${className}`}
                                onClick={onClick ? (event) => handleOnClick(event, onClick) : hidePopup}
                                disabled={disabled}
                                {...extraProps}
                            >
                                {label}
                            </button>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
};

Popup.propTypes = {
    actionBtnsConfig: PropTypes.arrayOf(
        PropTypes.shape({
            label: PropTypes.string.isRequired,
            onClick: PropTypes.func,
            disabled: PropTypes.bool,
            className: PropTypes.string,
        }),
    ).isRequired,
    children: PropTypes.node.isRequired,
    isVisible: PropTypes.bool.isRequired,
    onClose: PropTypes.func,
    title: PropTypes.string,
    subtitle: PropTypes.string,
    hasFocus: PropTypes.bool,
    size: PropTypes.string,
    noHeader: PropTypes.bool,
    noCloseBtn: PropTypes.bool,
    noKeyboard: PropTypes.bool,
    extraClasses: PropTypes.string,
    showTooltip: PropTypes.bool,
};

Popup.defaultProps = {
    hasFocus: true,
    noKeyboard: false,
    onClose: null,
    size: 'large',
    noHeader: false,
    noCloseBtn: false,
    extraClasses: '',
    title: null,
    subtitle: null,
    showTooltip: true,
};

export default Popup;
