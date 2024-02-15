import React, { useContext } from 'react';
import PropTypes from 'prop-types';

import ToggleSelection from '../toggle-selection/toggle.selection';
import Thumbnail from '../../../common/thumbnail/thumbnail';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import {
    LoadedLocationsMapContext,
    MarkedLocationIdContext,
    ContentTypesMapContext,
    SelectedLocationsContext,
    MultipleConfigContext,
    ContainersOnlyContext,
    AllowedContentTypesContext,
} from '../../universal.discovery.module';
import { ActiveLocationIdContext } from './grid.view';

const isSelectionButtonClicked = (event) => {
    return event.target.closest('.c-udw-toggle-selection');
};

const GridViewItem = ({ location, version }) => {
    const [, setActiveLocationId] = useContext(ActiveLocationIdContext);
    const [markedLocationId, setMarkedLocationId] = useContext(MarkedLocationIdContext);
    const [, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);
    const contentTypesMap = useContext(ContentTypesMapContext);
    const [selectedLocations, dispatchSelectedLocationsAction] = useContext(SelectedLocationsContext);
    const [multiple] = useContext(MultipleConfigContext);
    const containersOnly = useContext(ContainersOnlyContext);
    const allowedContentTypes = useContext(AllowedContentTypesContext);
    const contentTypeInfo = contentTypesMap[location.ContentInfo.Content.ContentType._href];
    const { isContainer } = contentTypeInfo;
    const isSelected = selectedLocations.some((selectedLocation) => selectedLocation.location.id === location.id);
    const isNotSelectable =
        (containersOnly && !isContainer) || (allowedContentTypes && !allowedContentTypes.includes(contentTypeInfo.identifier));
    const className = createCssClassNames({
        'ibexa-grid-view-item': true,
        'ibexa-grid-view-item--marked': markedLocationId === location.id,
        'ibexa-grid-view-item--not-selectable': isNotSelectable,
        'ibexa-grid-view-item--selected': isSelected && !multiple,
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
        setActiveLocationId(location.id);
    };
    const renderToggleSelection = () => {
        return (
            <div className="ibexa-grid-view-item__checkbox">
                <ToggleSelection location={location} multiple={multiple} isHidden={isNotSelectable} />
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

GridViewItem.defaultProps = {
    version: {},
};

export default GridViewItem;
