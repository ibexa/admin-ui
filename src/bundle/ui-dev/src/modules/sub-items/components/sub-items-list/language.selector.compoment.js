import React, { useEffect, useState } from 'react';
import PropTypes from 'prop-types';

import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { Button, ButtonType } from '@ids-components/components/Button';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import InstantFilter from '../sub-items-list/instant.filter.component';

const MIN_ITEMS_WITH_SEARCH = 10;

const LanguageSelector = ({ isOpen = false, label = '', languageItems = [], handleItemChange = () => {}, close = () => {} }) => {
    const Translator = getTranslator();
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
    const resetLanguageSelector = () => {
        setActiveLanguage('');
    };
    const handleDiscardClick = (event) => {
        closeSelector(event);
    };

    useEffect(() => {
        document.body.addEventListener('ibexa:edit-content-reset-language-selector', resetLanguageSelector, false);

        return () => {
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
                    handleItemChange={(value) => {
                        handleItemChange(value);
                        setActiveLanguage(value);
                    }}
                    isSearchEnabled={isSearchEnabled}
                />
            </div>
            <div className="ibexa-extra-actions__confirm-wrapper">
                <Button
                    type={ButtonType.Primary}
                    className="ibexa-extra-actions__confirm-btn"
                    onClick={dispatchSubmitFormEvent}
                    disabled={!activeLanguage}
                >
                    {Translator.trans(/*@Desc("Edit")*/ 'edit.languages.edit', {}, 'ibexa_sub_items')}
                </Button>
                <Button type={ButtonType.Secondary} className="ids-btn--close" onClick={handleDiscardClick}>
                    {Translator.trans(/*@Desc("Discard")*/ 'edit.languages.discard', {}, 'ibexa_sub_items')}
                </Button>
            </div>
        </div>
    );
};

LanguageSelector.propTypes = {
    isOpen: PropTypes.bool,
    label: PropTypes.string,
    languageItems: PropTypes.array,
    handleItemChange: PropTypes.func,
    close: PropTypes.func,
};

export default LanguageSelector;
