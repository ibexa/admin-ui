import React, { useContext } from 'react';
import PropTypes from 'prop-types';

import SimpleDropdown from '../../../common/simple-dropdown/simple.dropdown';
import { getTranslator } from '../../../../../../Resources/public/js/scripts/helpers/context.helper';
import {
    ITEMS_VIEW_TYPE_STORAGE_KEY_PREFIX,
    CurrentViewContext,
    MarkedLocationIdContext,
    ViewContext,
    RootLocationIdContext,
} from '../../universal.discovery.module';

const ViewSwitcher = ({ isDisabled }) => {
    const Translator = getTranslator();
    const viewLabel = Translator.trans(/*@Desc("View")*/ 'view_switcher.view', {}, 'ibexa_universal_discovery_widget');
    const [currentView, setCurrentView] = useContext(CurrentViewContext);
    const { views } = useContext(ViewContext);
    const rootLocationId = useContext(RootLocationIdContext);
    const [markedLocationId] = useContext(MarkedLocationIdContext);
    const selectedOption = views.find((option) => option.value === currentView);
    const onOptionClick = ({ value }) => {
        const itemsViewTypeStorageKey = `${ITEMS_VIEW_TYPE_STORAGE_KEY_PREFIX}-${markedLocationId ?? rootLocationId}`;

        window.localStorage.setItem(itemsViewTypeStorageKey, value);
        setCurrentView(value);
    };

    return (
        <div className="c-udw-view-switcher">
            <SimpleDropdown
                options={views}
                selectedOption={selectedOption}
                onOptionClick={onOptionClick}
                isDisabled={isDisabled}
                selectedItemLabel={viewLabel}
                isSwitcher={true}
            />
        </div>
    );
};

ViewSwitcher.propTypes = {
    isDisabled: PropTypes.bool,
};

ViewSwitcher.defaultProps = {
    isDisabled: false,
};

export const ViewSwitcherButton = {
    id: 'view-switcher',
    priority: 10,
    component: ViewSwitcher,
};

export default ViewSwitcher;
