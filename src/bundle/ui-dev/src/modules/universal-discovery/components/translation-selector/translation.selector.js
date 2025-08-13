import React, { useEffect, useMemo, useState } from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../../../common/helpers/css.class.names';

import { getAdminUiConfig, getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

const MIN_ITEMS_WITH_SEARCH = 10;

const TranslationSelectorButton = ({ hideTranslationSelector, selectTranslation, version, isOpen }) => {
    const Translator = getTranslator();
    const adminUiConfig = getAdminUiConfig();
    const [filterQuery, setFilterQuery] = useState('');
    const [activeLanguage, setActiveLanguage] = useState('');
    const languageCodes = version ? version.VersionInfo.languageCodes.split(',') : [];
    const isSearchEnabled = languageCodes.length >= MIN_ITEMS_WITH_SEARCH;
    const editTranslationLabel = Translator.trans(
        /*@Desc("Select translation")*/ 'meta_preview.edit_translation',
        {},
        'ibexa_universal_discovery_widget',
    );
    const resetLanguageSelector = () => {
        setFilterQuery('');
        setActiveLanguage('');
    };
    const getLanguageName = (languageCode, lowerCase = true) => {
        const language = window.ibexa.adminUiConfig.languages.mappings[languageCode];

        if (!language) {
            return null;
        }

        return lowerCase ? language.name.toLowerCase() : language.name;
    };

    const filteredLanguageCodes = useMemo(() => {
        if (!filterQuery) {
            return languageCodes;
        }

        const filterQueryLowerCase = filterQuery.toLowerCase();

        return languageCodes.filter((languageCode) => {
            const languageNameLowerCase = getLanguageName(languageCode);

            return languageNameLowerCase.includes(filterQueryLowerCase);
        });
    }, [filterQuery]);

    useEffect(() => {
        if (!isOpen) {
            resetLanguageSelector();
        }
    }, [isOpen]);

    const containerClassName = createCssClassNames({
        'c-translation-selector': true,
        'ibexa-extra-actions': true,
        'ibexa-extra-actions--edit': true,
        'ibexa-extra-actions--full-height': true,
        'ibexa-extra-actions--hidden': !isOpen,
        'ibexa-extra-actions--has-search': isSearchEnabled,
    });
    const searchInputWrapperClassName = createCssClassNames({
        'ibexa-instant-filter__input-wrapper': true,
        'ibexa-instant-filter__input-wrapper--hidden': !isSearchEnabled,
    });
    const renderLanguages = () => {
        return filteredLanguageCodes.map((languageCode) => {
            const languageNodeClassName = createCssClassNames({
                'ibexa-instant-filter__item': true,
                'ibexa-instant-filter__item--active': activeLanguage === languageCode,
            });

            return (
                <button type="button" key={languageCode} className={languageNodeClassName} onClick={() => setActiveLanguage(languageCode)}>
                    {adminUiConfig.languages.mappings[languageCode].name}
                </button>
            );
        });
    };

    return (
        <div className={containerClassName}>
            <div className="ibexa-extra-actions__header">
                <h2 className="ibexa-extra-actions__header-content">{editTranslationLabel}</h2>
            </div>
            <div className="ibexa-extra-actions__content">
                <div className="ibexa-instant-filter">
                    <div className={searchInputWrapperClassName}>
                        <input
                            type="text"
                            className="ibexa-instant-filter__input ibexa-input ibexa-input--text form-control"
                            placeholder={Translator.trans(
                                /*@Desc("Search...")*/ 'instant.filter.languages.placeholder',
                                {},
                                'ibexa_universal_discovery_widget',
                            )}
                            value={filterQuery}
                            onChange={(event) => setFilterQuery(event.target.value)}
                        />
                    </div>
                    <div className="ibexa-instant-filter__desc">
                        {Translator.trans(
                            /*@Desc("Languages")*/ 'meta_preview.instant.filter.languages.select_language.desc',
                            {},
                            'ibexa_universal_discovery_widget',
                        )}
                    </div>
                    <div className="ibexa-instant-filter__items">{renderLanguages()}</div>
                </div>
            </div>
            <div className="ibexa-extra-actions__confirm-wrapper">
                <button
                    type="submit"
                    className="btn ibexa-extra-actions__confirm-btn ibexa-btn ibexa-btn--primary"
                    disabled={!activeLanguage}
                    onClick={() => selectTranslation(activeLanguage)}
                >
                    {Translator.trans(/*@Desc("Edit")*/ 'meta_preview.edit.languages.edit', {}, 'ibexa_universal_discovery_widget')}
                </button>
                <button type="button" className="btn ibexa-btn--close ibexa-btn ibexa-btn--secondary" onClick={hideTranslationSelector}>
                    {Translator.trans(/*@Desc("Discard")*/ 'meta_preview.edit.languages.discard', {}, 'ibexa_universal_discovery_widget')}
                </button>
            </div>
        </div>
    );
};

TranslationSelectorButton.propTypes = {
    hideTranslationSelector: PropTypes.func.isRequired,
    selectTranslation: PropTypes.func.isRequired,
    version: PropTypes.object.isRequired,
    isOpen: PropTypes.bool.isRequired,
};

export default TranslationSelectorButton;
