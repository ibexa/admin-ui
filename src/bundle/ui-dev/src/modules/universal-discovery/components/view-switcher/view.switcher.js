import React, { useContext } from 'react';
import PropTypes from 'prop-types';

import SimpleDropdown from '../../../common/simple-dropdown/simple.dropdown';
import { getTranslator } from '../../../../../../Resources/public/js/scripts/helpers/context.helper';
import { CurrentViewContext, ViewContext } from '../../universal.discovery.module';

const ViewSwitcher = ({ isDisabled = false }) => {
    const Translator = getTranslator();
    const viewLabel = Translator.trans(/*@Desc("View")*/ 'view_switcher.view', {}, 'ibexa_universal_discovery_widget');
    const [currentView, setCurrentView] = useContext(CurrentViewContext);
    const { views } = useContext(ViewContext);
    const selectedOption = views.find((option) => option.value === currentView);
    const onOptionClick = ({ value }) => {
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

export const ViewSwitcherButton = {
    id: 'view-switcher',
    priority: 10,
    component: ViewSwitcher,
};

export default ViewSwitcher;
