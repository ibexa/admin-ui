import React, { useContext, useMemo, useState } from 'react';
import PropTypes from 'prop-types';

import TopMenuSearchInput from './top.menu.search.input';
import Icon from '../../../common/icon/icon';
import { Button, ButtonType } from '@ids-components/components/Button';

import { TitleContext, CancelContext } from '../../universal.discovery.module';
import { createCssClassNames } from '../../../common/helpers/css.class.names';
import { getAdminUiConfig, getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

const TopMenu = ({
    actionsDisabledMap = {
        'content-create-button': false,
        'sort-switcher': false,
        'view-switcher': false,
    },
}) => {
    const Translator = getTranslator();
    const adminUiConfig = getAdminUiConfig();
    const { topMenuActions } = adminUiConfig.universalDiscoveryWidget;
    const title = useContext(TitleContext);
    const cancelUDW = useContext(CancelContext);
    const [isSearchOpened, setIsSearchOpened] = useState(false);
    const sortedActions = useMemo(() => {
        const actions = topMenuActions;

        return actions.sort((actionA, actionB) => {
            return actionB.priority - actionA.priority;
        });
    }, []);
    const backTitle = Translator.trans(/* @Desc("Close") */ 'close.label', {}, 'ibexa_universal_discovery_widget');
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
                    const disabledData = actionsDisabledMap[action.id];
                    const hasDisabledConfig = disabledData instanceof Object;

                    return (
                        <Component key={action.id} isDisabled={!!disabledData} disabledConfig={hasDisabledConfig ? disabledData : null} />
                    );
                })}
            </div>
            <TopMenuSearchInput isSearchOpened={isSearchOpened} setIsSearchOpened={setIsSearchOpened} />
            <span className="c-top-menu__cancel-btn-wrapper">
                <Button
                    type={ButtonType.TertiaryAlt}
                    icon="discard"
                    onClick={cancelUDW}
                    title={backTitle}
                    className="c-top-menu__cancel-btn"
                    extraAria={{ 'data-tooltip-container-selector': '.c-top-menu__cancel-btn-wrapper' }}
                />
            </span>
        </div>
    );
};

TopMenu.propTypes = {
    actionsDisabledMap: PropTypes.object,
};

export default TopMenu;
