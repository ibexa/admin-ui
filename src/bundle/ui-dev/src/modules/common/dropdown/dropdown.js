import React, { useState, useEffect, useRef, useLayoutEffect } from 'react';
import ReactDOM from 'react-dom';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../../common/helpers/css.class.names';
import Icon from '../../common/icon/icon';

const { Translator, document } = window;
const MIN_SEARCH_ITEMS_DEFAULT = 5;
const MIN_ITEMS_LIST_HEIGHT = 150;
const ITEMS_LIST_WIDGET_MARGIN = 8;
const ITEMS_LIST_SITE_MARGIN = ITEMS_LIST_WIDGET_MARGIN + 4;
const RESTRICTED_AREA_ITEMS_CONTAINER = 190;

const Dropdown = ({
    dropdownListRef,
    value,
    options,
    onChange,
    small,
    single,
    placeholder,
    extraClasses,
    renderSelectedItem,
    minSearchItems,
}) => {
    const containerRef = useRef();
    const containerItemsRef = useRef();
    const selectionInfoRef = useRef();
    const [isExpanded, setIsExpanded] = useState(false);
    const [filterText, setFilterText] = useState('');
    const [itemsListStyles, setItemsListStyles] = useState({});
    const [overflowItemsCount, setOverflowItemsCount] = useState(0);
    const selectedItems = single
        ? [options.find((option) => option.value === value)]
        : value.map((singleValue) => options.find((option) => option.value === singleValue));
    const dropdownClassName = createCssClassNames({
        'ibexa-dropdown': true,
        'ibexa-dropdown--single': single,
        'ibexa-dropdown--multi': !single,
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
        const isItemSelected = single ? item.value === value : value.includes(item.value);
        const itemClassName = createCssClassNames({
            'ibexa-dropdown__item': true,
            'ibexa-dropdown__item--selected': isItemSelected,
            'ibexa-dropdown__item--hidden': !showItem(item.label, filterText),
        });

        return (
            <li
                className={itemClassName}
                key={item.value}
                onClick={() => {
                    onChange(item.value);

                    if (single) {
                        toggleExpanded();
                    }
                }}
            >
                {!single && <input type="checkbox" className="ibexa-input ibexa-input--checkbox" checked={isItemSelected} />}
                <span className="ibexa-dropdown__item-label">{item.label}</span>
                {single && (
                    <div className="ibexa-dropdown__item-check">
                        <Icon name="checkmark" extraClasses="ibexa-icon--tiny-small ibexa-dropdown__item-check-icon" />
                    </div>
                )}
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
        const placeholder = Translator.trans(/*@Desc("Search...")*/ 'dropdown.placeholder', {}, 'ibexa_universal_discovery_widget');
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
                            <Icon name="discard" extraClasses="ibexa-icon--small" />
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
    const renderPlaceholder = () => {
        if (!placeholder) {
            return null;
        }

        return (
            <li className="ibexa-dropdown__selected-item ibexa-dropdown__selected-item--predefined ibexa-dropdown__selected-placeholder">
                {placeholder}
            </li>
        );
    };
    const renderSelectedMultipleItem = (item) => {
        return (
            <li className="ibexa-dropdown__selected-item " data-value="pending">
                {item.label}
                <span className="ibexa-dropdown__remove-selection" onClick={() => onChange(item.value)} />
            </li>
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

    useLayoutEffect(() => {
        if (single || !selectionInfoRef.current) {
            return;
        }

        let itemsWidth = 0;
        let numberOfOverflowItems = 0;
        const selectedItemsNodes = selectionInfoRef.current.querySelectorAll('.ibexa-dropdown__selected-item');
        const selectedItemsOverflow = selectionInfoRef.current.querySelector('.ibexa-dropdown__selected-overflow-number');
        const dropdownItemsContainerWidth = selectionInfoRef.current.offsetWidth - RESTRICTED_AREA_ITEMS_CONTAINER;

        if (selectedItemsOverflow) {
            selectedItemsNodes.forEach((item) => {
                item.hidden = false;
            });
            selectedItemsNodes.forEach((item, index) => {
                const isOverflowNumber = item.classList.contains('ibexa-dropdown__selected-overflow-number');

                itemsWidth += item.offsetWidth;

                if (!isOverflowNumber && index !== 0 && itemsWidth > dropdownItemsContainerWidth) {
                    const isPlaceholder = item.classList.contains('ibexa-dropdown__selected-placeholder');

                    item.hidden = true;

                    if (!isPlaceholder) {
                        numberOfOverflowItems++;
                    }
                }
            });

            selectedItemsOverflow.hidden = !numberOfOverflowItems;

            if (numberOfOverflowItems !== overflowItemsCount) {
                setOverflowItemsCount(numberOfOverflowItems);
            }
        }
    }, [value]);

    useEffect(() => {
        if (single) {
            setIsExpanded(false);
        }
    }, [value]);

    return (
        <>
            <div className={dropdownClassName} ref={containerRef} onClick={toggleExpanded}>
                <div className="ibexa-dropdown__wrapper">
                    <ul className="ibexa-dropdown__selection-info" ref={selectionInfoRef}>
                        {selectedItems.length === 0 && renderPlaceholder()}
                        {single && <li className="ibexa-dropdown__selected-item">{renderSelectedItem(selectedItems[0])}</li>}
                        {!single && selectedItems.map((singleValue) => renderSelectedMultipleItem(singleValue))}
                        <li className="ibexa-dropdown__selected-item ibexa-dropdown__selected-item--predefined ibexa-dropdown__selected-overflow-number">
                            {overflowItemsCount}
                        </li>
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
    placeholder: PropTypes.string,
    extraClasses: PropTypes.string,
    renderSelectedItem: PropTypes.func,
    minSearchItems: PropTypes.number,
};

Dropdown.defaultProps = {
    small: false,
    single: false,
    placeholder: null,
    extraClasses: '',
    renderSelectedItem: (item) => item?.label,
    minSearchItems: MIN_SEARCH_ITEMS_DEFAULT,
};

export default Dropdown;
