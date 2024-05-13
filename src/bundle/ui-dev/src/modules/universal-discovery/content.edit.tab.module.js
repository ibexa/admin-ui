import React, { useRef, useContext } from 'react';

import {
    ActiveTabContext,
    RestInfoContext,
    SelectedLocationsContext,
    LoadedLocationsMapContext,
    EditOnTheFlyDataContext,
} from './universal.discovery.module';
import { getTranslator, getRouting } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { findLocationsByParentLocationId } from './services/universal.discovery.service';
import deepClone from '../common/helpers/deep.clone.helper';

const ContentEditTabModule = () => {
    const Routing = getRouting();
    const restInfo = useContext(RestInfoContext);
    const [, setActiveTab, previousActiveTab] = useContext(ActiveTabContext);
    const [loadedLocationsMap, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);
    const [selectedLocations, dispatchSelectedLocationsAction] = useContext(SelectedLocationsContext);
    const [editOnTheFlyData, setEditOnTheFlyData] = useContext(EditOnTheFlyDataContext);
    const iframeRef = useRef();
    const publishContent = () => {
        const submitButton = iframeRef.current.contentWindow.document.body.querySelector('[data-action="publish"]');

        if (submitButton) {
            submitButton.click();
        }
    };
    const cancelContentEdit = () => {
        setActiveTab(previousActiveTab);
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
            const subitem = clonedLocation.subitems.find(({ location }) => {
                return location.id === locationId;
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
        const iframeCloseBtn = iframeBody.querySelector('.ibexa-anchor-navigation-menu__close');

        if (locationId) {
            handleContentPublished(parseInt(locationId.content, 10));
        }

        iframeConfirmBtn?.addEventListener('click', publishContent, false);
        iframeCancelBtn?.addEventListener('click', cancelContentEdit, false);
        iframeCloseBtn?.addEventListener('click', cancelContentEdit, false);
    };
    const iframeUrl = Routing.generate(
        'ibexa.content.on_the_fly.edit',
        {
            contentId: editOnTheFlyData.contentId,
            versionNo: editOnTheFlyData.versionNo,
            languageCode: editOnTheFlyData.languageCode,
            locationId: editOnTheFlyData.locationId,
        },
        true,
    );

    return (
        <div className="c-content-edit">
            <iframe src={iframeUrl} className="c-content-edit__iframe" ref={iframeRef} onLoad={handleIframeLoad} />
        </div>
    );
};

const ContentEditTab = {
    id: 'content-edit',
    component: ContentEditTabModule,
    getLabel: () => {
        const Translator = getTranslator();

        return Translator.trans(/*@Desc("Content edit")*/ 'content_edit.label', {}, 'ibexa_universal_discovery_widget');
    },
    isHiddenOnList: true,
};

export { ContentEditTabModule as default, ContentEditTab };
