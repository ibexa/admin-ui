import React, { useState, useMemo } from 'react';
import PropTypes from 'prop-types';

import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { createCssClassNames } from '@ibexa-admin-ui-modules/common/helpers/css.class.names';

const InstantFilter = ({ items = [], handleItemChange = () => {}, isSearchEnabled = true, activeLanguage = '' }) => {
    const Translator = getTranslator();
    const [filterQuery, setFilterQuery] = useState('');
    const searchInputWrapperClassName = createCssClassNames({
        'ibexa-instant-filter__input-wrapper': true,
        'ibexa-instant-filter__input-wrapper--hidden': !isSearchEnabled,
    });
    const filteredItems = useMemo(() => {
        if (!filterQuery) {
            return items;
        }

        const filterQueryLowerCase = filterQuery.toLowerCase();

        return items.filter((item) => {
            const itemLabelLowerCase = item.label.toLowerCase();

            return itemLabelLowerCase.includes(filterQueryLowerCase);
        });
    }, [items, filterQuery]);

    return (
        <div className="ibexa-instant-filter">
            <div className={searchInputWrapperClassName}>
                <input
                    type="text"
                    className="ibexa-instant-filter__input ibexa-input ibexa-input--text form-control"
                    placeholder={Translator.trans(/*@Desc("Search...")*/ 'instant.filter.placeholder', {}, 'ibexa_sub_items')}
                    value={filterQuery}
                    onChange={(event) => setFilterQuery(event.target.value)}
                />
            </div>
            <div className="ibexa-instant-filter__desc">
                {Translator.trans(/*@Desc("Languages")*/ 'instant.filter.languages.select_language.desc', {}, 'ibexa_sub_items')}
            </div>
            <div className="ibexa-instant-filter__items">
                {filteredItems.map((item) => {
                    const radioId = `item_${item.value}`;
                    const labelClassName = createCssClassNames({
                        'form-check-label': true,
                        'ibexa-label': true,
                        'ibexa-label--active': activeLanguage === item.value,
                    });

                    return (
                        <div key={radioId} className="ibexa-instant-filter__item">
                            <div className="form-check">
                                <input
                                    type="radio"
                                    id={radioId}
                                    name="items"
                                    className="form-check-input ibexa-input"
                                    value={item.value}
                                    checked={activeLanguage === item.value}
                                    onChange={() => handleItemChange(item.value)}
                                />
                                <label className={labelClassName} htmlFor={radioId}>
                                    {item.label}
                                </label>
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
};

InstantFilter.propTypes = {
    isSearchEnabled: PropTypes.bool,
    activeLanguage: PropTypes.string,
    items: PropTypes.array,
    handleItemChange: PropTypes.func,
};

export default InstantFilter;
