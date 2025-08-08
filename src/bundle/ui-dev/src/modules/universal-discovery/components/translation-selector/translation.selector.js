import React, { useEffect, useRef, useState } from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../../../common/helpers/css.class.names';

import { getAdminUiConfig, getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

const MIN_ITEMS_WITH_SEARCH = 10;
const FILTER_TIMEOUT = 200;

const TranslationSelectorButton = ({ hideTranslationSelector, selectTranslation, version, isOpen }) => {
    const Translator = getTranslator();
    const adminUiConfig = getAdminUiConfig();
    const _refInstantFilter = useRef(null);
    const [filterQuery, setFilterQuery] = useState('');
    const [itemsMap, setItemsMap] = useState([]);
    const [activeLanguage, setActiveLanguage] = useState('');
    const languageCodes = version ? version.VersionInfo.languageCodes.split(',') : [];
    const hasSearchEnabled = languageCodes.length >= MIN_ITEMS_WITH_SEARCH;
    const editTranslationLabel = Translator.trans(
        /*@Desc("Select translation")*/ 'meta_preview.edit_translation',
        {},
        'ibexa_universal_discovery_widget',
    );
    const resetLanguageSelector = () => {
        setFilterQuery('');
        setActiveLanguage('');
    };

    let filterTimeout = null;

    useEffect(() => {
        const items = [..._refInstantFilter.current.querySelectorAll('.ibexa-instant-filter__item')];
        const itemsMapNext = items.map((item) => ({
            label: item.textContent.toLowerCase(),
            element: item,
        }));

        setItemsMap(itemsMapNext);
    }, []);

    useEffect(() => {
        const filterQueryLowerCase = filterQuery.toLowerCase();

        filterTimeout = window.setTimeout(() => {
            itemsMap.forEach((item) => {
                const methodName = item.label.includes(filterQueryLowerCase) ? 'removeAttribute' : 'setAttribute';

                item.element[methodName]('hidden', true);
            });
        }, FILTER_TIMEOUT);

        return () => {
            window.clearTimeout(filterTimeout);
        };
    }, [filterQuery]);

    useEffect(() => {
        if (!isOpen) {
            resetLanguageSelector();
        }
    }, [isOpen]);

    const className = createCssClassNames({
        'c-translation-selector': true,
        'ibexa-extra-actions': true,
        'ibexa-extra-actions--edit': true,
        'ibexa-extra-actions--full-height': true,
        'ibexa-extra-actions--hidden': !isOpen,
        'ibexa-extra-actions--has-search': hasSearchEnabled,
    });
    const searchInputWrapperClassName = createCssClassNames({
        'ibexa-instant-filter__input-wrapper': true,
        'ibexa-instant-filter__input-wrapper--hidden': !hasSearchEnabled,
    });
    const renderLanguages = () => {
        return languageCodes.map((languageCode) => {
            const languageNodeClassName = createCssClassNames({
                'ibexa-instant-filter__item': true,
                'ibexa-instant-filter__item--active': activeLanguage === languageCode,
            });

            return (
                <div key={languageCode} className={languageNodeClassName} onClick={() => setActiveLanguage(languageCode)}>
                    {adminUiConfig.languages.mappings[languageCode].name}
                </div>
            );
        });
    };

    return (
        <div className={className}>
            <div className="ibexa-extra-actions__header">
                <h2 className="ibexa-extra-actions__header-content">{editTranslationLabel}</h2>
            </div>
            <div className="ibexa-extra-actions__content">
                <div className="ibexa-instant-filter" ref={_refInstantFilter}>
                    <div className={searchInputWrapperClassName}>
                        <input
                            type="text"
                            className="ibexa-instant-filter__input ibexa-input ibexa-input--text form-control"
                            placeholder={Translator.trans(
                                /*@Desc("Search...")*/ 'instant.filter.languages.placeholder',
                                {},
                                'ibexa_sub_items',
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
