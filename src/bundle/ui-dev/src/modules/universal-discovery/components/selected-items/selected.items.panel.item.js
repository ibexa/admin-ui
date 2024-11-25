import React, { useContext, useEffect, useMemo, useRef } from 'react';

import {
    parse as parseTooltip,
    hideAll as hideAllTooltips,
} from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/tooltips.helper';
import { getAdminUiConfig, getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

import Icon from '../../../common/icon/icon';
import Thumbnail from '../../../common/thumbnail/thumbnail';

import { SelectedItemsContext } from '../../universal.discovery.module';

import { REMOVE_SELECTED_ITEMS } from '../../hooks/useSelectedItemsReducer';

const SelectedItemsPanelItem = ({ item, thumbnailData, name, description }) => {
    const adminUiConfig = getAdminUiConfig();
    const Translator = getTranslator();
    const refSelectedLocationsItem = useRef(null);
    const { dispatchSelectedItemsAction } = useContext(SelectedItemsContext);
    const removeItemLabel = Translator.trans(
        /*@Desc("Clear selection")*/ 'selected_items_panel.item.remove_item',
        {},
        'ibexa_universal_discovery_widget',
    );
    const removeFromSelection = () => {
        hideAllTooltips(refSelectedLocationsItem.current);
        dispatchSelectedItemsAction({ type: REMOVE_SELECTED_ITEMS, ids: [{ id: item.id, type: item.type }] });
    };
    const sortedActions = useMemo(() => {
        const { universalSelectItemActions } = adminUiConfig.universalDiscoveryWidget;
        const actions = universalSelectItemActions ? [...universalSelectItemActions] : [];

        return actions.sort((actionA, actionB) => {
            return actionB.priority - actionA.priority;
        });
    }, []);

    useEffect(() => {
        parseTooltip(refSelectedLocationsItem.current);
    }, []);

    return (
        <div className="c-selected-items-panel-item" ref={refSelectedLocationsItem}>
            <div className="c-selected-items-panel-item__image-wrapper">
                <Thumbnail thumbnailData={thumbnailData} iconExtraClasses="ibexa-icon--small" />
            </div>
            <div className="c-selected-items-panel-item__info">
                <span className="c-selected-items-panel-item__info-name">{name}</span>
                <span className="c-selected-items-panel-item__info-description">{description}</span>
            </div>
            <div className="c-selected-items-panel-item__actions-wrapper">
                {sortedActions.map((action) => {
                    const Component = action.component;

                    return <Component key={action.id} item={item} />;
                })}
                <button
                    type="button"
                    className="c-selected-items-panel-item__remove-button btn ibexa-btn ibexa-btn--ghost ibexa-btn--no-text"
                    onClick={removeFromSelection}
                    title={removeItemLabel}
                    data-tooltip-container-selector=".c-udw-tab"
                >
                    <Icon name="discard" extraClasses="ibexa-icon--tiny-small" />
                </button>
            </div>
        </div>
    );
};

SelectedItemsPanelItem.propTypes = {
    item: PropTypes.object.isRequired,
    thumbnailData: PropTypes.shape({
        mimeType: PropTypes.string.isRequired,
        resource: PropTypes.string.isRequired,
    }).isRequired,
    name: PropTypes.string.isRequired,
    description: PropTypes.string.isRequired,
};

export default SelectedItemsPanelItem;
