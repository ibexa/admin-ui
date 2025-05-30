import React, { useContext } from 'react';
import PropTypes from 'prop-types';

import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { hideAll as hideAllTooltips } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/tooltips.helper';
import Icon from '../../../common/icon/icon';

import {
    CreateContentWidgetContext,
    MarkedLocationIdContext,
    LoadedLocationsMapContext,
    ContentOnTheFlyConfigContext,
    SelectedLocationsContext,
    MultipleConfigContext,
    ContentTypesMapContext,
} from '../../universal.discovery.module';

const ContentCreateButton = ({ isDisabled = false }) => {
    const Translator = getTranslator();
    const [markedLocationId] = useContext(MarkedLocationIdContext);
    const [loadedLocationsMap] = useContext(LoadedLocationsMapContext);
    const [, setCreateContentVisible] = useContext(CreateContentWidgetContext);
    const [selectedLocations] = useContext(SelectedLocationsContext);
    const [multiple, multipleItemsLimit] = useContext(MultipleConfigContext);
    const { hidden, allowedLocations } = useContext(ContentOnTheFlyConfigContext);
    const contentTypesMap = useContext(ContentTypesMapContext);
    const createLabel = Translator.trans(/* @Desc("Create") */ 'create_content.create', {}, 'ibexa_universal_discovery_widget');
    const toggleContentCreateVisibility = () => {
        hideAllTooltips();
        setCreateContentVisible((prevState) => !prevState);
    };
    let selectedLocation = loadedLocationsMap.find((loadedLocation) => loadedLocation.parentLocationId === markedLocationId);

    if (!selectedLocation && loadedLocationsMap.length) {
        selectedLocation = loadedLocationsMap[loadedLocationsMap.length - 1].subitems.find(
            (subitem) => subitem.location.id === markedLocationId,
        );
    }

    const contentTypeInfo = contentTypesMap[selectedLocation?.location?.ContentInfo.Content.ContentType._href];
    const isAllowedLocation = selectedLocation && (!allowedLocations || allowedLocations.includes(selectedLocation.parentLocationId));
    const hasAccess =
        !selectedLocation ||
        !selectedLocation.permissions ||
        (selectedLocation.permissions && selectedLocation.permissions.create.hasAccess);
    const isLimitReached = multiple && multipleItemsLimit !== 0 && selectedLocations.length >= multipleItemsLimit;
    const isContainer = contentTypeInfo?.isContainer ?? true;

    if (hidden) {
        return null;
    }

    return (
        <div className="c-content-create-button">
            <button
                className="c-content-create-button__btn btn ibexa-btn ibexa-btn--dark"
                disabled={isDisabled || !hasAccess || !isAllowedLocation || isLimitReached || !isContainer}
                onClick={toggleContentCreateVisibility}
                type="button"
            >
                <Icon name="create" extraClasses="ibexa-icon--small" />
                <span className="ibexa-btn__label">{createLabel}</span>
            </button>
        </div>
    );
};

ContentCreateButton.propTypes = {
    isDisabled: PropTypes.bool,
};

export const ContentCreateButtonMenuItem = {
    id: 'content-create-button',
    priority: 30,
    component: ContentCreateButton,
};

export default ContentCreateButton;
