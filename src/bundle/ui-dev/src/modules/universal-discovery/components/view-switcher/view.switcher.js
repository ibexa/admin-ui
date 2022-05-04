import React, { useContext } from 'react';
import PropTypes from 'prop-types';

import SimpleDropdown from '../../../common/simple-dropdown/simple.dropdown';
import { CurrentViewContext, VIEWS } from '../../universal.discovery.module';

const { ibexa, Translator } = window;

const ViewSwitcher = ({ isDisabled }) => {
    const viewLabel = Translator.trans(/*@Desc("View")*/ 'view_switcher.view', {}, 'universal_discovery_widget');
    const [currentView, setCurrentView] = useContext(CurrentViewContext);
    const selectedOption = VIEWS.find((option) => option.value === currentView);
    const onOptionClick = ({ value }) => {
        setCurrentView(value);
    };

    return (
        <div className="c-udw-view-switcher">
            <SimpleDropdown
                options={VIEWS}
                selectedOption={selectedOption}
                onOptionClick={onOptionClick}
                isDisabled={isDisabled}
                selectedItemLabel={viewLabel}
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

ibexa.addConfig(
    'adminUiConfig.universalDiscoveryWidget.topMenuActions',
    [
        {
            id: 'view-switcher',
            priority: 10,
            component: ViewSwitcher,
        },
    ],
    true,
);

export default ViewSwitcher;
