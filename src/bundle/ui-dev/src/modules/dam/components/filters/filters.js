import React, { useContext, useState, useEffect, useCallback, useRef } from 'react';
import ReactDOM from 'react-dom';
import PropTypes from 'prop-types';

import Dropdown from '../../../common/dropdown/dropdown';
import FiltersGroup from './filters.group';

const Filters = ({ search }) => {
    const filtersLabel = Translator.trans(/*@Desc("Filters")*/ 'filters.title', {}, 'ibexa_universal_discovery_widget');
    const languageLabel = Translator.trans(/*@Desc("Language")*/ 'filters.language', {}, 'ibexa_universal_discovery_widget');
    const sectionLabel = Translator.trans(/*@Desc("Section")*/ 'filters.section', {}, 'ibexa_universal_discovery_widget');
    const subtreeLabel = Translator.trans(/*@Desc("Subtree")*/ 'filters.subtree', {}, 'ibexa_universal_discovery_widget');
    const clearLabel = Translator.trans(/*@Desc("Clear")*/ 'filters.clear', {}, 'ibexa_universal_discovery_widget');
    const applyLabel = Translator.trans(/*@Desc("Apply")*/ 'filters.apply', {}, 'ibexa_universal_discovery_widget');

    return (
        <>
            <div className="c-filters">
                <div className="c-filters__header">
                    <div className="c-filters__header-content">{filtersLabel}</div>
                    <div className="c-filters__header-actions">
                        <button className="btn ibexa-btn ibexa-btn--ghost ibexa-btn--small" type="button">
                            {clearLabel}
                        </button>
                        <button type="submit" className="btn ibexa-btn ibexa-btn--secondary ibexa-btn--small ibexa-btn--apply">
                            {applyLabel}
                        </button>
                    </div>
                </div>
                <FiltersGroup title="Translations">
                    <ul className="c-filters__collapsible-list">
                        <li key={'c'} className="c-filters__collapsible-list-item">
                            <div className="form-check form-check-inline">
                                <input
                                    type="checkbox"
                                    id={`ibexa-search-content-type-`}
                                    className="ibexa-input ibexa-input--checkbox form-check-input"
                                    value={'s'}
                                />
                                <label className="checkbox-inline form-check-label" htmlFor={`ibexa-search-content-type-`}>
                                    s
                                </label>
                            </div>
                        </li>
                    </ul>
                </FiltersGroup>
                <FiltersGroup title="Format and size">
                    <ul className="c-filters__collapsible-list">
                        <li key={'c'} className="c-filters__collapsible-list-item">
                            <div className="form-check form-check-inline">
                                <input
                                    type="checkbox"
                                    id={`ibexa-search-content-type-`}
                                    className="ibexa-input ibexa-input--checkbox form-check-input"
                                    value={'s'}
                                />
                                <label className="checkbox-inline form-check-label" htmlFor={`ibexa-search-content-type-`}>
                                    s
                                </label>
                            </div>
                        </li>
                    </ul>
                </FiltersGroup>
                <FiltersGroup title="Orientation and dimensions">
                    <ul className="c-filters__collapsible-list">
                        <li key={'c'} className="c-filters__collapsible-list-item">
                            <div className="form-check form-check-inline">
                                <input
                                    type="checkbox"
                                    id={`ibexa-search-content-type-`}
                                    className="ibexa-input ibexa-input--checkbox form-check-input"
                                    value={'s'}
                                />
                                <label className="checkbox-inline form-check-label" htmlFor={`ibexa-search-content-type-`}>
                                    s
                                </label>
                            </div>
                        </li>
                    </ul>
                </FiltersGroup>
            </div>
        </>
    );
};

Filters.propTypes = {
    search: PropTypes.func.isRequired,
};

export default Filters;
