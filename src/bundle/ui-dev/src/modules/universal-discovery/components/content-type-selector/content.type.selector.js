import React, { useContext } from 'react';

import { SelectedContentTypesContext } from '../search/search';
import { AllowedContentTypesContext } from '../../universal.discovery.module';

import Collapsible from '../collapsible/collapsible';

const { ibexa } = window;

const ContentTypeSelector = () => {
    const { contentTypes: contentTypesMap } = ibexa.adminUiConfig;
    const allowedContentTypes = useContext(AllowedContentTypesContext);
    const [selectedContentTypes, dispatchSelectedContentTypesAction] = useContext(SelectedContentTypesContext);
    const handleContentTypeSelect = ({ nativeEvent }) => {
        const { contentTypeIdentifier } = nativeEvent.target.dataset;
        const action = { contentTypeIdentifier };

        action.type = selectedContentTypes.includes(contentTypeIdentifier) ? 'REMOVE_CONTENT_TYPE' : 'ADD_CONTENT_TYPE';

        dispatchSelectedContentTypesAction(action);
    };

    return (
        <>
            {Object.entries(contentTypesMap).map(([contentTypeGroup, contentTypes]) => {
                const isHiddenGroup = contentTypes.every(
                    (contentType) => allowedContentTypes && !allowedContentTypes.includes(contentType.identifier),
                );

                if (isHiddenGroup) {
                    return null;
                }

                return (
                    <Collapsible key={contentTypeGroup} title={contentTypeGroup}>
                        <ul className="c-filters__collapsible-list">
                            {contentTypes.map((contentType) => {
                                const isHidden = allowedContentTypes && !allowedContentTypes.includes(contentType.identifier);

                                if (isHidden) {
                                    return null;
                                }

                                return (
                                    <li key={contentType.identifier} className="c-filters__collapsible-list-item">
                                        <div className="form-check form-check-inline">
                                            <input
                                                type="checkbox"
                                                id={`ibexa-search-content-type-${contentType.identifier}`}
                                                className="ibexa-input ibexa-input--checkbox form-check-input"
                                                value={contentType.identifier}
                                                data-content-type-identifier={contentType.identifier}
                                                onChange={handleContentTypeSelect}
                                                checked={selectedContentTypes.includes(contentType.identifier)}
                                            />
                                            <label
                                                className="checkbox-inline form-check-label"
                                                htmlFor={`ibexa-search-content-type-${contentType.identifier}`}
                                                title={contentType.name}
                                                data-tooltip-container-selector=".c-udw-tab"
                                            >
                                                {contentType.name}
                                            </label>
                                        </div>
                                    </li>
                                );
                            })}
                        </ul>
                    </Collapsible>
                );
            })}
        </>
    );
};

export default ContentTypeSelector;
