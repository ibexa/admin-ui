import React, { useState, useEffect, useRef, useLayoutEffect } from 'react';
import ReactDOM from 'react-dom';
import PropTypes from 'prop-types';

import { getRootDOMElement, getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import Icon from '@ibexa-admin-ui/src/bundle/ui-dev/src/modules/common/icon/icon';

import { createCssClassNames } from '../helpers/css.class.names';
// import Icon from '../icon/icon';
// import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

const { document } = window;
const MIN_SEARCH_ITEMS_DEFAULT = 5;
const MIN_ITEMS_LIST_HEIGHT = 150;
const ITEMS_LIST_WIDGET_MARGIN = 8;
const ITEMS_LIST_SITE_MARGIN = ITEMS_LIST_WIDGET_MARGIN + 4;
const RESTRICTED_AREA_ITEMS_CONTAINER = 190;

const PopupMenu = ({ positionOffset, items, scrollContainer, referenceElement, onItemClick }) => {
    const rootDOMElement = getRootDOMElement();
    const Translator = getTranslator();
    const containerRef = useRef();
    const [isVisible, setIsVisible] = useState(true);
    const [itemsListStyles, setItemsListStyles] = useState({
        left: 0,
        top: 0,
    });
    const [filterText, setFilterText] = useState('');
    const popupMenuClassName = createCssClassNames({
        'c-popup-menu': true,
        'c-popup-menu--visible': isVisible,
    });
    const searchPlaceholder = Translator.trans(/*@Desc("Search...")*/ 'popup_menu.placeholder', {}, 'popup_menu');
    const updateFilterValue = (event) => setFilterText(event.target.value);
    const resetInputValue = () => setFilterText('');
    const showItem = (item) => {
        if (filterText.length < 3) {
            return true;
        }

        const itemLabelLowerCase = item.label.toLowerCase();
        const filterTextLowerCase = filterText.toLowerCase();

        return itemLabelLowerCase.indexOf(filterTextLowerCase) === 0;
    };
    const renderGroup = (group) => {
        const groupClassName = createCssClassNames({
            'c-popup-menu__group': true,
        });

        return <div className={groupClassName}>{group.items.map(renderItem)}</div>;
    };
    const renderItem = (item) => {
        if (!showItem(item)) {
            return null;
        }

        const itemClassName = createCssClassNames({
            'c-popup-menu__item': true,
        });

        return (
            <div className={itemClassName} key={item.value}>
                <button type="button" className="c-popup-menu__item-content" onClick={() => onItemClick(item)}>
                    <span className="c-popup-menu__item-label">{item.label}</span>
                </button>
            </div>
        );
    };
    const calculateAndSetItemsListStyles = () => {
        const itemsStyles = {};
        const { top: referenceTop, left: referenceLeft } = referenceElement.getBoundingClientRect();
        const { height: containerHeight } = containerRef.current.getBoundingClientRect();
        const bottom = referenceTop + containerHeight;

        itemsStyles.top = referenceTop;

        if (window.innerHeight - bottom > MIN_ITEMS_LIST_HEIGHT) {
            const { x: offsetX, y: offsetY } = positionOffset(referenceElement, 'bottom');

            itemsStyles.top = referenceTop + offsetY;
            itemsStyles.left = referenceLeft + offsetX;
            itemsStyles.maxHeight = window.innerHeight - bottom;
        } else {
            const { x: offsetX, y: offsetY } = positionOffset(referenceElement, 'top');

            console.log(offsetY);

            itemsStyles.top = referenceTop + offsetY;
            itemsStyles.left = referenceLeft + offsetX;
            itemsStyles.maxHeight = referenceTop;
            itemsStyles.transform = 'translateY(-100%)';
        }

        setItemsListStyles(itemsStyles);
    };

    useEffect(() => {
        calculateAndSetItemsListStyles();

        if (!isVisible) {
            return;
        }

        const onInteractionOutside = (event) => {
            if (containerRef.current.contains(event.target) || referenceElement.contains(event.target)) {
                return;
            }

            setIsVisible(false);
        };

        window.document.body.addEventListener('click', onInteractionOutside, false);
        scrollContainer.addEventListener('scroll', calculateAndSetItemsListStyles, false);

        return () => {
            window.document.body.removeEventListener('click', onInteractionOutside);
            scrollContainer.removeEventListener('scroll', calculateAndSetItemsListStyles);

            setItemsListStyles({});
        };
    }, [isVisible]);

    return (
        <div className={popupMenuClassName} style={itemsListStyles} ref={containerRef}>
            <div className="c-popup-menu__search">
                <div className="ibexa-input-text-wrapper">
                    <input
                        type="text"
                        placeholder={searchPlaceholder}
                        className="c-popup-menu__search-input ibexa-input ibexa-input--small ibexa-input--text form-control"
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
                            <Icon name="discard" extraClasses="ibexa-icon--tiny-small" />
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
            </div>
            <div className="c-popup-menu__groups">{items.map(renderGroup)}</div>
        </div>
    );
};

PopupMenu.propTypes = {
    referenceElement: PropTypes.isRequired,
    positionOffset: PropTypes.func,
    scrollContainer: PropTypes.node,
    onItemClick: PropTypes.func,
};

PopupMenu.defaultProps = {
    positionOffset: () => ({ x: 0, y: 0 }),
    scrollContainer: window.document.body,
    onItemClick: () => {},
};

export default PopupMenu;
