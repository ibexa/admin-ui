import React, { useRef, useContext } from 'react';

import {
    TabsContext,
    ActiveTabContext,
    RestInfoContext,
    SelectedLocationsContext,
    LoadedLocationsMapContext,
    EditOnTheFlyDataContext,
} from './universal.discovery.module';
import { findLocationsByParentLocationId } from './services/universal.discovery.service';
import deepClone from '../common/helpers/deep.clone.helper';
import { createCssClassNames } from '../common/helpers/css.class.names';

const ContentEditTabModule = () => {
    const restInfo = useContext(RestInfoContext);
    const tabs = useContext(TabsContext);
    const [activeTab, setActiveTab] = useContext(ActiveTabContext);
    const [loadedLocationsMap, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);
    const [selectedLocations, dispatchSelectedLocationsAction] = useContext(SelectedLocationsContext);
    const [editOnTheFlyData, setEditOnTheFlyData] = useContext(EditOnTheFlyDataContext);
    const cancelLabel = Translator.trans(/*@Desc("Cancel")*/ 'content_edit.cancel.label', {}, 'universal_discovery_widget');
    const confirmLabel = Translator.trans(/*@Desc("Confirm")*/ 'content_edit.confirm.label', {}, 'universal_discovery_widget');
    const iframeRef = useRef();
    const publishContent = () => {
        const submitButton = iframeRef.current.contentWindow.document.body.querySelector('[data-action="publish"]');

        if (submitButton) {
            submitButton.click();
        }
    };
    const cancelContentEdit = () => {
        setActiveTab(tabs[0].id);
        setEditOnTheFlyData({});
    };
    const handleContentPublished = (locationId) => {
        const clonedLocationsMap = deepClone(loadedLocationsMap);
        let isInSubitems = false;

        findLocationsByParentLocationId({ ...restInfo, parentLocationId: locationId }, (response) => {
            const clonedSelectedLocation = deepClone(selectedLocations);
            const index = clonedSelectedLocation.findIndex((clonedLocation) => clonedLocation.location.id === locationId);

            if (index !== -1) {
                clonedSelectedLocation[index].location = response.location;

                dispatchSelectedLocationsAction({ type: 'REPLACE_SELECTED_LOCATIONS', locations: clonedSelectedLocation });
            }

            dispatchLoadedLocationsAction({ type: 'UPDATE_LOCATIONS', data: response });
        });

        clonedLocationsMap.forEach((clonedLocation) => {
            const subitem = clonedLocation.subitems.find((subitem) => {
                return subitem.location.id === locationId;
            });

            if (subitem) {
                clonedLocation.subitems = [];
                isInSubitems = true;
            }
        });

        if (isInSubitems) {
            dispatchLoadedLocationsAction({ type: 'SET_LOCATIONS', data: clonedLocationsMap });
        }

        cancelContentEdit();
    };
    const handleIframeLoad = () => {
        const locationId = iframeRef.current.contentWindow.document.querySelector('meta[name="LocationID"]');
        const iframeBody = iframeRef.current.contentWindow.document.body;
        const iframeConfirmBtn = iframeBody.querySelector('.ibexa-context-menu .ibexa-btn--confirm');
        const iframeCancelBtn = iframeBody.querySelector('.ibexa-context-menu .ibexa-btn--cancel');
        const iframeBackBtn = iframeBody.querySelector('.ibexa-anchor-navigation-menu__back');

        if (locationId) {
            handleContentPublished(parseInt(locationId.content, 10));
        }

        iframeConfirmBtn?.addEventListener('click', publishContent, false);
        iframeCancelBtn?.addEventListener('click', cancelContentEdit, false);
        iframeBackBtn?.addEventListener('click', cancelContentEdit, false);
    };
    const iframeUrl = window.Routing.generate(
        'ibexa.content.on_the_fly.edit',
        {
            contentId: editOnTheFlyData.contentId,
            versionNo: editOnTheFlyData.versionNo,
            languageCode: editOnTheFlyData.languageCode,
            locationId: editOnTheFlyData.locationId,
        },
        true
    );

    return (
        <div className="c-content-edit">
            <iframe src={iframeUrl} className="c-content-edit__iframe" ref={iframeRef} onLoad={handleIframeLoad} />
        </div>
    );
};

ibexa.addConfig(
    'adminUiConfig.universalDiscoveryWidget.tabs',
    [
        {
            id: 'content-edit',
            component: ContentEditTabModule,
            label: Translator.trans(/*@Desc("Content edit")*/ 'content_edit.label', {}, 'universal_discovery_widget'),
            isHiddenOnList: true,
        },
    ],
    true
);

export default ContentEditTabModule;
