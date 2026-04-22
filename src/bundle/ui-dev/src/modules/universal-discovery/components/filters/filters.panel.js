import React from 'react';
import PropTypes from 'prop-types';

import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { Button, ButtonType, ButtonSize } from '@ids-components/components/Button';

const FiltersPanel = ({ children = null, isApplyButtonEnabled, makeSearch, clearFilters }) => {
    const Translator = getTranslator();
    const filtersLabel = Translator.trans(/* @Desc("Filters") */ 'filters.title', {}, 'ibexa_universal_discovery_widget');
    const clearLabel = Translator.trans(/* @Desc("Clear") */ 'filters.clear', {}, 'ibexa_universal_discovery_widget');
    const applyLabel = Translator.trans(/* @Desc("Apply") */ 'filters.apply', {}, 'ibexa_universal_discovery_widget');

    return (
        <div className="c-filters-panel">
            <div className="c-filters-panel__header">
                <div className="c-filters-panel__header-content">{filtersLabel}</div>
                <div className="c-filters-panel__header-actions">
                    <Button type={ButtonType.TertiaryAlt} size={ButtonSize.Small} onClick={clearFilters}>
                        {clearLabel}
                    </Button>
                    <Button
                        type={ButtonType.Secondary}
                        size={ButtonSize.Small}
                        onClick={makeSearch}
                        disabled={!isApplyButtonEnabled}
                        className="ids-btn--apply"
                    >
                        {applyLabel}
                    </Button>
                </div>
            </div>
            {children}
        </div>
    );
};

FiltersPanel.propTypes = {
    children: PropTypes.node,
    isApplyButtonEnabled: PropTypes.bool.isRequired,
    makeSearch: PropTypes.func.isRequired,
    clearFilters: PropTypes.func.isRequired,
};

export default FiltersPanel;
