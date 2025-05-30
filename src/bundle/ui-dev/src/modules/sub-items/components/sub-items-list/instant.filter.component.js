import React, { useEffect, useState, useRef } from 'react';
import PropTypes from 'prop-types';

const { Translator } = window;

const FILTER_TIMEOUT = 200;

const InstantFilter = ({ items = [], handleItemChange = () => {} }) => {
    const _refInstantFilter = useRef(null);
    const [filterQuery, setFilterQuery] = useState('');
    const [itemsMap, setItemsMap] = useState([]);
    let filterTimeout = null;

    useEffect(() => {
        const currentItems = [..._refInstantFilter.current.querySelectorAll('.ibexa-instant-filter__item')];
        const itemsMapNext = currentItems.map((item) => ({
            label: item.textContent.toLowerCase(),
            element: item,
        }));

        setItemsMap(itemsMapNext);
    }, [items]);

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
            <div className="ibexa-instant-filter__input-wrapper">
                <input
                    type="text"
                    className="ibexa-instant-filter__input form-control"
                    placeholder={Translator.trans(
                        /* @Desc("Search by content type") */ 'instant.filter.placeholder',
                        {},
                        'ibexa_sub_items',
                    )}
                    value={filterQuery}
                    onChange={(event) => setFilterQuery(event.target.value)}
                />
            </div>
            <div className="ibexa-instant-filter__items">
                {items.map((item) => {
                    const radioId = `item_${item.value}`;

                    return (
                        <div key={radioId} className="ibexa-instant-filter__item">
                            <div className="form-check">
                                <input
                                    type="radio"
                                    id={radioId}
                                    name="items"
                                    className="form-check-input"
                                    value={item.value}
                                    onChange={() => handleItemChange(item.value)}
                                />
                                <label className="form-check-label" htmlFor={radioId}>
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
    items: PropTypes.array,
    handleItemChange: PropTypes.func,
};

export default InstantFilter;
