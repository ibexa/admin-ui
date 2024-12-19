import React from 'react';
import PropTypes from 'prop-types';

import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

const FiltersPanel = ({ children, isApplyButtonEnabled, makeSearch, clearFilters }) => {
    const Translator = getTranslator();
    const filtersLabel = Translator.trans(/*@Desc("Filters")*/ 'filters.title', {}, 'ibexa_universal_discovery_widget');
    const clearLabel = Translator.trans(/*@Desc("Clear")*/ 'filters.clear', {}, 'ibexa_universal_discovery_widget');
    const applyLabel = Translator.trans(/*@Desc("Apply")*/ 'filters.apply', {}, 'ibexa_universal_discovery_widget');

    return (
        <div className="c-filters-panel">
            <div className="c-filters-panel__header">
                <div className="c-filters-panel__header-content">{filtersLabel}</div>
                <div className="c-filters-panel__header-actions">
                    <button className="btn ibexa-btn ibexa-btn--ghost ibexa-btn--small" type="button" onClick={clearFilters}>
                        {clearLabel}
                    </button>
                    <button
                        type="button"
                        className="btn ibexa-btn ibexa-btn--secondary ibexa-btn--small ibexa-btn--apply"
                        onClick={makeSearch}
                        disabled={!isApplyButtonEnabled}
                    >
                        {applyLabel}
                    </button>
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

FiltersPanel.defaultProps = {
    children: null,
};

export default FiltersPanel;
