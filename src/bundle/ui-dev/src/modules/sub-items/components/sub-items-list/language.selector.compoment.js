import React, { useEffect, useRef, useState } from 'react';
import PropTypes from 'prop-types';

import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import InstantFilter from '../sub-items-list/instant.filter.component';

const MIN_ITEMS_WITH_SEARCH = 10;

const LanguageSelector = ({ isOpen = false, label = '', languageItems = [], handleItemChange = () => {}, close = () => {} }) => {
    const Translator = getTranslator();
    const discardBtnRef = useRef(null);
    const submitBtnRef = useRef(null);
    const [activeLanguage, setActiveLanguage] = useState('');
    const isSearchEnabled = languageItems.length >= MIN_ITEMS_WITH_SEARCH;
    const className = createCssClassNames({
        'c-language-selector': true,
        'ibexa-extra-actions': true,
        'ibexa-extra-actions--edit': true,
        'ibexa-extra-actions--hidden': !isOpen,
        'ibexa-extra-actions--has-search': isSearchEnabled,
    });
    const closeSelector = (event) => {
        if (!event.target.closest('.c-table-view-item__btn') && !event.target.classList.contains('ibexa-instant-filter__input')) {
            close();
            resetLanguageSelector();
        }
    };
    const dispatchSubmitFormEvent = () => {
        document.body.dispatchEvent(new CustomEvent('ibexa-sub-items:submit-version-edit-form'));
    };
    const handleItemChange = (value) => {
        handleItemChange(value);
        setActiveLanguage(value);
    };
    const resetLanguageSelector = () => {
        setActiveLanguage('');
    };

    useEffect(() => {
        discardBtnRef.current?.addEventListener('click', closeLanguageSelector, false);
        submitBtnRef.current?.addEventListener('click', dispatchSubmitFormEvent, false);
        document.body.addEventListener('ibexa:edit-content-reset-language-selector', resetLanguageSelector, false);

        return () => {
            discardBtnRef.current?.removeEventListener('click', closeLanguageSelector);
            submitBtnRef.current?.removeEventListener('click', dispatchSubmitFormEvent);
            document.body.removeEventListener('ibexa:edit-content-reset-language-selector', resetLanguageSelector);
        };
    }, []);

    return (
        <div className={className}>
            <div className="ibexa-extra-actions__header">
                <h2 className="ibexa-extra-actions__header-content">{label}</h2>
            </div>
            <div className="ibexa-extra-actions__content">
                <InstantFilter
                    items={languageItems}
                    activeLanguage={activeLanguage}
                    handleItemChange={handleItemChange}
                    isSearchEnabled={isSearchEnabled}
                />
            </div>
            <div className="ibexa-extra-actions__confirm-wrapper">
                <button
                    type="submit"
                    className="btn ibexa-extra-actions__confirm-btn ibexa-btn ibexa-btn--primary"
                    ref={submitBtnRef}
                    disabled={!activeLanguage}
                >
                    {Translator.trans(/*@Desc("Edit")*/ 'edit.languages.edit', {}, 'ibexa_sub_items')}
                </button>
                <button type="button" className="btn ibexa-btn--close ibexa-btn ibexa-btn--secondary" ref={discardBtnRef}>
                    {Translator.trans(/*@Desc("Discard")*/ 'edit.languages.discard', {}, 'ibexa_sub_items')}
                </button>
            </div>
        </div>
    );
};

LanguageSelector.propTypes = {
    isOpen: PropTypes.bool,
    label: PropTypes.string,
    languageItems: PropTypes.array,
    handleItemChange: PropTypes.func,
    closeLanguageSelector: PropTypes.func,
    close: PropTypes.func,
};

export default LanguageSelector;
