import React, { useContext, useState, useEffect, useRef } from 'react';

import {
    parse as parseTooltip,
    hideAll as hideAllTooltips,
} from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/tooltips.helper';
import { getBootstrap, getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

import Icon from '../../../common/icon/icon';
import SelectedLocationsItem from './selected.locations.item';
import { createCssClassNames } from '../../../common/helpers/css.class.names';

import { SelectionConfigContext, SelectedLocationsContext, AllowConfirmationContext } from '../../universal.discovery.module';

const SelectedLocations = () => {
    const Translator = getTranslator();
    const refSelectedLocations = useRef(null);
    const refTogglerButton = useRef(null);
    const { isInitLocationsDeselectionBlocked, initSelectedLocationsIds } = useContext(SelectionConfigContext);
    const [selectedLocations, dispatchSelectedLocationsAction] = useContext(SelectedLocationsContext);
    const allowConfirmation = useContext(AllowConfirmationContext);
    const [isComponentHidden, setIsComponentHidden] = useState(true);
    const [initSelectedLocations, setInitSelectedLocations] = useState([]);
    const [selectedLocationsWithoutInit, setSelectedLocationsWithoutInit] = useState([]);
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

        if (isInitLocationsDeselectionBlocked) {
            dispatchSelectedLocationsAction({ type: 'REPLACE_SELECTED_LOCATIONS', locations: initSelectedLocations });

            return;
        }

        dispatchSelectedLocationsAction({ type: 'CLEAR_SELECTED_LOCATIONS' });
    };
    const toggleExpanded = () => {
        setIsExpanded(!isExpanded);
    };
    const renderSelectionCounter = () => {
        const selectedLocationsCount = isInitLocationsDeselectionBlocked ? selectedLocationsWithoutInit.length : selectedLocations.length;
        const selectedLabel = Translator.transChoice(
            /*@Desc("{1}%count% selected item|[2,Inf]%count% selected items")*/ 'selected_locations.selected_items',
            selectedLocations.length,
            { count: selectedLocationsCount },
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
            /*@Desc("{1}Deselect|[2,Inf]Deselect all")*/ 'selected_locations.deselect_all',
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

        const selectedLocationsToIterate = isInitLocationsDeselectionBlocked ? selectedLocationsWithoutInit : selectedLocations;

        return (
            <div className="c-selected-locations__items-wrapper">
                {renderActionButtons()}
                <div className="c-selected-locations__items-list">
                    {selectedLocationsToIterate.map((selectedLocation) => (
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
        if (isComponentHidden) {
            return;
        }

        parseTooltip(refSelectedLocations.current);
        hideAllTooltips();

        const bootstrap = getBootstrap();
        const toggleButtonTooltip = bootstrap.Tooltip.getOrCreateInstance('.c-selected-locations__toggle-button');

        toggleButtonTooltip.setContent({ '.tooltip-inner': togglerLabel });
    }, [isExpanded]);

    useEffect(() => {
        if (isInitLocationsDeselectionBlocked) {
            const initSelectedLocationsTemp = [];
            const selectedLocationsWithoutInitTemp = [];

            selectedLocations.forEach((selectedLocation) => {
                if (initSelectedLocationsIds.includes(selectedLocation.location.id)) {
                    initSelectedLocationsTemp.push(selectedLocation);
                } else {
                    selectedLocationsWithoutInitTemp.push(selectedLocation);
                }
            });

            setInitSelectedLocations(initSelectedLocationsTemp);
            setSelectedLocationsWithoutInit(selectedLocationsWithoutInitTemp);
        }

        const onlyInitSelectedLocationsAreSelected = initSelectedLocationsIds.length === selectedLocations.length;

        setIsComponentHidden(!allowConfirmation || (onlyInitSelectedLocationsAreSelected && isInitLocationsDeselectionBlocked));
    }, [selectedLocations, isInitLocationsDeselectionBlocked, allowConfirmation]);

    if (isComponentHidden) {
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
