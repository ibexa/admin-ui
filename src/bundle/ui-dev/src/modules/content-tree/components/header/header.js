import React from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import Icon from '../../../common/icon/icon';
import { Button, ButtonType } from '@ids-components/components/Button';
import PopupActions from '../popup-actions/popup.actions';
import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

const Header = ({ isCollapsed, toggleCollapseTree, actions, popupRef }) => {
    const Translator = getTranslator();
    const headerTitle = Translator.trans(/* @Desc("Content tree") */ 'content_tree.header', {}, 'ibexa_content_tree');
    const renderCollapseButton = () => {
        const iconName = isCollapsed ? 'caret-next' : 'caret-back';
        const caretIconClass = createCssClassNames({
            'ibexa-icon--tiny': isCollapsed,
            'ibexa-icon--small-medium': !isCollapsed,
        });

        return (
            <Button
                type={ButtonType.Tertiary}
                onClick={toggleCollapseTree}
                className="c-header__toggle-btn"
            >
                {isCollapsed && <Icon name="content-tree" extraClasses="ibexa-icon--small-medium" />}
                <Icon name={iconName} extraClasses={caretIconClass} />
            </Button>
        );
    };

    if (isCollapsed) {
        return renderCollapseButton();
    }

    return (
        <div className="c-header">
            {renderCollapseButton()}
            <div className="c-header__name">
                <Icon name="content-tree" extraClasses="ibexa-icon--small-medium" />
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
