import React from 'react';
import PropTypes from 'prop-types';

import Icon from '../../../common/icon/icon';

import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

import { createCssClassNames } from '@ibexa-admin-ui/src/bundle/ui-dev/src/modules/common/helpers/css.class.names';

const SearchNoResults = ({ searchText, noResultsHints: noResultsHintsCustom, extraClasses }) => {
    const Translator = getTranslator();
    const className = createCssClassNames({
        'c-search-no-results': true,
        [extraClasses]: true,
    });
    const noResultsLabel = searchText
        ? Translator.trans(
              /* @Desc("No results found for %query%") */ 'search.no_results',
              { query: searchText },
              'ibexa_universal_discovery_widget',
          )
        : Translator.trans(/* @Desc("No results found") */ 'search.no_results_without_query', {}, 'ibexa_universal_discovery_widget');
    const noResultsHints = noResultsHintsCustom
        ? noResultsHintsCustom
        : [
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
        <div className={className}>
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
    extraClasses: PropTypes.string,
    searchText: PropTypes.string,
    noResultsHints: PropTypes.arrayOf(PropTypes.string),
};

SearchNoResults.defaultProps = {
    extraClasses: '',
    searchText: null,
    noResultsHints: null,
};

export default SearchNoResults;
