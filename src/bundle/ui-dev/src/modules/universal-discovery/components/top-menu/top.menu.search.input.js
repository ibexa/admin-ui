import React, { useState, useContext, useEffect, useRef } from 'react';
import PropTypes from 'prop-types';
import { InputTextInput } from '@ids-components/components/InputText';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import { Button, ButtonType } from '@ids-components/components/Button';

import { SearchTextContext } from '../../universal.discovery.module';

const ENTER_CHAR_CODE = 13;

const TopMenuSearchInput = ({ isSearchOpened, setIsSearchOpened }) => {
    const [searchText, , makeSearch] = useContext(SearchTextContext);
    const [inputValue, setInputValue] = useState(searchText);
    const inputRef = useRef();
    const className = createCssClassNames({
        'c-top-menu-search-input': true,
        'c-top-menu-search-input--search-opened': isSearchOpened,
    });
    const updateInputValue = (value) => setInputValue(value);
    const handleSearchBtnClick = () => {
        if (isSearchOpened) {
            makeSearch(inputValue);
            setIsSearchOpened(false);
        } else {
            setIsSearchOpened(true);
        }
    };
    const handleKeyPressed = ({ charCode }) => {
        if (charCode === ENTER_CHAR_CODE) {
            makeSearch(inputValue);
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
            <InputTextInput
                extraAria={{
                    className: 'c-top-menu-search-input__search-input',
                    onKeyPress: handleKeyPressed,
                }}
                name="udw-top-menu-search"
                onChange={updateInputValue}
                processActions={() => []}
                ref={inputRef}
                value={inputValue}
            />
            <Button
                type={isSearchOpened ? ButtonType.Primary : ButtonType.Tertiary}
                icon="search"
                onClick={handleSearchBtnClick}
                className="c-top-menu-search-input__search-btn"
            />
        </div>
    );
};

TopMenuSearchInput.propTypes = {
    setIsSearchOpened: PropTypes.func.isRequired,
    isSearchOpened: PropTypes.bool.isRequired,
};

export default TopMenuSearchInput;
