import React, { useContext, useEffect, useMemo, useRef } from 'react';
import PropTypes from 'prop-types';

import {
    parse as parseTooltip,
    hideAll as hideAllTooltips,
} from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/tooltips.helper';

import Icon from '../../../common/icon/icon';
import Thumbnail from '../../../common/thumbnail/thumbnail';

import { SelectedLocationsContext, ContentTypesMapContext } from '../../universal.discovery.module';
import { getAdminUiConfig, getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { useSelectedLocationsHelpers } from '../../hooks/useSelectedLocationsHelpers';

const SelectedLocationsItem = ({ location, permissions }) => {
    const adminUiConfig = getAdminUiConfig();
    const Translator = getTranslator();
    const refSelectedLocationsItem = useRef(null);
    const [, dispatchSelectedLocationsAction] = useContext(SelectedLocationsContext);
    const { checkIsDeselectionBlocked } = useSelectedLocationsHelpers();
    const isDeselectionBlocked = checkIsDeselectionBlocked(location);
    const contentTypesMap = useContext(ContentTypesMapContext);
    const clearLabel = Translator.trans(
        /*@Desc("Clear selection")*/ 'selected_locations.clear_selection',
        {},
        'ibexa_universal_discovery_widget',
    );
    const removeFromSelection = () => {
        hideAllTooltips(refSelectedLocationsItem.current);
        dispatchSelectedLocationsAction({ type: 'REMOVE_SELECTED_LOCATION', id: location.id });
    };
    const sortedActions = useMemo(() => {
        const { selectedItemActions } = adminUiConfig.universalDiscoveryWidget;
        const actions = selectedItemActions ? [...selectedItemActions] : [];

        return actions.sort((actionA, actionB) => {
            return actionB.priority - actionA.priority;
        });
    }, []);
    const version = location.ContentInfo.Content.CurrentVersion.Version;
    const thumbnailData = version ? version.Thumbnail : {};

    useEffect(() => {
        parseTooltip(refSelectedLocationsItem.current);
    }, []);

    return (
        <div className="c-selected-locations-item" ref={refSelectedLocationsItem}>
            <div className="c-selected-locations-item__image-wrapper">
                <Thumbnail thumbnailData={thumbnailData} iconExtraClasses="ibexa-icon--small" />
            </div>
            <div className="c-selected-locations-item__info">
                <span className="c-selected-locations-item__info-name">{location.ContentInfo.Content.TranslatedName}</span>
                <span className="c-selected-locations-item__info-description">
                    {contentTypesMap[location.ContentInfo.Content.ContentType._href].name}
                </span>
            </div>
            <div className="c-selected-locations-item__actions-wrapper">
                {sortedActions.map((action) => {
                    const Component = action.component;

                    return <Component key={action.id} location={location} permissions={permissions} />;
                })}
                <button
                    type="button"
                    className="c-selected-locations-item__remove-button btn ibexa-btn ibexa-btn--ghost ibexa-btn--no-text"
                    onClick={removeFromSelection}
                    title={clearLabel}
                    data-tooltip-container-selector=".c-udw-tab"
                    disabled={isDeselectionBlocked}
                >
                    <Icon name="discard" extraClasses="ibexa-icon--tiny-small" />
                </button>
            </div>
        </div>
    );
};

SelectedLocationsItem.propTypes = {
    location: PropTypes.object.isRequired,
    permissions: PropTypes.object.isRequired,
};

export default SelectedLocationsItem;
