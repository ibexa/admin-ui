import React, { useState, useEffect, useRef } from 'react';
import ReactDOM from 'react-dom';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../../common/helpers/css.class.names';
import Icon from '../../common/icon/icon';

const { Translator } = window;
const MIN_SEARCH_ITEMS_DEFAULT = 5;

const Dropdown = ({ dropdownListRef, value, options, onChange, small, single, extraClasses, renderSelectedItem, minSearchItems }) => {
    const containerRef = useRef();
    const containerItemsRef = useRef();
    const [isExpanded, setIsExpanded] = useState(false);
    const [filterText, setFilterText] = useState('');
    const selectedItem = options.find((option) => option.value === value);
    const dropdownClassName = createCssClassNames({
        'ibexa-dropdown': true,
        'ibexa-dropdown--single': single,
        'ibexa-dropdown--small': small,
        [extraClasses]: true,
    });
    const toggleExpanded = () => {
        setIsExpanded((prevState) => !prevState);
    };
    const updateFilterValue = (event) => setFilterText(event.target.value);
    const resetInputValue = () => setFilterText('');
    const showItem = (itemValue, searchedTerm) => {
        if (searchedTerm.length < 3) {
            return true;
        }

        const itemValueLowerCase = itemValue.toLowerCase();
        const searchedTermLowerCase = searchedTerm.toLowerCase();

        return itemValueLowerCase.indexOf(searchedTermLowerCase) === 0;
    };
    const renderItem = (item) => {
        const itemClassName = createCssClassNames({
            'ibexa-dropdown__item': true,
            'ibexa-dropdown__item--selected': item.value === value,
            'ibexa-dropdown__item--hidden': !showItem(item.label, filterText),
        });

        return (
            <li
                className={itemClassName}
                key={item.value}
                onClick={() => {
                    onChange(item.value);
                    toggleExpanded();
                }}
            >
                <span className="ibexa-dropdown__item-label">{item.label}</span>
            </li>
        );
    };
    const renderItemsList = () => {
        const itemsStyles = {};
        const placeholder = Translator.trans(/*@Desc("Search...")*/ 'dropdown.placeholder', {}, 'universal_discovery_widget');
        const itemsContainerClass = createCssClassNames({
            'ibexa-dropdown__items': true,
            'ibexa-dropdown__items--search-hidden': options.length < minSearchItems,
        });

        if (containerRef.current) {
            const { width, left, top, height } = containerRef.current.getBoundingClientRect();

            itemsStyles.width = width;
            itemsStyles.left = left;
            itemsStyles.top = top + height + 8;
        }

        return (
            <div className={itemsContainerClass} style={itemsStyles} ref={containerItemsRef}>
                <div className="ibexa-input-text-wrapper">
                    <input
                        type="text"
                        placeholder={placeholder}
                        className="ibexa-dropdown__items-filter ibexa-input ibexa-input--small ibexa-input--text form-control"
                        onChange={updateFilterValue}
                        value={filterText}
                    />
                    <div className="ibexa-input-text-wrapper__actions">
                        <button
                            type="button"
                            className="btn ibexa-input-text-wrapper__action-btn ibexa-input-text-wrapper__action-btn--clear"
                            tabIndex="-1"
                            onClick={resetInputValue}
                        >
                            <Icon name="discard" />
                        </button>
                        <button
                            type="button"
                            className="btn ibexa-input-text-wrapper__action-btn ibexa-input-text-wrapper__action-btn--search"
                            tabIndex="-1"
                        >
                            <Icon name="search" extraClasses="ibexa-icon--small" />
                        </button>
                    </div>
                </div>
                <ul className="ibexa-dropdown__items-list">{options.map(renderItem)}</ul>
            </div>
        );
    };

    useEffect(() => {
        if (!isExpanded) {
            return;
        }

        const onInteractionOutside = (event) => {
            if (containerRef.current.contains(event.target) || containerItemsRef.current?.contains(event.target)) {
                return;
            }

            setIsExpanded(false);
        };

        document.body.addEventListener('click', onInteractionOutside, false);

        return () => {
            document.body.removeEventListener('click', onInteractionOutside, false);
        };
    }, [isExpanded]);

    useEffect(() => {
        setIsExpanded(false);
    }, [value]);

    return (
        <>
            <div className={dropdownClassName} ref={containerRef} onClick={toggleExpanded}>
                <div className="ibexa-dropdown__wrapper">
                    <ul className="ibexa-dropdown__selection-info">
                        <li className="ibexa-dropdown__selected-item">{renderSelectedItem(selectedItem)}</li>
                    </ul>
                </div>
            </div>
            {isExpanded && ReactDOM.createPortal(renderItemsList(), dropdownListRef.current)}
        </>
    );
};

Dropdown.propTypes = {
    dropdownListRef: PropTypes.object.isRequired,
    value: PropTypes.string.isRequired,
    options: PropTypes.array.isRequired,
    onChange: PropTypes.func.isRequired,
    small: PropTypes.bool,
    single: PropTypes.bool,
    extraClasses: PropTypes.string,
    renderSelectedItem: PropTypes.func,
    minSearchItems: PropTypes.number,
};

Dropdown.defaultProps = {
    small: false,
    single: false,
    extraClasses: '',
    renderSelectedItem: (item) => item?.label,
    minSearchItems: MIN_SEARCH_ITEMS_DEFAULT,
};

export default Dropdown;
