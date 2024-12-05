import React, { useState, useEffect, useRef, useMemo, useCallback } from 'react';
import PropTypes from 'prop-types';

import { getRootDOMElement } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { createCssClassNames } from '@ibexa-admin-ui/src/bundle/ui-dev/src/modules/common/helpers/css.class.names';

import PopupMenuSearch from './popup.menu.search';
import PopupMenuGroup from './popup.menu.group';

const MIN_ITEMS_LIST_HEIGHT = 150;

const PopupMenu = ({ extraClasses, footer, items, onItemClick, positionOffset, referenceElement, scrollContainer, onClose }) => {
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
    const calculateAndSetItemsListStyles = useCallback(() => {
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
    }, [referenceElement, positionOffset]);
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
    }, [onClose, scrollContainer, referenceElement, calculateAndSetItemsListStyles]);

    return (
        <div className={popupMenuClassName} style={itemsListStyles} ref={containerRef}>
            <PopupMenuSearch numberOfItems={numberOfItems} filterText={filterText} setFilterText={setFilterText} />
            <div className="c-popup-menu__groups">
                {items.map((group) => (
                    <PopupMenuGroup key={group.key} items={group.items} filterText={filterText} onItemClick={onItemClick} />
                ))}
            </div>
            {renderFooter()}
        </div>
    );
};

PopupMenu.propTypes = {
    referenceElement: PropTypes.node.isRequired,
    extraClasses: PropTypes.string,
    footer: PropTypes.node,
    items: PropTypes.arrayOf({
        id: PropTypes.string.isRequired,
        items: PropTypes.shape({
            id: PropTypes.oneOf([PropTypes.string, PropTypes.number]),
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
