import React, { useContext, useEffect } from 'react';
import PropTypes from 'prop-types';

import { parse as parseTooltip } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/tooltips.helper';

import ToggleSelection from '../toggle-selection/toggle.selection';
import Icon from '../../../common/icon/icon';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import { useSelectedLocationsHelpers } from '../../hooks/useSelectedLocationsHelpers';
import {
    MarkedLocationIdContext,
    LoadedLocationsMapContext,
    ContentTypesMapContext,
    SelectedLocationsContext,
    MultipleConfigContext,
} from '../../universal.discovery.module';

const { document } = window;

const FinderLeaf = ({ location }) => {
    const [markedLocationId, setMarkedLocationId] = useContext(MarkedLocationIdContext);
    const [loadedLocationsMap, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);
    const contentTypesMap = useContext(ContentTypesMapContext);
    const [, dispatchSelectedLocationsAction] = useContext(SelectedLocationsContext);
    const [multiple] = useContext(MultipleConfigContext);
    const { checkIsSelectable, checkIsSelected, checkIsSelectionBlocked } = useSelectedLocationsHelpers();
    const isSelected = checkIsSelected(location);
    const isNotSelectable = !checkIsSelectable(location);
    const isSelectionBlocked = checkIsSelectionBlocked(location);
    const markLocation = ({ nativeEvent }) => {
        const isSelectionButtonClicked = nativeEvent.target.closest('.c-udw-toggle-selection');
        const isMarkedLocationClicked = location.id === markedLocationId;

        if (isSelectionButtonClicked || isMarkedLocationClicked) {
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
    const renderToggleSelection = () => {
        return <ToggleSelection location={location} multiple={multiple} isDisabled={isSelectionBlocked} isHidden={isNotSelectable} />;
    };
    const className = createCssClassNames({
        'c-finder-leaf': true,
        'c-finder-leaf--marked': !!loadedLocationsMap.find((loadedLocation) => loadedLocation.parentLocationId === location.id),
        'c-finder-leaf--has-children': !!location.childCount,
        'c-finder-leaf--not-selectable': isNotSelectable,
        'c-finder-leaf--selected': isSelected && !multiple,
    });

    useEffect(() => {
        parseTooltip(document.querySelector('.c-udw-tab'));
    }, []);

    return (
        <div className={className} onClick={markLocation}>
            {renderToggleSelection()}
            <span className="c-finder-leaf__name">
                <span className="c-finder-leaf__icon-wrapper">
                    <Icon
                        extraClasses="ibexa-icon--small ibexa-icon--base-dark"
                        customPath={contentTypesMap[location.ContentInfo.Content.ContentType._href].thumbnail}
                    />
                </span>
                <span title={location.ContentInfo.Content.TranslatedName} data-tooltip-container-selector=".c-udw-tab">
                    {location.ContentInfo.Content.TranslatedName}
                </span>
            </span>
        </div>
    );
};

FinderLeaf.propTypes = {
    location: PropTypes.object.isRequired,
};

export default FinderLeaf;
