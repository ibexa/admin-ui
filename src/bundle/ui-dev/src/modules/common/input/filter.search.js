import React, { useState, useEffect, useRef } from 'react';
import ReactDOM from 'react-dom';
import PropTypes from 'prop-types';
import Icon from '../../common/icon/icon';
import { createCssClassNames } from '../../common/helpers/css.class.names';

const { Translator } = window;
const defaultPlaceholder = Translator.trans(/*@Desc("Search...")*/ 'dropdown.placeholder', {}, 'universal_discovery_widget');

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
    placeholder: PropTypes.string.isRequired,
    onChange: PropTypes.func.isRequired,
    extraClasses: PropTypes.string.isRequired,
    value: PropTypes.string.isRequired,
};

Search.defaultProps = {
    placeholder: defaultPlaceholder,
    extraClasses: '',
};

export default Search;
