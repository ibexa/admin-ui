import React, { useState, useEffect, useRef, useMemo } from 'react';
import PropTypes from 'prop-types';

import { getTranslator, getRootDOMElement } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { createCssClassNames } from '@ibexa-admin-ui/src/bundle/ui-dev/src/modules/common/helpers/css.class.names';
import Icon from '@ibexa-admin-ui/src/bundle/ui-dev/src/modules/common/icon/icon';

const MIN_SEARCH_ITEMS_DEFAULT = 5;
const MIN_ITEMS_LIST_HEIGHT = 150;

const PopupMenu = ({ extraClasses, footer, items, onItemClick, positionOffset, referenceElement, scrollContainer, onClose }) => {
    const Translator = getTranslator();
    const containerRef = useRef();
    const [isRendered, setIsRendered] = useState(false);
    const [itemsListStyles, setItemsListStyles] = useState({
        left: 0,
        top: 0,
    });
    const [filterText, setFilterText] = useState('');
    const numberOfItems = useMemo(() => items.reduce((sum, group) => sum + group.items.length, 0), [items]);
    const popupMenuClassName = createCssClassNames({
        'c-popup-menu': true,
        'c-popup-menu--hidden': !isRendered,
        [extraClasses]: true,
    });
    const searchPlaceholder = Translator.trans(/*@Desc("Search...")*/ 'ibexa_popup_menu.search.placeholder', {}, 'ibexa_popup_menu');
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
        const isAnyItemVisible = group.items.some(showItem);

        if (!isAnyItemVisible) {
            return null;
        }

        return <div className="c-popup-menu__group">{group.items.map(renderItem)}</div>;
    };
    const renderItem = (item) => {
        if (!showItem(item)) {
            return null;
        }

        return (
            <div className="c-popup-menu__item" key={item.value}>
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

        if (window.innerHeight - bottom > MIN_ITEMS_LIST_HEIGHT) {
            const { x: offsetX, y: offsetY } = positionOffset(referenceElement, 'bottom');

            itemsStyles.top = referenceTop + offsetY;
            itemsStyles.left = referenceLeft + offsetX;
            itemsStyles.maxHeight = window.innerHeight - itemsStyles.top;
        } else {
            const { x: offsetX, y: offsetY } = positionOffset(referenceElement, 'top');

            itemsStyles.top = referenceTop + offsetY;
            itemsStyles.left = referenceLeft + offsetX;
            itemsStyles.maxHeight = itemsStyles.top;
            itemsStyles.transform = 'translateY(-100%)';
        }

        setItemsListStyles(itemsStyles);
    };
    const renderSearch = () => {
        if (numberOfItems < MIN_SEARCH_ITEMS_DEFAULT) {
            return null;
        }

        return (
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
        );
    };
    const renderFooter = () => {
        if (!footer) {
            return null;
        }

        return <div className="c-popup-menu__footer">{footer}</div>;
    };

    useEffect(() => {
        calculateAndSetItemsListStyles();
        setIsRendered(true);

        const rootDOMElement = getRootDOMElement();
        const onInteractionOutside = (event) => {
            if (containerRef.current.contains(event.target) || referenceElement.contains(event.target)) {
                return;
            }

            onClose();
        };

        rootDOMElement.addEventListener('click', onInteractionOutside, false);
        scrollContainer.addEventListener('scroll', calculateAndSetItemsListStyles, false);

        return () => {
            rootDOMElement.removeEventListener('click', onInteractionOutside);
            scrollContainer.removeEventListener('scroll', calculateAndSetItemsListStyles);

            setItemsListStyles({});
        };
    }, [onClose, scrollContainer]);

    return (
        <div className={popupMenuClassName} style={itemsListStyles} ref={containerRef}>
            {renderSearch()}
            <div className="c-popup-menu__groups">{items.map(renderGroup)}</div>
            {renderFooter()}
        </div>
    );
};

PopupMenu.propTypes = {
    referenceElement: PropTypes.node.isRequired,
    extraClasses: PropTypes.string,
    footer: PropTypes.node,
    items: PropTypes.arrayOf({
        items: PropTypes.shape({
            value: PropTypes.oneOf([PropTypes.string, PropTypes.number]),
            label: PropTypes.string,
        }),
    }),
    onClose: PropTypes.func,
    onItemClick: PropTypes.func,
    positionOffset: PropTypes.func,
    scrollContainer: PropTypes.node,
};

PopupMenu.defaultProps = {
    extraClasses: '',
    footer: null,
    items: [],
    onClose: () => {},
    onItemClick: () => {},
    positionOffset: () => ({ x: 0, y: 0 }),
    scrollContainer: getRootDOMElement(),
};

export default PopupMenu;