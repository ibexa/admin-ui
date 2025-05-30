import React from 'react';
import PropTypes from 'prop-types';
import ListItem from '../list-item/list.item.component';
import { getTranslator, getRouting } from '../../../../../../Resources/public/js/scripts/helpers/context.helper';

const List = ({
    items,
    loadMoreSubitems,
    currentLocationId,
    path,
    subitemsLoadLimit = null,
    subitemsLimit,
    treeMaxDepth,
    afterItemToggle,
    indent = 0,
    isRoot = false,
    onClickItem = () => {},
}) => {
    const Translator = getTranslator();
    const Routing = getRouting();
    const commonAttrs = { loadMoreSubitems, subitemsLoadLimit, subitemsLimit, treeMaxDepth, afterItemToggle, onClickItem };
    const listAttrs = { ...commonAttrs, currentLocationId };
    const listItemAttrs = commonAttrs;
    const renderNoSubitemMessage = () => {
        const [rootLocation] = items;
        const isRootLoaded = rootLocation;
        const noSubitemsMessage = Translator.trans(/* @Desc("This Location has no sub-items") */ 'no_subitems', {}, 'ibexa_content_tree');

        if (!isRoot || !isRootLoaded || (rootLocation.subitems && rootLocation.subitems.length)) {
            return;
        }

        return <div className="c-list__no-items-message">{noSubitemsMessage}</div>;
    };

    return (
        <ul className="c-list">
            {items.map((item) => {
                const hasPreviousPath = path && path.length;
                const locationHref = Routing.generate('ibexa.content.view', {
                    contentId: item.contentId,
                    locationId: item.locationId,
                });
                const itemPath = `${hasPreviousPath ? `${path},` : ''}${item.locationId}`;
                const { subitems } = item;

                return (
                    <ListItem
                        {...item}
                        {...listItemAttrs}
                        key={item.locationId}
                        selected={item.locationId === currentLocationId}
                        href={locationHref}
                        isRootItem={isRoot}
                        onClick={onClickItem.bind(null, item)}
                        path={itemPath}
                        indent={indent}
                    >
                        {subitems.length ? (
                            <List path={itemPath} items={subitems} isRoot={false} indent={indent + 1} {...listAttrs} />
                        ) : (
                            renderNoSubitemMessage()
                        )}
                    </ListItem>
                );
            })}
        </ul>
    );
};

List.propTypes = {
    path: PropTypes.string.isRequired,
    items: PropTypes.array.isRequired,
    loadMoreSubitems: PropTypes.func.isRequired,
    currentLocationId: PropTypes.number.isRequired,
    subitemsLimit: PropTypes.number.isRequired,
    subitemsLoadLimit: PropTypes.number,
    treeMaxDepth: PropTypes.number.isRequired,
    afterItemToggle: PropTypes.func.isRequired,
    indent: PropTypes.number,
    isRoot: PropTypes.bool,
    onClickItem: PropTypes.func,
};

export default List;
