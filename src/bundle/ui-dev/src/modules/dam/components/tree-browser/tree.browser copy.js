import React, { useState, useEffect, useRef, useContext, useMemo } from 'react';
import PropTypes from 'prop-types';
import Icon from '../../../common/icon/icon';
import {
    LoadedLocationsMapContext,
    MarkedLocationIdContext,
    RestInfoContext,
    RootLocationIdContext,
} from '../../../universal-discovery/universal.discovery.module';
import { createCssClassNames } from '../../../common/helpers/css.class.names';

import { getData, saveData } from '@ibexa-tree-builder/src/bundle/ui-dev/src/modules/tree-builder/helpers/localStorage';
import { loadLocationItems, loadSubtree } from '@ibexa-content-tree/src/bundle/ui-dev/src/modules/common/services/content.tree.service';
import {
    findItem,
    generateInitialSubtree,
    expandCurrentLocationInSubtree,
    clipTooDeepSubtreeBranches,
    limitSubitemsInSubtree,
    getLoadSubtreeParams,
    generateSubtree,
    addItemToSubtree,
    removeItemFromSubtree,
    updateItemInSubtree,
} from '@ibexa-content-tree/src/bundle/ui-dev/src/modules/common/helpers/tree';
import deepClone from '../../../common/helpers/deep.clone.helper';
import { getLocationData } from '../../../universal-discovery/content.meta.preview.module';

const { ibexa, Translator } = window;

const MODULE_ID = 'ibexa-image-picker-tree-browser';
const SUBTREE_ID = 'subtree';

const TreeBrowser = (props) => {
    const { subitemsLimit, subitemsLoadLimit, treeMaxDepth } = props;
    const restInfo = useContext(RestInfoContext);
    const rootLocationId = useContext(RootLocationIdContext);
    const [loadedLocationsMap, dispatchLoadedLocationsAction] = useContext(LoadedLocationsMapContext);
    const [markedLocationId, setMarkedLocationId] = useContext(MarkedLocationIdContext);
    const locationData = useMemo(() => getLocationData(loadedLocationsMap, markedLocationId), [markedLocationId, loadedLocationsMap]);
    const currentLocationPath = locationData && locationData.location ? locationData.location.pathString : '/1/';
    const treeBuilderModuleRef = useRef(null);
    const userId = ibexa.helpers.user.getId();
    const readSubtree = () => {
        return getData({ moduleId: MODULE_ID, userId, subId: rootLocationId, dataId: SUBTREE_ID });
    };
    const saveSubtree = () => {
        saveData({ moduleId: MODULE_ID, userId, subId: rootLocationId, dataId: SUBTREE_ID, data: subtree.current });
    };
    const [isLoaded, setIsLoaded] = useState(false);
    const [tree, setTree] = useState({});
    const subtree = useRef(generateInitialSubtree({ rootLocationId, subitemsLoadLimit })); // subtree is actually tree request data
    const getCurrentLocationId = () => {
        const currentLocationIdString = currentLocationPath
            .split('/')
            .filter((id) => !!id)
            .pop();

        return parseInt(currentLocationIdString, 10);
    };
    const setInitialItemsState = (location) => {
        subtree.current = generateSubtree({ items: [location], isRoot: true, subitemsLoadLimit, subitemsLimit });

        setIsLoaded(true);
        setTree(location);
        saveSubtree();
    };
    const loadTreeToState = () => {
        const { sort } = props;
        loadSubtree(getLoadSubtreeParams({ subtree, restInfo, sort }))
            .then((loadedSubtree) => {
                setInitialItemsState(loadedSubtree[0]);

                const path = currentLocationPath.split('/').filter((id) => !!id);
                const rootLocationIdIndex = path.findIndex((element) => parseInt(element, 10) === rootLocationId);

                if (rootLocationIdIndex !== -1) {
                    const pathStartingOnRootLocation = path.slice(rootLocationIdIndex - path.length);
                    const itemsToExpand = pathStartingOnRootLocation.map((locationId) => ({ id: parseInt(locationId, 10) }));

                    treeBuilderModuleRef.current?.expandItems(itemsToExpand);
                }
            })
            .catch(window.ibexa.helpers.notification.showErrorNotification);
    };
    const renderLabel = (item, otherProps) => {
        const { name, contentTypeIdentifier, locationId } = item.internalItem;
        const { isLoading, labelTruncatedCallbackRef } = otherProps;
        const iconAttrs = {
            extraClasses: 'ibexa-icon--small ibexa-icon--dark',
        };

        if (!isLoading || item.subitems.length) {
            if (locationId === 1) {
                iconAttrs.customPath = ibexa.helpers.contentType.getContentTypeIconUrl('folder');
            } else {
                iconAttrs.customPath =
                    ibexa.helpers.contentType.getContentTypeIconUrl(contentTypeIdentifier) ||
                    ibexa.helpers.contentType.getContentTypeIconUrl('file');
            }
        } else {
            iconAttrs.name = 'spinner';
            iconAttrs.extraClasses = `${iconAttrs.extraClasses} ibexa-spin`;
        }

        return (
            <>
                <span className="c-ct-list-item__icon">
                    <Icon {...iconAttrs} />
                </span>
                <span className="c-tb-list-item-single__label-truncated" title={name} ref={labelTruncatedCallbackRef}>
                    {name}
                </span>
            </>
        );
    };
    const callbackToggleExpanded = (item, { isExpanded, loadMore }) => {
        if (isExpanded) {
            addItemToSubtree(subtree.current[0], item.internalItem, item.internalItem.path.split('/'), {
                subitemsLoadLimit,
                subitemsLimit,
            });
        } else {
            removeItemFromSubtree(subtree.current[0], item.internalItem, item.internalItem.path.split('/'));
        }

        saveSubtree();

        const { subitems } = item;
        const shouldLoadInitialItems = isExpanded && subitems && !subitems.length;

        if (shouldLoadInitialItems) {
            loadMore();
        }
    };
    const isActive = (item) => {
        return item.internalItem.locationId === getCurrentLocationId();
    };
    const loadMoreSubitems = (item) =>
        loadLocationItems({
            ...restInfo,
            parentLocationId: item.internalItem.locationId,
            limit: props.subitemsLoadLimit,
            offset: item.internalItem.subitems.length,
        })
            .then((location) => {
                setTree((prevTree) => {
                    const prevTreeParentItem = findItem([prevTree], item.internalItem.path.split('/'));

                    if (prevTreeParentItem) {
                        const nextTree = deepClone(prevTree);
                        const nextTreeParentItem = findItem([nextTree], item.internalItem.path.split('/'));

                        nextTreeParentItem.subitems = [...nextTreeParentItem.subitems, ...location.subitems].map((subitem) => ({
                            ...subitem,
                            path: `${nextTreeParentItem.path}/${subitem.locationId}`,
                        }));

                        updateItemInSubtree(subtree.current[0], nextTreeParentItem, item.internalItem.path.split('/'));
                        saveSubtree();

                        return nextTree;
                    }

                    return prevTree;
                });
            })
            .catch(window.ibexa.helpers.notification.showErrorNotification);
    const getCustomItemClass = (item) => {
        const { children, total, isRootItem } = item;
        const className = createCssClassNames({
            'c-ct-list-item': true,
            'c-ct-list-item--can-load-more': children && children.length < total,
            'c-ct-list-item--is-root-item': isRootItem,
        });

        return className;
    };
    const renderEmpty = () => {
        if (!isLoaded || tree?.locationId !== undefined) {
            return null;
        }

        const emptyBadge = Translator.trans(/*@Desc("1")*/ 'content.1', {}, 'ibexa_content_tree_ui');
        const emptyContent = Translator.trans(
            /*@Desc("Your tree is empty. Start creating your structure")*/ 'content.empty',
            {},
            'ibexa_content_tree_ui',
        );

        return (
            <div className="c-ct-empty">
                <div className="c-ct-empty__badge">
                    <div className="c-ct-badge">
                        <div className="c-ct-badge__content">{emptyBadge}</div>
                    </div>
                </div>
                <div className="c-ct-empty__content">{emptyContent}</div>
            </div>
        );
    };
    const onItemClick = (locationId) => {
        dispatchLoadedLocationsAction({ type: 'UPDATE_LOCATIONS', data: { parentLocationId: locationId, subitems: [] } });
    }
    const buildItem = (item) =>
        item.internalItem
            ? item
            : {
                  internalItem: item,
                  id: item.locationId,
                  path: item.path,
                  subitems: item.subitems,
                  total: item.totalSubitemsCount,
                  hidden: item.isInvisible,
                  renderLabel,
                  onItemClick: () => onItemClick(item.locationId),
                  customItemClass: getCustomItemClass(item),
              };
    const moduleName = Translator.trans(/*@Desc("Content tree")*/ 'content.tree_name', {}, 'ibexa_content_tree_ui');

    useEffect(() => {
        const subtreeData = readSubtree();

        if (subtreeData) {
            subtree.current = subtreeData;
        }

        expandCurrentLocationInSubtree({ subtree: subtree.current, rootLocationId, currentLocationPath, subitemsLimit });
        clipTooDeepSubtreeBranches({ subtree: subtree.current[0], maxDepth: treeMaxDepth - 1 });
        subtree.current[0].children.forEach((subtreeChild) => limitSubitemsInSubtree({ subtree: subtreeChild, subitemsLimit }));
        saveSubtree();
        loadTreeToState();
    }, []);

    useEffect(() => {
        document.body.addEventListener('ibexa-content-tree-refresh', loadTreeToState);

        return () => {
            document.body.removeEventListener('ibexa-content-tree-refresh', loadTreeToState);
        };
    }, []);

    return (
        <ibexa.modules.TreeBuilder
            ref={treeBuilderModuleRef}
            moduleId={MODULE_ID}
            moduleName={moduleName}
            userId={userId}
            subId={rootLocationId}
            tree={tree}
            buildItem={buildItem}
            isActive={isActive}
            loadMoreSubitems={loadMoreSubitems}
            callbackToggleExpanded={callbackToggleExpanded}
            subitemsLimit={subitemsLimit}
            treeDepthLimit={treeMaxDepth}
            // actionsType={ACTION_TYPE.CONTEXTUAL_MENU}
            dragDisabled={true}
            isLoading={!isLoaded}
        >
            {renderEmpty()}
        </ibexa.modules.TreeBuilder>
    );
};

TreeBrowser.propTypes = {
    subitemsLimit: PropTypes.number,
    subitemsLoadLimit: PropTypes.number,
    treeMaxDepth: PropTypes.number,
    sort: PropTypes.shape({
        sortClause: PropTypes.string,
        sortOrder: PropTypes.string,
    }),
};

TreeBrowser.defaultProps = {
    subitemsLimit: ibexa.adminUiConfig.contentTree.childrenLoadMaxLimit,
    subitemsLoadLimit: ibexa.adminUiConfig.contentTree.loadMoreLimit,
    treeMaxDepth: ibexa.adminUiConfig.contentTree.treeMaxDepth,
    sort: {},
};

export default TreeBrowser;
