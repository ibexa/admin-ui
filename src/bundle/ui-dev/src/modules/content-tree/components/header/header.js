import React from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import Icon from '../../../common/icon/icon';
import PopupActions from '../popup-actions/popup.actions';

const { Translator } = window;

const Header = ({ isCollapsed, toggleCollapseTree, actions, popupRef }) => {
    const headerTitle = Translator.trans(/*@Desc("Content tree")*/ 'content_tree.header', {}, 'ibexa_content_tree');
    const renderCollapseButton = () => {
        const iconName = isCollapsed ? 'caret-next' : 'caret-back';
        const caretIconClass = createCssClassNames({
            'ibexa-icon--tiny': isCollapsed,
            'ibexa-icon--small': !isCollapsed,
        });

        return (
            <button
                type="button"
                className="ibexa-btn btn ibexa-btn--no-text ibexa-btn--tertiary c-header__toggle-btn"
                onClick={toggleCollapseTree}
            >
                {isCollapsed && <Icon name="content-tree" extraClasses="ibexa-icon--small" />}
                <Icon name={iconName} extraClasses={caretIconClass} />
            </button>
        );
    };

    if (isCollapsed) {
        return renderCollapseButton();
    }

    return (
        <div className="c-header">
            {renderCollapseButton()}
            <div className="c-header__name">
                <Icon name="content-tree" extraClasses="ibexa-icon--small" />
                {headerTitle}
            </div>
            <div className="c-header__options">
                <PopupActions listRef={popupRef} options={actions} />
            </div>
        </div>
    );
};

Header.propTypes = {
    isCollapsed: PropTypes.bool.isRequired,
    toggleCollapseTree: PropTypes.func.isRequired,
    actions: PropTypes.array.isRequired,
    popupRef: PropTypes.object.isRequired,
};

export default Header;
