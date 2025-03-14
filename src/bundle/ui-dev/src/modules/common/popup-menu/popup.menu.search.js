import React from 'react';
import PropTypes from 'prop-types';

import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import Icon from '@ibexa-admin-ui/src/bundle/ui-dev/src/modules/common/icon/icon';

const MIN_SEARCH_ITEMS_DEFAULT = 5;

const PopupMenuSearch = ({ numberOfItems, filterText, setFilterText }) => {
    const Translator = getTranslator();
    const searchPlaceholder = Translator.trans(/*@Desc("Search...")*/ 'ibexa_popup_menu.search.placeholder', {}, 'ibexa_popup_menu');
    const updateFilterValue = (event) => setFilterText(event.target.value);
    const resetInputValue = () => setFilterText('');

    if (numberOfItems < MIN_SEARCH_ITEMS_DEFAULT) {
        return null;
    }

    return (
        <div className="c-popup-menu__search">
            <div className="ibexa-input-text-wrapper">
                <div className="ibexa-input-text-wrapper__input-wrapper">
                    <input
                        type="text"
                        placeholder={searchPlaceholder}
                        className="c-popup-menu__search-input ibexa-input ibexa-input--small ibexa-input--text form-control"
                        onChange={updateFilterValue}
                        value={filterText}
                    />
                    <div className="ibexa-input-text-wrapper__actions">
                        <button
                            type="button"
                            className="btn ibexa-input-text-wrapper__action-btn ibexa-input-text-wrapper__action-btn--clear"
                            tabIndex="-1"
                            onClick={resetInputValue}
                        >
                            <Icon name="discard" extraClasses="ibexa-icon--tiny-small" />
                        </button>
                        <button
                            type="button"
                            className="btn ibexa-input-text-wrapper__action-btn ibexa-input-text-wrapper__action-btn--search"
                            tabIndex="-1"
                        >
                            <Icon name="search" extraClasses="ibexa-icon--small" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
};

PopupMenuSearch.propTypes = {
    numberOfItems: PropTypes.number.isRequired,
    setFilterText: PropTypes.func.isRequired,
    filterText: PropTypes.string,
};

PopupMenuSearch.defaultProps = {
    filterText: '',
};

export default PopupMenuSearch;
