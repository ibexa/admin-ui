import React from 'react';
import PropTypes from 'prop-types';
import { createCssClassNames } from '../../common/helpers/css.class.names';

const { Translator } = window;
const defaultPlaceholder = Translator.trans(/*@Desc("Search...")*/ 'search.placeholder', {}, 'universal_discovery_widget');

const Search = ({ onChange, placeholder, extraClasses, value }) => {
    const searchClassName = createCssClassNames({
        'form-control': true,
        'ibexa-input': true,
        'ibexa-input--text': true,
        [extraClasses]: true,
    });

    return <input type="text" name="filter" placeholder={placeholder} value={value} onChange={onChange} className={searchClassName} />;
};

Search.propTypes = {
    placeholder: PropTypes.string,
    onChange: PropTypes.func.isRequired,
    extraClasses: PropTypes.string,
    value: PropTypes.string.isRequired,
};

Search.defaultProps = {
    placeholder: defaultPlaceholder,
    extraClasses: '',
};

export default Search;
