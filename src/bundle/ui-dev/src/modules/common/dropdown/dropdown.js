import React, { useState, useEffect, useRef } from 'react';
import ReactDOM from 'react-dom';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../../common/helpers/css.class.names';
import Icon from '../../common/icon/icon';

const { Translator, document } = window;
const MIN_SEARCH_ITEMS_DEFAULT = 5;
const MIN_ITEMS_LIST_HEIGHT = 150;
const ITEMS_LIST_WIDGET_MARGIN = 8;
const ITEMS_LIST_SITE_MARGIN = ITEMS_LIST_WIDGET_MARGIN + 4;

const Dropdown = ({ dropdownListRef, value, options, onChange, small, single, extraClasses, renderSelectedItem, minSearchItems }) => {
    const containerRef = useRef();
    const containerItemsRef = useRef();
    const [isExpanded, setIsExpanded] = useState(false);
    const [filterText, setFilterText] = useState('');
    const [itemsListStyles, setItemsListStyles] = useState({});
    const selectedItem = options.find((option) => option.value === value);
    const dropdownClassName = createCssClassNames({
        'ibexa-dropdown': true,
        'ibexa-dropdown--single': single,
        'ibexa-dropdown--small': small,
        'ibexa-dropdown--expanded': isExpanded,
        [extraClasses]: true,
    });
    const toggleExpanded = () => {
        calculateAndSetItemsListStyles();
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
                <div className="ibexa-dropdown__item-check">
                    <Icon name="checkmark" extraClasses="ibexa-icon--tiny-small ibexa-dropdown__item-check-icon" />
                </div>
            </li>
        );
    };
    const calculateAndSetItemsListStyles = () => {
        const itemsStyles = {};
        const { width, left, top, height, bottom } = containerRef.current.getBoundingClientRect();

        itemsStyles.width = width;
        itemsStyles.left = left;

        if (window.innerHeight - bottom > MIN_ITEMS_LIST_HEIGHT) {
            itemsStyles.top = top + height + ITEMS_LIST_WIDGET_MARGIN;
            itemsStyles.maxHeight = window.innerHeight - bottom - ITEMS_LIST_SITE_MARGIN;
        } else {
            const headerContainer = document.querySelector('.ibexa-main-header');
            const headerHeight = headerContainer.offsetHeight;

            itemsStyles.top = top - ITEMS_LIST_WIDGET_MARGIN;
            itemsStyles.maxHeight = top - headerHeight - ITEMS_LIST_SITE_MARGIN;
            itemsStyles.transform = 'translateY(-100%)';
        }

        setItemsListStyles(itemsStyles);
    };
    const renderItemsList = () => {
        const placeholder = Translator.trans(/*@Desc("Search...")*/ 'dropdown.placeholder', {}, 'universal_discovery_widget');
        const itemsContainerClass = createCssClassNames({
            'ibexa-dropdown__items': true,
            'ibexa-dropdown__items--search-hidden': options.length < minSearchItems,
        });

        return (
            <div className={itemsContainerClass} style={itemsListStyles} ref={containerItemsRef}>
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

        const scrollContainer = document.querySelector('.ibexa-main-container__content-column');
        const onInteractionOutside = (event) => {
            if (containerRef.current.contains(event.target) || containerItemsRef.current?.contains(event.target)) {
                return;
            }

            setIsExpanded(false);
        };

        document.body.addEventListener('click', onInteractionOutside, false);
        scrollContainer.addEventListener('scroll', calculateAndSetItemsListStyles, false);

        return () => {
            document.body.removeEventListener('click', onInteractionOutside);
            document.body.removeEventListener('click', calculateAndSetItemsListStyles);

            setItemsListStyles({});
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
