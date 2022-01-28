import React, { useState, useContext, useEffect, useRef } from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import Icon from '../../../common/icon/icon';

import { ActiveTabContext, SearchTextContext } from '../../universal.discovery.module';

const ENTER_CHAR_CODE = 13;
const SEARCH_TAB_ID = 'search';

const TopMenuSearchInput = ({ isSearchOpened, setIsSearchOpened }) => {
    const [activeTab, setActiveTab] = useContext(ActiveTabContext);
    const [searchText, setSearchText] = useContext(SearchTextContext);
    const [inputValue, setInputValue] = useState(searchText);
    const inputRef = useRef();
    const className = createCssClassNames({
        'c-top-menu-search-input': true,
        'c-top-menu-search-input--search-opened': isSearchOpened,
    });
    const searchBtnClassName = createCssClassNames({
        'c-top-menu-search-input__search-btn btn ibexa-btn ibexa-btn--no-text': true,
        'ibexa-btn--primary': isSearchOpened,
        'ibexa-btn--tertiary': !isSearchOpened,
    });
    const updateInputValue = ({ target: { value } }) => setInputValue(value);
    const search = (value) => {
        if (activeTab !== SEARCH_TAB_ID) {
            setActiveTab('search');
        }

        setSearchText(value);
    };
    const handleSearchBtnClick = () => {
        if (isSearchOpened) {
            search(inputValue);
            setIsSearchOpened(false);
        } else {
            setIsSearchOpened(true);
        }
    };
    const handleKeyPressed = ({ charCode }) => {
        if (charCode === ENTER_CHAR_CODE) {
            search(inputValue);
        }
    };

    useEffect(() => {
        if (isSearchOpened) {
            inputRef.current?.focus();
        }
    }, [isSearchOpened]);

    useEffect(() => {
        const handleClickOutside = (event) => {
            const isClickOutside = !event.target.closest('.c-top-menu-search-input');

            if (isClickOutside) {
                setIsSearchOpened(false);
            }
        };

        document.addEventListener('click', handleClickOutside, false);

        return () => document.removeEventListener('click', handleClickOutside, false);
    }, []);

    return (
        <div className={className}>
            <input
                ref={inputRef}
                type="text"
                className="c-top-menu-search-input__search-input"
                onChange={updateInputValue}
                onKeyPress={handleKeyPressed}
                value={inputValue}
            />
            <button className={searchBtnClassName} type="button" onClick={handleSearchBtnClick}>
                <Icon name="search" extraClasses="ibexa-icon--small" />
            </button>
        </div>
    );
};

TopMenuSearchInput.propTypes = {
    setIsSearchOpened: PropTypes.func.isRequired,
    isSearchOpened: PropTypes.bool.isRequired,
};

export default TopMenuSearchInput;
