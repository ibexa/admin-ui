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
import { createCssClassNames } from '../common/helpers/css.class.names';

const generateIframeUrl = ({ locationId, languageCode, contentTypeIdentifier }) => {
    return window.Routing.generate('ezplatform.content_on_the_fly.create', {
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
    const [activeTab, setActiveTab] = useContext(ActiveTabContext);
    const [createContentVisible, setCreateContentVisible] = useContext(CreateContentWidgetContext);
    const [selectedLocations, dispatchSelectedLocationsAction] = useContext(SelectedLocationsContext);
    const [loadedLocationsMap, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);
    const [multiple, multipleItemsLimit] = useContext(MultipleConfigContext);
    const iframeUrl = generateIframeUrl(contentOnTheFlyData);
    const iframeRef = createRef();
    const cancelContentCreate = (event) => {
        event.preventDefault();
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
    const handleIframeLoad = () => {
        const locationId = iframeRef.current.contentWindow.document.querySelector('meta[name="LocationID"]');
        const iframeBody = iframeRef.current.contentWindow.document.body;
        const iframeConfirmButton = iframeBody.querySelector('.ibexa-btn--confirm');
        const iframeCancelButton = iframeBody.querySelector('.ibexa-btn--cancel');

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

        iframeConfirmButton?.addEventListener('click', publishContent, false);
        iframeCancelButton?.addEventListener('click', cancelContentCreate, false);
    };
    const cancelLabel = Translator.trans(/*@Desc("Cancel")*/ 'content_create.cancel.label', {}, 'universal_discovery_widget');
    const confirmLabel = Translator.trans(/*@Desc("Confirm")*/ 'content_create.confirm.label', {}, 'universal_discovery_widget');

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
            label: Translator.trans(/*@Desc("Content create")*/ 'content_create.label', {}, 'universal_discovery_widget'),
            icon: window.ibexa.helpers.icon.getIconPath('search'),
            isHiddenOnList: true,
        },
    ],
    true
);

export default ContentCreateTabModule;
