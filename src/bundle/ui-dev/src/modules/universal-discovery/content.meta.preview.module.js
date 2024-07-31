import React, { useContext, useEffect, useMemo, useRef } from 'react';

import Icon from '../common/icon/icon';
import Thumbnail from '../common/thumbnail/thumbnail';
import { createCssClassNames } from '../common/helpers/css.class.names';
import { findMarkedLocation } from './helpers/locations.helper';
import { addBookmark, removeBookmark } from './services/universal.discovery.service';
import ContentEditButton from './components/content-edit-button/content.edit.button';
import {
    MarkedLocationIdContext,
    LoadedLocationsMapContext,
    ContentTypesMapContext,
    RestInfoContext,
    AllowRedirectsContext,
} from './universal.discovery.module';

import { formatShortDateTime } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/timezone.helper';
import { parse as parseTooltip } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/tooltips.helper';
import {
    getTranslator,
    getRouting,
    getAdminUiConfig,
    SYSTEM_ROOT_LOCATION_ID,
} from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

const ContentMetaPreview = () => {
    const Translator = getTranslator();
    const Routing = getRouting();
    const adminUiConfig = getAdminUiConfig();
    const refContentMetaPreview = useRef(null);
    const [markedLocationId] = useContext(MarkedLocationIdContext);
    const [loadedLocationsMap, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);
    const contentTypesMap = useContext(ContentTypesMapContext);
    const restInfo = useContext(RestInfoContext);
    const allowRedirects = useContext(AllowRedirectsContext);
    const locationData = useMemo(() => findMarkedLocation(loadedLocationsMap, markedLocationId), [markedLocationId, loadedLocationsMap]);
    const lastModifiedLabel = Translator.trans(/*@Desc("Modified")*/ 'meta_preview.last_modified', {}, 'ibexa_universal_discovery_widget');
    const creationDateLabel = Translator.trans(/*@Desc("Created")*/ 'meta_preview.creation_date', {}, 'ibexa_universal_discovery_widget');
    const translationsLabel = Translator.trans(
        /*@Desc("Translations")*/ 'meta_preview.translations',
        {},
        'ibexa_universal_discovery_widget',
    );
    const { languages, timezone, dateFormat } = adminUiConfig;

    useEffect(() => {
        parseTooltip(refContentMetaPreview.current);
    });

    if (!markedLocationId || markedLocationId === SYSTEM_ROOT_LOCATION_ID || !locationData) {
        return null;
    }

    const { bookmarked, location, version, permissions } = locationData;
    const bookmarkIconName = bookmarked ? 'bookmark-active' : 'bookmark';
    const isLocationDataLoaded = !!(location && version);
    const toggleBookmarked = () => {
        const toggleBookmark = bookmarked ? removeBookmark : addBookmark;

        toggleBookmark({ ...restInfo, locationId: location.id }, () => {
            dispatchLoadedLocationsAction({ type: 'UPDATE_LOCATIONS', data: { ...locationData, bookmarked: !bookmarked } });
        });
    };
    const previewContent = () => {
        location.href = Routing.generate(
            'ibexa.content.view',
            { contentId: location.ContentInfo.Content._id, locationId: location.id },
            true,
        );
    };
    const renderActions = () => {
        const previewLabel = Translator.trans(/*@Desc("Preview")*/ 'meta_preview.preview', {}, 'ibexa_universal_discovery_widget');
        const editLabel = Translator.trans(/*@Desc("Edit")*/ 'meta_preview.edit', {}, 'ibexa_universal_discovery_widget');
        const bookmarksAddLabel = Translator.trans(
            /*@Desc("Add to bookmarks")*/ 'meta_preview.bookmarks_add',
            {},
            'ibexa_universal_discovery_widget',
        );
        const bookmarksRemoveLabel = Translator.trans(
            /*@Desc("Remove from bookmarks")*/ 'meta_preview.bookrmarks_remove',
            {},
            'ibexa_universal_discovery_widget',
        );

        const previewButton = allowRedirects ? (
            <div className="c-content-meta-preview__action-item">
                <button
                    className="c-content-meta-preview__preview-button btn ibexa-btn ibexa-btn--ghost"
                    type="button"
                    onClick={previewContent}
                >
                    <Icon name="view" extraClasses="ibexa-icon--small" />
                    {previewLabel}
                </button>
            </div>
        ) : null;
        const hasAccess = permissions && permissions.edit.hasAccess;

        return (
            <div className="c-content-meta-preview__actions">
                {previewButton}
                <div className="c-content-meta-preview__action-item">
                    <ContentEditButton location={location} version={version} isDisabled={!hasAccess} label={editLabel} />
                </div>
                <div className="c-content-meta-preview__action-item">
                    <button
                        className="c-content-meta-preview__toggle-bookmark-button btn ibexa-btn ibexa-btn--ghost"
                        type="button"
                        onClick={toggleBookmarked}
                    >
                        <Icon name={bookmarkIconName} extraClasses="ibexa-icon--small" />
                        {bookmarked ? bookmarksRemoveLabel : bookmarksAddLabel}
                    </button>
                </div>
            </div>
        );
    };
    const renderMetaPreviewLoadingSpinner = () => {
        const spinnerClassName = createCssClassNames({
            'c-content-meta-preview__loading-spinner': true,
            'c-content-meta-preview__loading-spinner--hidden': isLocationDataLoaded,
        });

        return (
            <div className={spinnerClassName}>
                <Icon name="spinner" extraClasses="ibexa-icon--medium ibexa-spin" />
            </div>
        );
    };
    const renderMetaPreview = () => {
        if (!isLocationDataLoaded) {
            return;
        }

        return (
            <>
                <div className="c-content-meta-preview__preview">
                    <Thumbnail thumbnailData={version.Thumbnail} iconExtraClasses="ibexa-icon--extra-large" />
                </div>
                {renderActions()}
                <div className="c-content-meta-preview__header">
                    <span className="c-content-meta-preview__content-name">{location.ContentInfo.Content.TranslatedName}</span>
                </div>
                <div className="c-content-meta-preview__info">
                    <div className="c-content-meta-preview__content-type-name">
                        {contentTypesMap[location.ContentInfo.Content.ContentType._href].name}
                    </div>
                    <div className="c-content-meta-preview__details">
                        <div className="c-content-meta-preview__details-item">
                            <div className="c-content-meta-preview__details-item-row">{lastModifiedLabel}</div>
                            <div className="c-content-meta-preview__details-item-row">
                                {formatShortDateTime(
                                    new Date(location.ContentInfo.Content.lastModificationDate),
                                    timezone,
                                    dateFormat.shortDateTimeFormat,
                                )}
                            </div>
                        </div>
                        <div className="c-content-meta-preview__details-item">
                            <div className="c-content-meta-preview__details-item-row">{creationDateLabel}</div>
                            <div className="c-content-meta-preview__details-item-row">
                                {formatShortDateTime(
                                    new Date(location.ContentInfo.Content.publishedDate),
                                    timezone,
                                    dateFormat.shortDateTimeFormat,
                                )}
                            </div>
                        </div>
                        <div className="c-content-meta-preview__details-item">
                            <div className="c-content-meta-preview__details-item-row">{translationsLabel}</div>
                            <div className="c-content-meta-preview__details-item-row c-content-meta-preview__translations-wrapper">
                                {version.VersionInfo.languageCodes.split(',').map((languageCode) => {
                                    return (
                                        <span key={languageCode} className="c-content-meta-preview__translation">
                                            {languages.mappings[languageCode].name}
                                        </span>
                                    );
                                })}
                            </div>
                        </div>
                    </div>
                </div>
            </>
        );
    };

    return (
        <div className="c-content-meta-preview" ref={refContentMetaPreview}>
            {renderMetaPreviewLoadingSpinner()}
            {renderMetaPreview()}
        </div>
    );
};

export default ContentMetaPreview;
