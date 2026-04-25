import React from 'react';
import PropTypes from 'prop-types';
import { InputTextInput } from '@ids-components/components/InputText';
import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

const Search = ({ onChange, placeholder = '', extraClasses = '', value }) => {
    const Translator = getTranslator();
    const inputPlaceholder =
        placeholder || Translator.trans(/* @Desc("Search...") */ 'search.placeholder', {}, 'ibexa_universal_discovery_widget');

    return (
        <InputTextInput
            extraAria={{
                className: `ids-input ids-input--text ${extraClasses}`.trim(),
            }}
            hasSearchAction={true}
            name="filter"
            onChange={(nextValue, event) => onChange(event ?? { target: { value: nextValue } })}
            placeholder={inputPlaceholder}
            searchButtonType="button"
            value={value}
        />
    );
};

Search.propTypes = {
    placeholder: PropTypes.string,
    onChange: PropTypes.func.isRequired,
    extraClasses: PropTypes.string,
    value: PropTypes.string.isRequired,
};

export default Search;
