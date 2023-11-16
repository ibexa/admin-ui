import React, { useContext } from 'react';
import {
    SelectedContentTypesContext,
    SelectedSectionContext,
    SelectedSubtreeContext,
    SelectedSubtreeBreadcrumbsContext,
} from '../search/search';
import Tag from '../../../common/tag/tag';
import { getAdminUiConfig } from '../../../modules.service';

const SearchTags = () => {
    const adminUiConfig = getAdminUiConfig();
    const [selectedContentTypes, dispatchSelectedContentTypesAction] = useContext(SelectedContentTypesContext);
    const [selectedSection, setSelectedSection] = useContext(SelectedSectionContext);
    const [, setSelectedSubtree] = useContext(SelectedSubtreeContext);
    const [selectedSubtreeBreadcrumbs, setSelectedSubtreeBreadcrumbs] = useContext(SelectedSubtreeBreadcrumbsContext);
    const clearSelectedSubtree = () => {
        setSelectedSubtree('');
        setSelectedSubtreeBreadcrumbs('');
    };
    const contentTypesMap = Object.values(adminUiConfig.contentTypes).reduce((contentTypeDataMap, contentTypeGroup) => {
        for (const contentTypeData of contentTypeGroup) {
            contentTypeDataMap[contentTypeData.identifier] = contentTypeData;
        }

        return contentTypeDataMap;
    }, {});

    return (
        <div className="c-search-tags">
            {selectedContentTypes.map((contentTypeIdentifier) => (
                <Tag
                    key={contentTypeIdentifier}
                    content={contentTypesMap[contentTypeIdentifier].name}
                    onRemove={() => dispatchSelectedContentTypesAction({ contentTypeIdentifier, type: 'REMOVE_CONTENT_TYPE' })}
                    extraClasses="c-search-tags__tag"
                />
            ))}
            {!!selectedSection && (
                <Tag content={selectedSection} onRemove={() => setSelectedSection('')} extraClasses="c-search-tags__tag" />
            )}
            {!!selectedSubtreeBreadcrumbs && (
                <Tag content={selectedSubtreeBreadcrumbs} onRemove={() => clearSelectedSubtree()} extraClasses="c-search-tags__tag" />
            )}
        </div>
    );
};

SearchTags.propTypes = {};

export default SearchTags;
