import React from 'react';
import PropTypes from 'prop-types';

import Icon from '../../../common/icon/icon';
import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

const SearchNoResults = ({ searchText }) => {
    const Translator = getTranslator();
    const noResultsLabel = Translator.trans(
        /* @Desc("No results found for %query%") */ 'search.no_results',
        { query: searchText },
        'ibexa_universal_discovery_widget',
    );
    const noResultsHints = [
        Translator.trans(
            /* @Desc("Check the spelling of keywords.") */ 'search.no_results.hint.check_spelling',
            {},
            'ibexa_universal_discovery_widget',
        ),
        Translator.trans(
            /* @Desc("Try more general keywords.") */ 'search.no_results.hint.more_general',
            {},
            'ibexa_universal_discovery_widget',
        ),
        Translator.trans(
            /* @Desc("Try different keywords.") */ 'search.no_results.hint.different_kewords',
            {},
            'ibexa_universal_discovery_widget',
        ),
        Translator.trans(
            /* @Desc("Try fewer keywords. Reducing keywords results in more matches.") */ 'search.no_results.hint.fewer_keywords',
            {},
            'ibexa_universal_discovery_widget',
        ),
    ];

    return (
        <div className="c-search-no-results">
            <img src="/bundles/ibexaadminui/img/no-results.svg" />
            <h2 className="c-search-no-results__no-results-title">{noResultsLabel}</h2>
            <div className="c-search-no-results__no-results-subtitle">
                {noResultsHints.map((hint, key) => (
                    <div
                        key={key} // eslint-disable-line react/no-array-index-key
                        className="c-search-no-results__no-results-hint"
                    >
                        <div className="c-search-no-results__no-results-hint-icon-wrapper">
                            <Icon name="approved" extraClasses="ibexa-icon--small-medium" />
                        </div>
                        <div className="c-search-no-results__no-results-hint-text">{hint}</div>
                    </div>
                ))}
            </div>
        </div>
    );
};

SearchNoResults.propTypes = {
    searchText: PropTypes.string.isRequired,
};

export default SearchNoResults;
