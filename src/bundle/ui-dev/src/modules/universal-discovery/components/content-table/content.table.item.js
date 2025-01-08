import React, { useContext } from 'react';
import PropTypes from 'prop-types';

import ToggleSelection from '../toggle-selection/toggle.selection';
import Icon from '../../../common/icon/icon';

import { formatShortDateTime } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/timezone.helper';
import { createCssClassNames } from '../../../common/helpers/css.class.names';
import { loadAccordionData } from '../../services/universal.discovery.service';
import { useSelectedLocationsHelpers } from '../../hooks/useSelectedLocationsHelpers';
import {
    RestInfoContext,
    CurrentViewContext,
    SortingContext,
    SortOrderContext,
    LoadedLocationsMapContext,
    MarkedLocationIdContext,
    ContentTypesMapContext,
    SelectedLocationsContext,
    MultipleConfigContext,
    RootLocationIdContext,
} from '../../universal.discovery.module';

const ContentTableItem = ({ location }) => {
    const restInfo = useContext(RestInfoContext);
    const [currentView] = useContext(CurrentViewContext);
    const [sorting] = useContext(SortingContext);
    const [sortOrder] = useContext(SortOrderContext);
    const [, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);
    const [markedLocationId, setMarkedLocationId] = useContext(MarkedLocationIdContext);
    const contentTypesMap = useContext(ContentTypesMapContext);
    const [, dispatchSelectedLocationsAction] = useContext(SelectedLocationsContext);
    const [multiple] = useContext(MultipleConfigContext);
    const rootLocationId = useContext(RootLocationIdContext);
    const contentTypeInfo = contentTypesMap[location.ContentInfo.Content.ContentType._href];
    const { checkIsSelectable, checkIsSelectionBlocked } = useSelectedLocationsHelpers();
    const isNotSelectable = !checkIsSelectable(location);
    const isSelectionBlocked = checkIsSelectionBlocked(location);
    const className = createCssClassNames({
        'ibexa-table__row c-content-table-item': true,
        'c-content-table-item--marked': markedLocationId === location.id,
        'c-content-table-item--not-selectable': isNotSelectable,
    });
    const markLocation = ({ nativeEvent }) => {
        const isSelectionButtonClicked = nativeEvent.target.closest('.c-udw-toggle-selection');

        if (isSelectionButtonClicked) {
            return;
        }

        dispatchLoadedLocationsAction({
            type: 'SET_LOCATIONS',
            data: [
                {
                    parentLocationId: null,
                    count: 1,
                    subitems: [{ location }],
                },
            ],
        });
        setMarkedLocationId(location.id);
        loadAccordionData(
            {
                ...restInfo,
                parentLocationId: location.id,
                sortClause: sorting,
                sortOrder: sortOrder,
                gridView: currentView === 'grid',
                rootLocationId,
            },
            (locationsMap) => {
                dispatchLoadedLocationsAction({ type: 'SET_LOCATIONS', data: locationsMap });
            },
        );

        if (!multiple) {
            dispatchSelectedLocationsAction({ type: 'CLEAR_SELECTED_LOCATIONS' });

            if (!isNotSelectable) {
                dispatchSelectedLocationsAction({ type: 'ADD_SELECTED_LOCATION', location: location });
            }
        }
    };
    const renderToggleSelection = () => {
        return <ToggleSelection location={location} multiple={multiple} isDisabled={isSelectionBlocked} isHidden={isNotSelectable} />;
    };

    return (
        <tr className={className} onClick={markLocation}>
            {multiple && <td className="ibexa-table__cell ibexa-table__cell--has-checkbox">{renderToggleSelection()}</td>}
            <td className="ibexa-table__cell c-content-table-item__icon-wrapper">
                <Icon extraClasses="ibexa-icon--small" customPath={contentTypeInfo.thumbnail} />
            </td>
            <td className="ibexa-table__cell">{location.ContentInfo.Content.TranslatedName}</td>
            <td className="ibexa-table__cell">{formatShortDateTime(new Date(location.ContentInfo.Content.lastModificationDate))}</td>
            <td className="ibexa-table__cell">{contentTypeInfo.name}</td>
        </tr>
    );
};

ContentTableItem.propTypes = {
    location: PropTypes.object.isRequired,
};

export default ContentTableItem;
