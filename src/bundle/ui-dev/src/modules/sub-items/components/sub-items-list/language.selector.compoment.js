import React, { useEffect } from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import InstantFilter from '../sub-items-list/instant.filter.component';

const LanguageSelector = ({ isOpen = false, label = '', languageItems = [], handleItemChange = () => {}, close = () => {} }) => {
    const className = createCssClassNames({
        'ibexa-extra-actions': true,
        'c-language-selector': true,
        'ibexa-extra-actions--edit': true,
        'ibexa-extra-actions--hidden': !isOpen,
    });
    const closeSelector = (event) => {
        if (!event.target.closest('.c-table-view-item__btn') && !event.target.classList.contains('ibexa-instant-filter__input')) {
            close();
        }
    };

    useEffect(() => {
        window.document.addEventListener('click', closeSelector, false);

        return () => {
            window.document.removeEventListener('click', closeSelector);
        };
    }, []);

    return (
        <div className={className}>
            <div className="ibexa-extra-actions__header">{label}</div>
            <div className="ibexa-extra-actions__content">
                <InstantFilter items={languageItems} handleItemChange={handleItemChange} />
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
