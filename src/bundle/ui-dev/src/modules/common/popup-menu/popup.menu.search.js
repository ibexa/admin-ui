import React from 'react';
import PropTypes from 'prop-types';
import { InputTextInput, InputTextInputSize } from '@ids-components/components/InputText';

import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

const MIN_SEARCH_ITEMS_DEFAULT = 5;

const PopupMenuSearch = ({ numberOfItems, filterText = '', setFilterText }) => {
    const Translator = getTranslator();
    const searchPlaceholder = Translator.trans(/* @Desc("Search...") */ 'ibexa_popup_menu.search.placeholder', {}, 'ibexa_popup_menu');
    const updateFilterValue = (event) => setFilterText(event.target.value);
    const resetInputValue = () => setFilterText('');

    if (numberOfItems < MIN_SEARCH_ITEMS_DEFAULT) {
        return null;
    }

    return (
        <div className="c-popup-menu__search">
            <InputTextInput
                className="c-popup-menu__search-input-wrapper"
                extraAria={{
                    className: 'ids-input ids-input--text ids-input--small c-popup-menu__search-input',
                }}
                hasSearchAction={true}
                name="popup-menu-search"
                onChange={(value) => updateFilterValue({ target: { value } })}
                placeholder={searchPlaceholder}
                processActions={(actions) =>
                    actions.map((action) => ({
                        ...action,
                        component:
                            action.id === 'clear'
                                ? React.cloneElement(action.component, { onClick: resetInputValue })
                                : action.component,
                    }))
                }
                searchButtonType="button"
                size={InputTextInputSize.Small}
                value={filterText}
            />
        </div>
    );
};

PopupMenuSearch.propTypes = {
    numberOfItems: PropTypes.number.isRequired,
    setFilterText: PropTypes.func.isRequired,
    filterText: PropTypes.string,
};

export default PopupMenuSearch;
