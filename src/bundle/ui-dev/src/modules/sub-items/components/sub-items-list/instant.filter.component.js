import React, { useEffect, useState, useRef } from 'react';
import PropTypes from 'prop-types';

import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { createCssClassNames } from '@ibexa-admin-ui-modules/common/helpers/css.class.names';

const FILTER_TIMEOUT = 200;

const InstantFilter = (props) => {
    const Translator = getTranslator();
    const _refInstantFilter = useRef(null);
    const [filterQuery, setFilterQuery] = useState('');
    const [itemsMap, setItemsMap] = useState([]);
    const searchInputWrapperClassName = createCssClassNames({
        'ibexa-instant-filter__input-wrapper': true,
        'ibexa-instant-filter__input-wrapper--hidden': !props.hasSearchEnabled,
    });
    let filterTimeout = null;

    useEffect(() => {
        const items = [..._refInstantFilter.current.querySelectorAll('.ibexa-instant-filter__item')];
        const itemsMapNext = items.map((item) => ({
            label: item.textContent.toLowerCase(),
            element: item,
        }));

        setItemsMap(itemsMapNext);
    }, [props.items]);

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

    return (
        <div className="ibexa-instant-filter" ref={_refInstantFilter}>
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
                {props.items.map((item) => {
                    const radioId = `item_${item.value}`;
                    const labelClassName = createCssClassNames({
                        'form-check-label': true,
                        'ibexa-label': true,
                        'ibexa-label--active': props.activeLanguage === item.value,
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
                                    checked={props.activeLanguage === item.value}
                                    onChange={() => props.handleItemChange(item.value)}
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
    hasSearchEnabled: PropTypes.bool,
    activeLanguage: PropTypes.string,
    items: PropTypes.array,
    handleItemChange: PropTypes.func,
};

InstantFilter.defaultProps = {
    hasSearchEnabled: true,
    activeLanguage: '',
    items: [],
    handleItemChange: () => {},
};

export default InstantFilter;
