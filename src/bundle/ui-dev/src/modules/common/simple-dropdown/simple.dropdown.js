import React, { useState, useEffect, useRef } from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../helpers/css.class.names';
import Icon from '../icon/icon';

const SimpleDropdown = ({ options, selectedOption, extraClasses, onOptionClick, isDisabled, selectedItemLabel, isSwitcher }) => {
    const containerRef = useRef();
    const [isExpanded, setIsExpanded] = useState(false);
    const dropdownClass = createCssClassNames({
        'c-simple-dropdown': true,
        'c-simple-dropdown--expanded': isExpanded,
        'c-simple-dropdown--disabled': isDisabled,
        'c-simple-dropdown--switcher': isSwitcher,
        [extraClasses]: true,
    });
    const toggleExpanded = () => {
        if (isDisabled) {
            return;
        }

        setIsExpanded((prevState) => !prevState);
    };
    const onOptionClickWrapper = (option) => {
        onOptionClick(option);

        setIsExpanded(false);
    };
    const renderItem = (item) => {
        const isItemSelected = item.value === selectedOption?.value;
        const itemClass = createCssClassNames({
            'c-simple-dropdown__list-item': true,
            'c-simple-dropdown__list-item--selected': isItemSelected,
        });

        return (
            <li key={item.value} className={itemClass} onClick={() => onOptionClickWrapper(item)}>
                {item.iconName && <Icon name={item.iconName} extraClasses="c-simple-dropdown__list-item-type-icon ibexa-icon--small" />}
                <span>{item.label}</span>
                {isItemSelected && (
                    <div className="c-simple-dropdown__list-item-checkmark">
                        <Icon name="checkmark" extraClasses="c-simple-dropdown__list-item-checkmark-icon ibexa-icon--tiny-small" />
                    </div>
                )}
            </li>
        );
    };
    const renderCaretIcon = () => {
        const iconName = isExpanded ? 'caret-up' : 'caret-down';

        return <Icon name={iconName} extraClasses="ibexa-icon--tiny-small c-simple-dropdown__expand-icon" />;
    };
    const renderSelectedLabel = () => {
        if (!selectedOption && !!selectedItemLabel) {
            return null;
        }

        return (
            <span className="c-simple-dropdown__selected-item-label">
                {selectedItemLabel.length ? selectedItemLabel : selectedOption.label}
            </span>
        );
    };
    const renderSelectedIcon = () => {
        if (!selectedOption || !selectedOption.iconName) {
            return null;
        }

        return <Icon name={selectedOption.iconName} extraClasses="ibexa-icon--small c-simple-dropdown__selected-item-type-icon" />;
    };
    const renderSelectedItem = () => {
        return (
            <button className="c-simple-dropdown__selected" onClick={toggleExpanded}>
                {renderSelectedIcon()}
                {renderSelectedLabel()}
                {renderCaretIcon()}
            </button>
        );
    };

    useEffect(() => {
        if (!isExpanded) {
            return;
        }

        const onInteractionOutside = (event) => {
            if (containerRef.current.contains(event.target)) {
                return;
            }

            setIsExpanded(false);
        };

        document.body.addEventListener('click', onInteractionOutside, false);

        return () => {
            document.body.removeEventListener('click', onInteractionOutside, false);
        };
    }, [isExpanded]);

    return (
        <div className={dropdownClass} ref={containerRef}>
            {renderSelectedItem()}
            <div className="c-simple-dropdown__items">
                <ul className="c-simple-dropdown__list-items">{options.map(renderItem)}</ul>
            </div>
        </div>
    );
};

SimpleDropdown.propTypes = {
    options: PropTypes.array.isRequired,
    selectedOption: PropTypes.object.isRequired,
    extraClasses: PropTypes.string,
    onOptionClick: PropTypes.func.isRequired,
    isDisabled: PropTypes.bool,
    selectedItemLabel: PropTypes.string,
    isSwitcher: PropTypes.bool,
};

SimpleDropdown.defaultProps = {
    isDisabled: false,
    extraClasses: '',
    selectedItemLabel: '',
    isSwitcher: false,
};

export default SimpleDropdown;
