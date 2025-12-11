import React, { useContext, useState, useEffect, useRef, useMemo } from 'react';
import PropTypes from 'prop-types';

import { parse as parseTooltip } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/tooltips.helper';
import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

import Icon from '../../../common/icon/icon';
import Spinner from '../../../common/spinner/spinner';
import EmptyTableBodyRow from '../../../common/table/empty.table.body.row';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import { findMarkedLocation } from '../../helpers/locations.helper';
import { useLoadBookmarksFetch } from '../../hooks/useLoadBookmarksFetch';
import {
    ContentTypesMapContext,
    MarkedLocationIdContext,
    LoadedLocationsMapContext,
    SelectedLocationsContext,
    MultipleConfigContext,
    ContainersOnlyContext,
    AllowedContentTypesContext,
} from '../../universal.discovery.module';

const SCROLL_OFFSET = 200;

const BookmarksList = ({ setBookmarkedLocationMarked, itemsPerPage = 50 }) => {
    const Translator = getTranslator();
    const refBookmarksList = useRef(null);
    const [offset, setOffset] = useState(0);
    const [bookmarks, setBookmarks] = useState([]);
    const [markedLocationId, setMarkedLocationId] = useContext(MarkedLocationIdContext);
    const [loadedLocationsMap, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);
    const [, dispatchSelectedLocationsAction] = useContext(SelectedLocationsContext);
    const [multiple] = useContext(MultipleConfigContext);
    const allowedContentTypes = useContext(AllowedContentTypesContext);
    const contentTypesMap = useContext(ContentTypesMapContext);
    const containersOnly = useContext(ContainersOnlyContext);
    const markedLocationData = useMemo(
        () => findMarkedLocation(loadedLocationsMap, markedLocationId),
        [markedLocationId, loadedLocationsMap],
    );
    const [data, isLoading, reloadBookmarks] = useLoadBookmarksFetch(itemsPerPage, offset);
    const areSomeBookmarksAdded = bookmarks.length === 0;
    const containerClassName = createCssClassNames({
        'c-bookmarks-list': true,
        'c-bookmarks-list--no-items': areSomeBookmarksAdded,
    });
    const noBookmarksInfoText = Translator.trans(
        /*@Desc("You have no bookmarks yet")*/ 'bookmarks_tab.no_items.info_text',
        {},
        'ibexa_universal_discovery_widget',
    );
    const noBookmarksActionText = Translator.trans(
        /*@Desc("Your bookmarks will show up here.")*/ 'bookmarks_tab.no_items.action_text',
        {},
        'ibexa_universal_discovery_widget',
    );
    const loadMore = ({ target }) => {
        const areAllItemsLoaded = bookmarks.length >= data.count;
        const isOffsetReached = target.scrollHeight - target.clientHeight - target.scrollTop < SCROLL_OFFSET;

        if (areAllItemsLoaded || !isOffsetReached || isLoading) {
            return;
        }

        setOffset(offset + itemsPerPage);
    };
    const renderLoadingSpinner = () => {
        if (!isLoading) {
            return null;
        }

        return (
            <div className="c-bookmarks-list__spinner-wrapper">
                <Spinner />
            </div>
        );
    };
    const renderNoBookmarksContent = () => {
        return (
            <table>
                <tbody>
                    <EmptyTableBodyRow infoText={noBookmarksInfoText} actionText={noBookmarksActionText} />
                </tbody>
            </table>
        );
    };

    useEffect(() => {
        if (isLoading) {
            return;
        }

        setBookmarks((prevState) => [...prevState, ...data.items]);
    }, [data.items, isLoading]);

    useEffect(() => {
        parseTooltip(refBookmarksList.current);
    }, [bookmarks]);

    useEffect(() => {
        const isBookmarkMarked = bookmarks.some(({ id }) => id === markedLocationId);

        if (isBookmarkMarked && markedLocationData.bookmarked === false) {
            reloadBookmarks();
            setBookmarks([]);
            setMarkedLocationId(null);
        } else if (!isBookmarkMarked && markedLocationData.bookmarked) {
            reloadBookmarks();
            setBookmarks([]);
        }
    }, [markedLocationData.bookmarked]);

    return (
        <div className={containerClassName} onScroll={loadMore} ref={refBookmarksList}>
            {!isLoading && areSomeBookmarksAdded && renderNoBookmarksContent()}
            {bookmarks.map((bookmark) => {
                const isMarked = bookmark.id === markedLocationId;
                const contentTypeInfo = contentTypesMap[bookmark.ContentInfo.Content.ContentType._href];
                const { isContainer } = contentTypeInfo;
                const isNotSelectable =
                    (containersOnly && !isContainer) || (allowedContentTypes && !allowedContentTypes.includes(contentTypeInfo.identifier));
                const className = createCssClassNames({
                    'c-bookmarks-list__item': true,
                    'c-bookmarks-list__item--marked': isMarked,
                    'c-bookmarks-list__item--not-selectable': isNotSelectable,
                });
                const markLocation = () => {
                    if (isMarked) {
                        return;
                    }

                    dispatchLoadedLocationsAction({ type: 'CLEAR_LOCATIONS' });
                    setBookmarkedLocationMarked(bookmark.id);

                    if (!multiple) {
                        dispatchSelectedLocationsAction({ type: 'CLEAR_SELECTED_LOCATIONS' });

                        if (!isNotSelectable) {
                            dispatchSelectedLocationsAction({ type: 'ADD_SELECTED_LOCATION', location: bookmark });
                        }
                    }
                };

                return (
                    <div key={bookmark.id} className={className} onClick={markLocation}>
                        <Icon extraClasses="ibexa-icon--small-medium" customPath={contentTypeInfo.thumbnail} />
                        <span
                            title={bookmark.ContentInfo.Content.TranslatedName}
                            data-tooltip-container-selector=".c-bookmarks-list"
                            className="c-bookmarks-list__item-name"
                        >
                            {bookmark.ContentInfo.Content.TranslatedName}
                        </span>
                    </div>
                );
            })}
            {renderLoadingSpinner()}
        </div>
    );
};

BookmarksList.propTypes = {
    setBookmarkedLocationMarked: PropTypes.func.isRequired,
    itemsPerPage: PropTypes.number,
};

export default BookmarksList;
