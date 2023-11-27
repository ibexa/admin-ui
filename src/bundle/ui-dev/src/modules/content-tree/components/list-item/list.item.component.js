import React, { Component } from 'react';
import PropTypes from 'prop-types';
import Icon from '../../../common/icon/icon';

import { showWarningNotification } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/notification.helper';
import { getContentTypeIconUrl } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/content.type.helper';
import { getTranslator, getAdminUiConfig } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

class ListItem extends Component {
    constructor(props) {
        super(props);

        this.toggleExpandedState = this.toggleExpandedState.bind(this);
        this.cancelLoadingState = this.cancelLoadingState.bind(this);
        this.loadMoreSubitems = this.loadMoreSubitems.bind(this);
        this.handleAfterExpandedStateChange = this.handleAfterExpandedStateChange.bind(this);

        this.Translator = getTranslator();
        this.adminUiConfig = getAdminUiConfig();
        this.secondaryItemActions = this.getSecondaryItemActions();
        this.sortedActions = this.getSortedActions();

        this.state = {
            isExpanded: !!props.subitems.length,
            isLoading: false,
        };
    }

    getSecondaryItemActions() {
        const { secondaryItemActions } = this.adminUiConfig.contentTreeWidget;

        if (!secondaryItemActions) {
            return [];
        }

        return [...secondaryItemActions].sort((prefixActionA, prefixActionB) => {
            return prefixActionB.priority - prefixActionA.priority;
        });
    }

    getSortedActions() {
        const { itemActions } = this.adminUiConfig.contentTreeWidget;
        const actions = itemActions ? [...itemActions] : [];

        return actions.sort((actionA, actionB) => {
            return actionB.priority - actionA.priority;
        });
    }

    cancelLoadingState() {
        this.setState(() => ({ isLoading: false }));
    }

    toggleExpandedState() {
        const { path: currentPath, treeMaxDepth } = this.props;
        const currentDepth = currentPath.split(',').length - 1;

        if (currentDepth >= treeMaxDepth) {
            const notificationMessage = this.Translator.trans(
                /*@Desc("Cannot load sub-items for this Location because you reached max tree depth.")*/ 'expand_item.limit.message',
                {},
                'ibexa_content_tree',
            );

            showWarningNotification(notificationMessage);

            return;
        }

        this.setState(
            (state) => ({ isExpanded: !state.isExpanded }),
            () => {
                const { afterItemToggle, path } = this.props;

                afterItemToggle(path, this.state.isExpanded);
                this.handleAfterExpandedStateChange();
            },
        );
    }

    handleAfterExpandedStateChange() {
        const loadInitialItems = this.state.isExpanded && !this.props.subitems.length;

        if (loadInitialItems) {
            this.loadMoreSubitems();
        }
    }

    loadMoreSubitems() {
        const { subitems, subitemsLimit } = this.props;
        const subitemsLimitReached = subitems.length >= subitemsLimit;

        if (this.state.isLoading || subitemsLimitReached) {
            return;
        }

        const { path, locationId, subitemsLoadLimit, loadMoreSubitems } = this.props;

        this.setState(
            () => ({ isLoading: true }),
            () =>
                loadMoreSubitems(
                    {
                        path,
                        parentLocationId: locationId,
                        offset: subitems.length,
                        limit: subitemsLoadLimit,
                    },
                    this.cancelLoadingState,
                ),
        );
    }

    checkCanLoadMore() {
        const { subitems, totalSubitemsCount } = this.props;

        return subitems.length < totalSubitemsCount;
    }

    renderIcon() {
        const { contentTypeIdentifier, locationId } = this.props;
        const iconAttrs = {
            extraClasses: 'ibexa-icon--small ibexa-icon--dark',
        };

        if (!this.state.isLoading || this.props.subitems.length) {
            if (locationId === 1) {
                iconAttrs.customPath = getContentTypeIconUrl('folder');
            } else {
                iconAttrs.customPath = getContentTypeIconUrl(contentTypeIdentifier) || getContentTypeIconUrl('file');
            }
        } else {
            iconAttrs.name = 'spinner';
            iconAttrs.extraClasses = `${iconAttrs.extraClasses} ibexa-spin`;
        }

        return (
            <span className="c-list-item__icon">
                <Icon {...iconAttrs} />
            </span>
        );
    }

    renderLoadMoreBtn() {
        const { subitems, subitemsLimit } = this.props;
        const subitemsLimitReached = subitems.length >= subitemsLimit;

        if (!this.state.isExpanded || subitemsLimitReached || !this.checkCanLoadMore() || !subitems.length) {
            return null;
        }

        const { isLoading } = this.state;
        const seeMoreLabel = Translator.trans(/*@Desc("See more")*/ 'see_more', {}, 'ibexa_content_tree');
        const loadingMoreLabel = Translator.trans(/*@Desc("Loading more...")*/ 'loading_more', {}, 'ibexa_content_tree');
        const btnLabel = isLoading ? loadingMoreLabel : seeMoreLabel;
        let loadingSpinner = null;

        if (isLoading) {
            loadingSpinner = <Icon name="spinner" extraClasses="ibexa-spin ibexa-icon--small c-list-item__load-more-btn-spinner" />;
        }

        return (
            <button type="button" className="c-list-item__load-more-btn" onClick={this.loadMoreSubitems}>
                {loadingSpinner} {btnLabel}
            </button>
        );
    }

    renderSubitemsLimitReachedInfo() {
        const { subitems, subitemsLimit } = this.props;
        const subitemsLimitReached = subitems.length >= subitemsLimit;

        if (!this.state.isExpanded || !subitemsLimitReached) {
            return null;
        }

        const message = this.Translator.trans(/*@Desc("Loading limit reached")*/ 'show_more.limit_reached', {}, 'ibexa_content_tree');

        return <div className="c-list-item__load-more-limit-info">{message}</div>;
    }

    renderItemLabel() {
        const { href, name, locationId, indent, onClick } = this.props;

        if (locationId === 1) {
            return null;
        }

        const togglerClassName = 'c-list-item__toggler';
        const togglerAttrs = {
            className: togglerClassName,
            onClick: this.toggleExpandedState,
            tabIndex: -1,
        };

        return (
            <div className="c-list-item__row" style={{ '--indent': indent }}>
                <div className="c-list-item__prefix-actions">
                    {this.secondaryItemActions.map((action) => {
                        const ActionComponent = action.component;

                        return (
                            <div key={action.id} className="c-list-item__prefix-actions-item">
                                <ActionComponent {...this.props} />
                            </div>
                        );
                    })}
                </div>
                <span {...togglerAttrs} />
                <a className="c-list-item__label" href={href} onClick={onClick}>
                    {this.renderIcon()}
                    <span className="c-list-item__label-content" title={name}>
                        {name}
                    </span>
                </a>
                <div className="c-list-item__actions">
                    {this.sortedActions.map((action) => {
                        const ActionComponent = action.component;

                        return (
                            <div key={action.id} className="c-list-item__actions-item">
                                <ActionComponent {...this.props} />
                            </div>
                        );
                    })}
                </div>
            </div>
        );
    }

    render() {
        const { totalSubitemsCount, children, isInvisible, selected } = this.props;
        const itemClassName = 'c-list-item';
        const itemAttrs = { className: itemClassName };

        if (totalSubitemsCount) {
            itemAttrs.className = `${itemAttrs.className} ${itemClassName}--has-sub-items`;
        }

        if (this.checkCanLoadMore()) {
            itemAttrs.className = `${itemAttrs.className} ${itemClassName}--can-load-more`;
        }

        if (this.state.isExpanded) {
            itemAttrs.className = `${itemAttrs.className} ${itemClassName}--is-expanded`;
        }

        if (isInvisible) {
            itemAttrs.className = `${itemAttrs.className} ${itemClassName}--is-hidden`;
        }

        if (selected) {
            itemAttrs.className = `${itemAttrs.className} ${itemClassName}--is-selected`;
        }

        if (this.props.isRootItem) {
            itemAttrs.className = `${itemAttrs.className} ${itemClassName}--is-root-item`;
        }

        return (
            <li {...itemAttrs}>
                {this.renderItemLabel()}
                {children}
                {this.renderLoadMoreBtn()}
                {this.renderSubitemsLimitReachedInfo()}
            </li>
        );
    }
}

ListItem.propTypes = {
    path: PropTypes.string.isRequired,
    href: PropTypes.string.isRequired,
    contentTypeIdentifier: PropTypes.string.isRequired,
    totalSubitemsCount: PropTypes.number.isRequired,
    subitems: PropTypes.array.isRequired,
    children: PropTypes.element,
    hidden: PropTypes.bool,
    isContainer: PropTypes.bool.isRequired,
    selected: PropTypes.bool.isRequired,
    locationId: PropTypes.number.isRequired,
    name: PropTypes.string.isRequired,
    isInvisible: PropTypes.bool.isRequired,
    loadMoreSubitems: PropTypes.func.isRequired,
    subitemsLimit: PropTypes.number.isRequired,
    subitemsLoadLimit: PropTypes.number,
    treeMaxDepth: PropTypes.number.isRequired,
    afterItemToggle: PropTypes.func.isRequired,
    isRootItem: PropTypes.bool,
    onClick: PropTypes.func,
    indent: PropTypes.number,
    adminUiConfig: PropTypes.object,
};

ListItem.defaultProps = {
    children: null,
    hidden: false,
    isRootItem: false,
    onClick: () => {},
    subitemsLoadLimit: null,
    indent: 0,
    adminUiConfig: window.ibexa?.adminUiConfig,
};

export default ListItem;
