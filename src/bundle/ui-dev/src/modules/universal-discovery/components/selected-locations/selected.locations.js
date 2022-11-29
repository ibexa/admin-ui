import React, { useContext, useState, useEffect, useRef } from 'react';

import Icon from '../../../common/icon/icon';
import SelectedLocationsItem from './selected.locations.item';
import { createCssClassNames } from '../../../common/helpers/css.class.names';

import { SelectedLocationsContext, AllowConfirmationContext } from '../../universal.discovery.module';

const { Translator, ibexa } = window;

const SelectedLocations = () => {
    const refSelectedLocations = useRef(null);
    const refTogglerButton = useRef(null);
    const [selectedLocations, dispatchSelectedLocationsAction] = useContext(SelectedLocationsContext);
    const allowConfirmation = useContext(AllowConfirmationContext);
    const [isExpanded, setIsExpanded] = useState(false);
    const className = createCssClassNames({
        'c-selected-locations': true,
        'c-selected-locations--expanded': isExpanded,
    });
    const expandLabel = Translator.trans(/*@Desc("Expand sidebar")*/ 'selected_locations.expand.sidebar', {}, 'universal_discovery_widget');
    const collapseLabel = Translator.trans(
        /*@Desc("Collapse sidebar")*/ 'selected_locations.collapse.sidebar',
        {},
        'universal_discovery_widget',
    );
    const togglerLabel = isExpanded ? collapseLabel : expandLabel;
    const clearSelection = () => {
        ibexa.helpers.tooltips.hideAll(refSelectedLocations.current);
        dispatchSelectedLocationsAction({ type: 'CLEAR_SELECTED_LOCATIONS' });
    };
    const toggleExpanded = () => {
        setIsExpanded(!isExpanded);
    };
    const renderSelectionCounter = () => {
        const selectedLabel = Translator.trans(
            /*@Desc("%count% selected item(s)")*/ 'selected_locations.selected_items',
            { count: selectedLocations.length },
            'universal_discovery_widget',
        );

        return <div className="c-selected-locations__selection-counter">{selectedLabel}</div>;
    };
    const renderToggleButton = () => {
        const iconName = isExpanded ? 'caret-double-back' : 'caret-double-next';

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
        const removeAllLabel = Translator.trans(
            /*@Desc("Deselect all")*/ 'selected_locations.deselect_all',
            {},
            'universal_discovery_widget',
        );

        return (
            <div className="c-selected-locations__actions">
                <button
                    type="button"
                    className="c-selected-locations__clear-selection-button btn ibexa-btn ibexa-btn--small ibexa-btn--secondary"
                    onClick={clearSelection}
                >
                    {removeAllLabel}
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
        ibexa.helpers.tooltips.parse(refSelectedLocations.current);
        ibexa.helpers.tooltips.hideAll();

        if (refTogglerButton.current) {
            refTogglerButton.current.dataset.originalTitle = togglerLabel;
        }
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
