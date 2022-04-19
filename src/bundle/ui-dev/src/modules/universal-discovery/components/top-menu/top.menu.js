import React, { useContext, useMemo, useState } from 'react';
import PropTypes from 'prop-types';

import TopMenuSearchInput from './top.menu.search.input';
import Icon from '../../../common/icon/icon';

import { TitleContext, CancelContext } from '../../universal.discovery.module';
import { createCssClassNames } from '../../../common/helpers/css.class.names';

const { Translator, ibexa } = window;

const TopMenu = ({ actionsDisabledMap }) => {
    const title = useContext(TitleContext);
    const cancelUDW = useContext(CancelContext);
    const [isSearchOpened, setIsSearchOpened] = useState(false);
    const sortedActions = useMemo(() => {
        const actions = [...ibexa.adminUiConfig.universalDiscoveryWidget.topMenuActions];

        return actions.sort((actionA, actionB) => {
            return actionB.priority - actionA.priority;
        });
    }, []);
    const backTitle = Translator.trans(/*@Desc("Close")*/ 'close.label', {}, 'universal_discovery_widget');
    const className = createCssClassNames({
        'c-top-menu': true,
        'c-top-menu--search-opened': isSearchOpened,
    });

    return (
        <div className={className}>
            <h2 className="c-top-menu__title-wrapper" data-tooltip-container-selector=".c-udw-tab" title={title}>
                {title}
            </h2>
            <div className="c-top-menu__actions-wrapper">
                {sortedActions.map((action) => {
                    const Component = action.component;

                    return <Component key={action.id} isDisabled={actionsDisabledMap[action.id]} />;
                })}
            </div>
            <TopMenuSearchInput isSearchOpened={isSearchOpened} setIsSearchOpened={setIsSearchOpened} />
            <span className="c-top-menu__cancel-btn-wrapper">
                <button
                    className="c-top-menu__cancel-btn btn ibexa-btn ibexa-btn--ghost ibexa-btn--no-text"
                    type="button"
                    onClick={cancelUDW}
                    title={backTitle}
                    data-tooltip-container-selector=".c-top-menu__cancel-btn-wrapper"
                >
                    <Icon name="discard" extraClasses="ibexa-icon--medium" />
                </button>
            </span>
        </div>
    );
};

TopMenu.propTypes = {
    actionsDisabledMap: PropTypes.object,
};

TopMenu.defaultProps = {
    actionsDisabledMap: {
        'content-create-button': false,
        'sort-switcher': false,
        'view-switcher': false,
    },
};

export default TopMenu;
