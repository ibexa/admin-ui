import React, { useContext } from 'react';
import PropTypes from 'prop-types';

import ToggleSelection from '../toggle-selection/toggle.selection';
import Thumbnail from '../../../common/thumbnail/thumbnail';
import Icon from '../../../common/icon/icon';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import { useSelectedLocationsHelpers } from '../../hooks/useSelectedLocationsHelpers';
import {
    LoadedLocationsMapContext,
    MarkedLocationIdContext,
    ContentTypesMapContext,
    SelectedLocationsContext,
    MultipleConfigContext,
    ContainersOnlyContext,
    GridActiveLocationIdContext,
} from '../../universal.discovery.module';

const isSelectionButtonClicked = (event) => {
    return event.target.closest('.c-udw-toggle-selection');
};

const GridViewItem = ({ location, version = {} }) => {
    const [, setGridActiveLocationId] = useContext(GridActiveLocationIdContext);
    const [markedLocationId, setMarkedLocationId] = useContext(MarkedLocationIdContext);
    const [, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);
    const contentTypesMap = useContext(ContentTypesMapContext);
    const [, dispatchSelectedLocationsAction] = useContext(SelectedLocationsContext);
    const [multiple] = useContext(MultipleConfigContext);
    const containersOnly = useContext(ContainersOnlyContext);
    const contentTypeInfo = contentTypesMap[location.ContentInfo.Content.ContentType._href];
    const { isContainer } = contentTypeInfo;
    const { checkIsSelectable, checkIsSelected, checkIsSelectionBlocked, checkIsDeselectionBlocked } = useSelectedLocationsHelpers();
    const isSelected = checkIsSelected(location);
    const isNotSelectable = !checkIsSelectable(location);
    const isSelectionBlocked = checkIsSelectionBlocked(location);
    const isDeselectionBlocked = checkIsDeselectionBlocked(location);
    const className = createCssClassNames({
        'ibexa-grid-view-item': true,
        'ibexa-grid-view-item--marked': markedLocationId === location.id,
        'ibexa-grid-view-item--not-selectable': isNotSelectable,
        'ibexa-grid-view-item--selected': isSelected && !multiple,
        'ibexa-grid-view-item--hidden': location.hidden,
    });
    const markLocation = ({ nativeEvent }) => {
        if (isSelectionButtonClicked(nativeEvent)) {
            return;
        }

        setMarkedLocationId(location.id);
        dispatchLoadedLocationsAction({ type: 'CUT_LOCATIONS', locationId: location.id });
        dispatchLoadedLocationsAction({ type: 'UPDATE_LOCATIONS', data: { parentLocationId: location.id, subitems: [] } });

        if (!multiple) {
            dispatchSelectedLocationsAction({ type: 'CLEAR_SELECTED_LOCATIONS' });

            if (!isNotSelectable) {
                dispatchSelectedLocationsAction({ type: 'ADD_SELECTED_LOCATION', location });
            }
        }
    };
    const loadLocation = ({ nativeEvent }) => {
        if (isSelectionButtonClicked(nativeEvent) || (containersOnly && !isContainer)) {
            return;
        }

        dispatchLoadedLocationsAction({ type: 'UPDATE_LOCATIONS', data: { parentLocationId: location.id, subitems: [] } });
        setGridActiveLocationId(location.id);
    };
    const renderToggleSelection = () => {
        return (
            <div className="ibexa-grid-view-item__checkbox">
                <ToggleSelection
                    location={location}
                    multiple={multiple}
                    isDisabled={isSelectionBlocked || isDeselectionBlocked}
                    isHidden={isNotSelectable}
                />
            </div>
        );
    };

    return (
        <div className={className} onClick={markLocation} onDoubleClick={loadLocation}>
            <div className="ibexa-grid-view-item__image-wrapper">
                <Thumbnail
                    thumbnailData={version.Thumbnail}
                    iconExtraClasses="ibexa-icon--extra-large"
                    contentTypeIconPath={contentTypesMap[location.ContentInfo.Content.ContentType._href].thumbnail}
                />
            </div>
            <div className="ibexa-grid-view-item__footer">
                <div className="ibexa-grid-view-item__title" title={location.ContentInfo.Content.TranslatedName}>
                    {location.ContentInfo.Content.TranslatedName}
                    {location.hidden && <Icon name="view-hide" extraClasses="ibexa-icon--small ibexa-grid-view-item__hidden-icon" />}
                </div>
            </div>
            {renderToggleSelection()}
        </div>
    );
};

GridViewItem.propTypes = {
    location: PropTypes.object.isRequired,
    version: PropTypes.object,
};

export default GridViewItem;
