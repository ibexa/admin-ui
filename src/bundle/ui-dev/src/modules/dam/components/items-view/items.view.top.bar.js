import React, { useContext, useState, useEffect } from 'react';
import PropTypes from 'prop-types';
import Icon from '../../../common/icon/icon';
import ViewSwitcher from '../view-switcher/view.switcher';
import SortSwitcher from '../../../universal-discovery/components/sort-switcher/sort.switcher';
import { LoadedLocationsMapContext } from '../../../universal-discovery/universal.discovery.module';

const ItemsViewTopBar = ({ title, activeView,onViewChange }) => {
    const [loadedLocationsMap, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);
    const locationData = loadedLocationsMap.length ? loadedLocationsMap[loadedLocationsMap.length - 1] : { subitems: [] };

    console.log(locationData)
    return (
        <div className="c-dwb-items-view-top-bar">
            <h3 className="c-dwb-items-view-top-bar__title">{ locationData.location?.ContentInfo?.Content?.TranslatedName ?? locationData.treeBrowserItemName ?? null }</h3>
            <div className="c-dwb-items-view-top-bar__actions">
                <SortSwitcher />
                <ViewSwitcher onViewChange={onViewChange} activeView={activeView} isDisabled={false} />
            </div>
        </div>
    );
};

ItemsViewTopBar.propTypes = {
    title: PropTypes.string.isRequired,
    activeView: PropTypes.string.isRequired,
    onViewChange: PropTypes.func.isRequired,
};

ItemsViewTopBar.defaultProps = {};

export default ItemsViewTopBar;
