import React, { useContext, useState, useMemo, useEffect, useCallback, useRef } from 'react';

import Icon from '../../../common/icon/icon';

import { getTranslator, SYSTEM_ROOT_LOCATION_ID } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { createCssClassNames } from '../../../common/helpers/css.class.names';
import { getLoadedLocationsLimitedMap } from './breadcrumbs.helpers';
import { LoadedLocationsMapContext, MarkedLocationIdContext, GridActiveLocationIdContext } from '../../universal.discovery.module';

const Breadcrumbs = () => {
    const Translator = getTranslator();
    const hiddenListWrapperRef = useRef();
    const [, setMarkedLocationId] = useContext(MarkedLocationIdContext);
    const [gridActiveLocationId, setGridActiveLocationId] = useContext(GridActiveLocationIdContext);
    const [loadedLocationsFullMap, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);
    const loadedLocationsMap = getLoadedLocationsLimitedMap(loadedLocationsFullMap, gridActiveLocationId);
    const [hiddenListVisible, setHiddenListVisible] = useState(false);
    const { visibleItems, hiddenItems } = useMemo(() => {
        return loadedLocationsMap.reduce(
            (splittedItems, loadedLocation, index) => {
                if (loadedLocationsMap.length - 3 <= index) {
                    splittedItems.visibleItems.push(loadedLocation);
                } else {
                    splittedItems.hiddenItems.push(loadedLocation);
                }

                return splittedItems;
            },
            { visibleItems: [], hiddenItems: [] },
        );
    }, [loadedLocationsMap]);
    const goToLocation = (locationId) => {
        const itemIndex = loadedLocationsMap.findIndex((data) => data.parentLocationId === locationId);
        const updatedLoadedLocations = loadedLocationsMap.slice(0, itemIndex + 1);

        updatedLoadedLocations[updatedLoadedLocations.length - 1].subitems = [];

        dispatchLoadedLocationsAction({ type: 'SET_LOCATIONS', data: updatedLoadedLocations });
        setMarkedLocationId(locationId);
        setGridActiveLocationId(locationId);
    };
    const toggleHiddenListVisible = useCallback(() => {
        setHiddenListVisible(!hiddenListVisible);
    }, [setHiddenListVisible, hiddenListVisible]);
    const handleTogglerClick = (event) => {
        event.stopPropagation();
        toggleHiddenListVisible();
    };
    const renderHiddenList = () => {
        if (!hiddenItems.length) {
            return null;
        }

        const hiddenListClassNames = createCssClassNames({
            'c-breadcrumbs__hidden-list': true,
            'c-breadcrumbs__hidden-list--visible': hiddenListVisible,
        });
        const toggleClassNames = createCssClassNames({
            'c-breadcrumbs__hidden-list-toggler': true,
            'c-breadcrumbs__hidden-list-toggler--active': hiddenListVisible,
        });

        return (
            <div ref={hiddenListWrapperRef} className="c-breadcrumbs__hidden-list-wrapper">
                <button className={toggleClassNames} onClick={handleTogglerClick} type="button">
                    <Icon name="options" extraClasses="ibexa-icon--small-medium" />
                </button>
                <ul className={hiddenListClassNames}>
                    {hiddenItems.map((item) => {
                        const locationId = item.parentLocationId;
                        const locationName =
                            locationId === SYSTEM_ROOT_LOCATION_ID
                                ? Translator.trans(
                                      /* @Desc("Root Location") */ 'breadcrumbs.root_location',
                                      {},
                                      'ibexa_universal_discovery_widget',
                                  )
                                : item.location.ContentInfo.Content.TranslatedName;
                        const onClickHandler = goToLocation.bind(this, locationId);

                        return (
                            <li key={locationId} onClick={onClickHandler} className="c-breadcrumbs__hidden-list-item">
                                {locationName}
                            </li>
                        );
                    })}
                </ul>
            </div>
        );
    };
    const renderSeparator = () => {
        return <span className="c-breadcrumbs__list-item-separator">/</span>;
    };

    useEffect(() => {
        if (!hiddenListVisible) {
            return;
        }

        const hideHiddenMenuOnClickOutside = (event) => {
            const { target } = event;

            if (hiddenListWrapperRef.current?.contains(target) ?? false) {
                return;
            }

            setHiddenListVisible(false);
        };

        window.document.body.addEventListener('click', hideHiddenMenuOnClickOutside, false);

        return () => window.document.body.removeEventListener('click', hideHiddenMenuOnClickOutside, false);
    }, [hiddenListVisible, setHiddenListVisible, hiddenListWrapperRef]);

    if (loadedLocationsMap.some((loadedLocation) => loadedLocation.parentLocationId !== 1 && !loadedLocation.location)) {
        return null;
    }

    return (
        <div className="c-breadcrumbs">
            {renderHiddenList()}
            <div className="c-breadcrumbs__list-wrapper">
                <ul className="c-breadcrumbs__list">
                    {visibleItems.map((item, index) => {
                        const locationId = item.parentLocationId;
                        const locationName =
                            locationId === SYSTEM_ROOT_LOCATION_ID
                                ? Translator.trans(
                                      /* @Desc("Root Location") */ 'breadcrumbs.root_location',
                                      {},
                                      'ibexa_universal_discovery_widget',
                                  )
                                : item.location.ContentInfo.Content.TranslatedName;
                        const isLast = index === visibleItems.length - 1;
                        const onClickHandler = goToLocation.bind(this, locationId);
                        const className = createCssClassNames({
                            'c-breadcrumbs__list-item': true,
                            'c-breadcrumbs__list-item--last': isLast,
                        });

                        return (
                            <li key={locationId} onClick={onClickHandler} className={className}>
                                <span className="c-breadcrumbs__list-item-text">{locationName}</span>
                                {!isLast && renderSeparator()}
                            </li>
                        );
                    })}
                </ul>
            </div>
        </div>
    );
};

export default Breadcrumbs;
