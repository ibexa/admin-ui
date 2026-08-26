import React from 'react';
import PropTypes from 'prop-types';
import { Alert as IdsAlert, AlertType, AlertVariant } from '@ids-components/components/Alert';

/**
 * Compatibility wrapper around the design-system Alert.
 *
 * @deprecated since 6.0 — use `Alert` from `@ids-components/components/Alert` directly.
 */
const Alert = ({
    type,
    title = null,
    subtitle = null,
    iconName = null,
    iconPath = null,
    showCloseBtn = false,
    onClose = () => {},
    extraClasses = '',
    children = null,
}) => {
    const hasDescription = !!subtitle || !!children;
    const description = hasDescription ? (
        <>
            {subtitle}
            {children}
        </>
    ) : null;

    return (
        <IdsAlert
            className={extraClasses.trim()}
            icon={iconName ?? ''}
            iconPath={iconPath ?? ''}
            isDismissible={showCloseBtn}
            onDismiss={onClose}
            title={title ?? ''}
            type={type}
            variant={AlertVariant.Local}
        >
            {description}
        </IdsAlert>
    );
};

Alert.propTypes = {
    type: PropTypes.oneOf(Object.values(AlertType)).isRequired,
    title: PropTypes.string,
    subtitle: PropTypes.string,
    iconName: PropTypes.string,
    iconPath: PropTypes.string,
    showCloseBtn: PropTypes.bool,
    onClose: PropTypes.func,
    extraClasses: PropTypes.string,
    children: PropTypes.node,
};

export default Alert;
