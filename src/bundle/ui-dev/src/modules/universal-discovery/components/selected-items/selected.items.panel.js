import React, { useContext, useState, useEffect, useRef, useMemo } from 'react';

import {
    parse as parseTooltip,
    hideAll as hideAllTooltips,
} from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/tooltips.helper';
import {
    getBootstrap,
    getAdminUiConfig,
    getTranslator,
} from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

import Icon from '../../../common/icon/icon';
import { createCssClassNames } from '../../../common/helpers/css.class.names';
import { Button, ButtonType, ButtonSize } from '@ids-components/components/Button';

import { AllowConfirmationContext, SelectedItemsContext } from '../../universal.discovery.module';

import { CLEAR_SELECTED_ITEMS } from '../../hooks/useSelectedItemsReducer';

const SelectedItemsPanel = () => {
    const Translator = getTranslator();
    const adminUiConfig = getAdminUiConfig();
    const itemsComponentsMap = useMemo(() => {
        const { universalSelectItemsComponentsConfigs } = adminUiConfig.universalDiscoveryWidget;
        const configsArray = universalSelectItemsComponentsConfigs ? [...universalSelectItemsComponentsConfigs] : [];

        return configsArray.reduce((configsMap, config) => {
            configsMap[config.itemType] = config;

            return configsMap;
        }, {});
    }, [adminUiConfig]);
    const refSelectedLocations = useRef(null);
    const { selectedItems, dispatchSelectedItemsAction } = useContext(SelectedItemsContext);
    const allowConfirmation = useContext(AllowConfirmationContext);
    const [isExpanded, setIsExpanded] = useState(false);
    const className = createCssClassNames({
        'c-selected-items-panel': true,
        'c-selected-items-panel--expanded': isExpanded,
    });
    const expandLabel = Translator.trans(
        /* @Desc("Expand sidebar") */ 'selected_items.expand.sidebar',
        {},
        'ibexa_universal_discovery_widget',
    );
    const collapseLabel = Translator.trans(
        /* @Desc("Collapse sidebar") */ 'selected_items.collapse.sidebar',
        {},
        'ibexa_universal_discovery_widget',
    );
    const togglerLabel = isExpanded ? collapseLabel : expandLabel;
    const clearSelection = () => {
        hideAllTooltips(refSelectedLocations.current);
        dispatchSelectedItemsAction({ type: CLEAR_SELECTED_ITEMS });
    };
    const toggleExpanded = () => {
        setIsExpanded(!isExpanded);
    };
    const renderSelectionCounter = () => {
        const selectedLabel = Translator.transChoice(
            /* @Desc("{1}%count% selected item|[2,Inf]%count% selected items") */ 'selected_items.selection_info',
            selectedItems.length,
            { count: selectedItems.length },
            'ibexa_universal_discovery_widget',
        );

        return <div className="c-selected-items-panel__selection-counter">{selectedLabel}</div>;
    };
    const renderToggleBtn = () => {
        return (
            <Button
                type={ButtonType.Tertiary}
                size={ButtonSize.Small}
                icon="expand-left"
                onClick={toggleExpanded}
                title={togglerLabel}
                className="c-selected-items-panel__toggle-button"
                extraAria={{ 'data-tooltip-container-selector': '.c-udw-tab' }}
            />
        );
    };
    const renderActionBtns = () => {
        const removeLabel = Translator.transChoice(
            /* @Desc("{1}Deselect|[2,Inf]Deselect all") */ 'selected_items.deselect_all',
            selectedItems.length,
            {},
            'ibexa_universal_discovery_widget',
        );

        return (
            <div className="c-selected-items-panel__actions">
                <Button
                    type={ButtonType.Secondary}
                    size={ButtonSize.Small}
                    onClick={clearSelection}
                    className="c-selected-items-panel__clear-selection-button"
                >
                    {removeLabel}
                </Button>
            </div>
        );
    };
    const renderLocationsList = () => {
        if (!isExpanded) {
            return null;
        }

        return (
            <div className="c-selected-items-panel__items-wrapper">
                {renderActionBtns()}
                <div className="c-selected-items-panel__items-list">
                    {selectedItems.map((selectedItem) => {
                        const ItemComponent = itemsComponentsMap[selectedItem.type].component;

                        if (!ItemComponent) {
                            throw new Error(`SelectedItemsPanel: component for ${selectedItem.type} not provided in configuration.`);
                        }

                        return <ItemComponent key={`${selectedItem.type}-${selectedItem.id}`} item={selectedItem} />;
                    })}
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
        const toggleBtnTooltip = bootstrap.Tooltip.getOrCreateInstance('.c-selected-items-panel__toggle-button');

        toggleBtnTooltip.setContent({ '.tooltip-inner': togglerLabel });
    }, [isExpanded]);

    if (!allowConfirmation) {
        return null;
    }

    return (
        <div className={className} ref={refSelectedLocations}>
            <div className="c-selected-items-panel__header">
                {renderToggleBtn()}
                {renderSelectionCounter()}
            </div>
            {renderLocationsList()}
        </div>
    );
};

export default SelectedItemsPanel;
