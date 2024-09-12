import React, { useContext, useState, useEffect, useRef } from 'react';

import {
    parse as parseTooltip,
    hideAll as hideAllTooltips,
} from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/tooltips.helper';
import { getBootstrap, getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

import Icon from '../../../common/icon/icon';
import SelectedLocationsItem from './selected.locations.item';
import { createCssClassNames } from '../../../common/helpers/css.class.names';

import { SelectedLocationsContext, AllowConfirmationContext } from '../../universal.discovery.module';

const SelectedLocations = () => {
    const Translator = getTranslator();
    const refSelectedLocations = useRef(null);
    const refTogglerButton = useRef(null);
    const [selectedLocations, dispatchSelectedLocationsAction] = useContext(SelectedLocationsContext);
    const allowConfirmation = useContext(AllowConfirmationContext);
    const [isExpanded, setIsExpanded] = useState(false);
    const className = createCssClassNames({
        'c-selected-locations': true,
        'c-selected-locations--expanded': isExpanded,
    });
    const expandLabel = Translator.trans(
        /*@Desc("Expand sidebar")*/ 'selected_locations.expand.sidebar',
        {},
        'ibexa_universal_discovery_widget',
    );
    const collapseLabel = Translator.trans(
        /*@Desc("Collapse sidebar")*/ 'selected_locations.collapse.sidebar',
        {},
        'ibexa_universal_discovery_widget',
    );
    const togglerLabel = isExpanded ? collapseLabel : expandLabel;
    const clearSelection = () => {
        hideAllTooltips(refSelectedLocations.current);
        dispatchSelectedLocationsAction({ type: 'CLEAR_SELECTED_LOCATIONS' });
    };
    const toggleExpanded = () => {
        setIsExpanded(!isExpanded);
    };
    const renderSelectionCounter = () => {
        const selectedLabel = Translator.transChoice(
            /*@Desc("{1}%count% selected item|[2,Inf]%count% selected items")*/ 'selected_locations.selected_items',
            selectedLocations.length,
            { count: selectedLocations.length },
            'ibexa_universal_discovery_widget',
        );

        return <div className="c-selected-locations__selection-counter">{selectedLabel}</div>;
    };
    const renderToggleButton = () => {
        const iconName = isExpanded ? 'caret-double-next' : 'caret-double-back';

        return (
            <button
                ref={refTogglerButton}
                type="button"
                className="c-selected-locations__toggle-button btn ibexa-btn ibexa-btn--ghost ibexa-btn--no-text"
                onClick={toggleExpanded}
                title={togglerLabel}
                data-tooltip-container-selector=".c-udw-tab"
            >
                <Icon name={iconName} extraClasses="ibexa-icon--small" />
            </button>
        );
    };
    const renderActionButtons = () => {
        const removeLabel = Translator.transChoice(
            /*@Desc("{1}Deselect|[2,Inf]Deselect all")*/ 'selected_locations.deselect',
            selectedLocations.length,
            {},
            'ibexa_universal_discovery_widget',
        );

        return (
            <div className="c-selected-locations__actions">
                <button
                    type="button"
                    className="c-selected-locations__clear-selection-button btn ibexa-btn ibexa-btn--small ibexa-btn--secondary"
                    onClick={clearSelection}
                >
                    {removeLabel}
                </button>
            </div>
        );
    };
    const renderLocationsList = () => {
        if (!isExpanded) {
            return null;
        }

        return (
            <div className="c-selected-locations__items-wrapper">
                {renderActionButtons()}
                <div className="c-selected-locations__items-list">
                    {selectedLocations.map((selectedLocation) => (
                        <SelectedLocationsItem
                            key={selectedLocation.location.id}
                            location={selectedLocation.location}
                            permissions={selectedLocation.permissions}
                        />
                    ))}
                </div>
            </div>
        );
    };

    useEffect(() => {
        if (!allowConfirmation) {
            return;
        }

        parseTooltip(refSelectedLocations.current);
        hideAllTooltips();

        const bootstrap = getBootstrap();
        const toggleButtonTooltip = bootstrap.Tooltip.getOrCreateInstance('.c-selected-locations__toggle-button');

        toggleButtonTooltip.setContent({ '.tooltip-inner': togglerLabel });
    }, [isExpanded]);

    if (!allowConfirmation) {
        return null;
    }

    return (
        <div className={className} ref={refSelectedLocations}>
            <div className="c-selected-locations__header">
                {renderSelectionCounter()}
                {renderToggleButton()}
            </div>
            {renderLocationsList()}
        </div>
    );
};

export default SelectedLocations;
