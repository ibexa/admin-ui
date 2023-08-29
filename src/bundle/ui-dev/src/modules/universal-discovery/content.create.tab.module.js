import React, { useContext, createRef } from 'react';

import {
    ContentOnTheFlyDataContext,
    TabsContext,
    ContentOnTheFlyConfigContext,
    ActiveTabContext,
    CreateContentWidgetContext,
    RestInfoContext,
    SelectedLocationsContext,
    ConfirmContext,
    LoadedLocationsMapContext,
    MultipleConfigContext,
} from './universal.discovery.module';
import { findLocationsById } from './services/universal.discovery.service';
import deepClone from '../common/helpers/deep.clone.helper';

const { ibexa, Translator, Routing } = window;

const generateIframeUrl = ({ locationId, languageCode, contentTypeIdentifier }) => {
    return Routing.generate('ibexa.content.on_the_fly.create', {
        locationId,
        languageCode,
        contentTypeIdentifier,
    });
};

const ContentCreateTabModule = () => {
    const [contentOnTheFlyData, setContentOnTheFlyData] = useContext(ContentOnTheFlyDataContext);
    const tabs = useContext(TabsContext);
    const contentOnTheFlyConfig = useContext(ContentOnTheFlyConfigContext);
    const onConfirm = useContext(ConfirmContext);
    const restInfo = useContext(RestInfoContext);
    const [, setActiveTab] = useContext(ActiveTabContext);
    const [, setCreateContentVisible] = useContext(CreateContentWidgetContext);
    const [selectedLocations, dispatchSelectedLocationsAction] = useContext(SelectedLocationsContext);
    const [loadedLocationsMap, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);
    const [multiple] = useContext(MultipleConfigContext);
    const iframeUrl = generateIframeUrl(contentOnTheFlyData);
    const iframeRef = createRef();
    const cancelContentCreate = () => {
        setCreateContentVisible(false);
        setContentOnTheFlyData({});
        setActiveTab(tabs[0].id);
    };
    const publishContent = () => {
        const submitButton = iframeRef.current.contentWindow.document.body.querySelector('[data-action="publish"]');

        if (submitButton) {
            submitButton.click();
        }
    };
    const handleCancelInIframe = (event) => {
        event.preventDefault();
        cancelContentCreate();
    };
    const handleIframeLoad = () => {
        const locationId = iframeRef.current.contentWindow.document.querySelector('meta[name="LocationID"]');
        const iframeBody = iframeRef.current.contentWindow.document.body;
        const iframeConfirmBtn = iframeBody.querySelector('.ibexa-context-menu .ibexa-btn--confirm');
        const iframeCancelBtn = iframeBody.querySelector('.ibexa-context-menu .ibexa-btn--cancel');
        const iframeCloseBtn = iframeBody.querySelector('.ibexa-anchor-navigation-menu__close');

        if (locationId) {
            findLocationsById({ ...restInfo, id: parseInt(locationId.content, 10) }, (createdItems) => {
                if (contentOnTheFlyConfig.autoConfirmAfterPublish) {
                    const items = multiple ? [...selectedLocations, { location: createdItems[0] }] : [{ location: createdItems[0] }];

                    onConfirm(items);

                    return;
                }

                const clonedLoadedLocations = deepClone(loadedLocationsMap);
                const parentLocationData = clonedLoadedLocations[clonedLoadedLocations.length - 1];
                const action = multiple
                    ? { type: 'ADD_SELECTED_LOCATION', location: createdItems[0] }
                    : { type: 'REPLACE_SELECTED_LOCATIONS', locations: [{ location: createdItems[0] }] };

                parentLocationData.subitems = [];
                parentLocationData.totalCount = parentLocationData.totalCount + 1;

                dispatchLoadedLocationsAction({ type: 'SET_LOCATIONS', data: clonedLoadedLocations });
                dispatchSelectedLocationsAction(action);
                cancelContentCreate();
            });
        }

        iframeConfirmBtn?.addEventListener('click', publishContent, false);
        iframeCancelBtn?.addEventListener('click', handleCancelInIframe, false);
        iframeCloseBtn?.addEventListener('click', handleCancelInIframe, false);
    };

    return (
        <div className="m-content-create">
            <iframe src={iframeUrl} className="m-content-create__iframe" ref={iframeRef} onLoad={handleIframeLoad} />
        </div>
    );
};

ibexa.addConfig(
    'adminUiConfig.universalDiscoveryWidget.tabs',
    [
        {
            id: 'content-create',
            component: ContentCreateTabModule,
            label: Translator.trans(/*@Desc("Content create")*/ 'content_create.label', {}, 'ibexa_universal_discovery_widget'),
            icon: ibexa.helpers.icon.getIconPath('search'),
            isHiddenOnList: true,
        },
    ],
    true,
);

export default ContentCreateTabModule;
