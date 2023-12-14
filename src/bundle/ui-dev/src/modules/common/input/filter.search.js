import React from 'react';
import PropTypes from 'prop-types';
import { createCssClassNames } from '../../common/helpers/css.class.names';
import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

const Search = ({ onChange, placeholder, extraClasses, value }) => {
    const Translator = getTranslator();
    const inputPlaceholder =
        placeholder ?? Translator.trans(/*@Desc("Search...")*/ 'search.placeholder', {}, 'ibexa_universal_discovery_widget');
    const searchClassName = createCssClassNames({
        'form-control': true,
        'ibexa-input': true,
        'ibexa-input--text': true,
        [extraClasses]: true,
    });

    return <input type="text" name="filter" placeholder={inputPlaceholder} value={value} onChange={onChange} className={searchClassName} />;
};

Search.propTypes = {
    placeholder: PropTypes.string,
    onChange: PropTypes.func.isRequired,
    extraClasses: PropTypes.string,
    value: PropTypes.string.isRequired,
};

Search.defaultProps = {
    placeholder: '',
    extraClasses: '',
};

export default Search;
