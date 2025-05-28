import React, { Component } from 'react';
import PropTypes from 'prop-types';

import List from '../list/list.component';
import Header from '../header/header';
import Icon from '../../../common/icon/icon';

const { Translator } = window;

const CLASS_IS_TREE_RESIZING = 'ibexa-is-tree-resizing';
const MIN_CONTAINER_WIDTH = 200;
const COLLAPSED_WIDTH = 66;
const EXPANDED_WIDTH = 320;
const DEFAULT_CONTAINER_WIDTH = 300;

export default class ContentTree extends Component {
    constructor(props) {
        super(props);

        this.changeContainerWidth = this.changeContainerWidth.bind(this);
        this.toggleCollapseTree = this.toggleCollapseTree.bind(this);
        this.addWidthChangeListener = this.addWidthChangeListener.bind(this);
        this.handleResizeEnd = this.handleResizeEnd.bind(this);
        this.checkIsTreeCollapsed = this.checkIsTreeCollapsed.bind(this);
        this._refTreeContainer = React.createRef();
        this._refPopupContainer = React.createRef();
        this.scrollTimeout = null;
        this.scrollPositionRestored = false;

        this.state = {
            resizeStartPositionX: 0,
            containerWidth: this.getConfig('width') || DEFAULT_CONTAINER_WIDTH,
            resizedContainerWidth: 0,
            isResizing: false,
        };
    }

    componentWillUnmount() {
        this.clearDocumentResizingListeners();
    }

    componentDidMount() {
        this.containerScrollRef.addEventListener('scroll', (event) => {
            window.clearTimeout(this.scrollTimeout);

            this.scrollTimeout = window.setTimeout(
                (scrollTop) => {
                    this.saveConfig('scrollTop', scrollTop);
                },
                50,
                event.currentTarget.scrollTop,
            );
        });

        document.body.dispatchEvent(
            new CustomEvent('ibexa-tb-rendered', {
                detail: {
                    id: 'ibexa-content-tree',
                },
            }),
        );
    }

    componentDidUpdate(prevState) {
        if (this.state.containerWidth !== prevState.containerWidth) {
            this.saveConfig('width', this.state.containerWidth);

            document.body.dispatchEvent(new CustomEvent('ibexa-content-tree-resized'));
        }

        if (this.props.items && this.props.items.length && !this.scrollPositionRestored) {
            this.scrollPositionRestored = true;

            this.containerScrollRef.scrollTo(0, this.getConfig('scrollTop'));
        }
    }

    saveConfig(id, value) {
        const { userId } = this.props;
        const data = JSON.parse(window.localStorage.getItem('ibexa-content-tree-state') || '{}');

        if (!data[userId]) {
            data[userId] = {};
        }

        data[userId][id] = value;

        window.localStorage.setItem('ibexa-content-tree-state', JSON.stringify(data));
    }

    getConfig(id) {
        const { userId } = this.props;
        const data = JSON.parse(window.localStorage.getItem('ibexa-content-tree-state') || '{}');

        return data[userId]?.[id];
    }

    changeContainerWidth({ clientX }) {
        const currentPositionX = clientX;

        this.setState(
            (state) => ({
                resizedContainerWidth: state.containerWidth + (currentPositionX - state.resizeStartPositionX),
            }),
            () => {
                document.body.dispatchEvent(new CustomEvent('ibexa-content-resized'));
            },
        );
    }

    toggleCollapseTree() {
        const width = this.checkIsTreeCollapsed() ? EXPANDED_WIDTH : COLLAPSED_WIDTH;

        this.setState(
            () => ({
                resizedContainerWidth: width,
                containerWidth: width,
            }),
            () => {
                document.body.dispatchEvent(new CustomEvent('ibexa-content-resized'));
            },
        );
    }

    addWidthChangeListener({ nativeEvent }) {
        const resizeStartPositionX = nativeEvent.clientX;
        const containerWidth = this._refTreeContainer.current.getBoundingClientRect().width;

        window.document.addEventListener('mousemove', this.changeContainerWidth, false);
        window.document.addEventListener('mouseup', this.handleResizeEnd, false);
        window.document.body.classList.add(CLASS_IS_TREE_RESIZING);

        this.setState(() => ({ resizeStartPositionX, containerWidth, resizedContainerWidth: containerWidth, isResizing: true }));
    }

    handleResizeEnd() {
        this.clearDocumentResizingListeners();

        this.setState(
            (state) => ({
                resizeStartPositionX: 0,
                containerWidth: state.resizedContainerWidth,
                isResizing: false,
            }),
            () => {
                document.body.dispatchEvent(new CustomEvent('ibexa-content-resized'));
            },
        );
    }

    clearDocumentResizingListeners() {
        window.document.removeEventListener('mousemove', this.changeContainerWidth);
        window.document.removeEventListener('mouseup', this.handleResizeEnd);
        window.document.body.classList.remove(CLASS_IS_TREE_RESIZING);
    }

    getCollapseAllBtn() {
        const CollapseAction = () => {
            const collapseAllLabel = Translator.trans(/* @Desc("Collapse all") */ 'collapse_all', {}, 'ibexa_content_tree');

            return <div onClick={this.props.onCollapseAllItems}>{collapseAllLabel}</div>;
        };

        return CollapseAction;
    }

    renderHeader() {
        const actions = [
            {
                id: 'collapse-all',
                priority: 0,
                component: this.getCollapseAllBtn(),
            },
        ];

        return (
            <Header
                toggleCollapseTree={this.toggleCollapseTree}
                isCollapsed={this.checkIsTreeCollapsed()}
                popupRef={this._refPopupContainer}
                actions={actions}
            />
        );
    }

    renderList() {
        const { items, loadMoreSubitems, currentLocationId, onClickItem, subitemsLoadLimit, subitemsLimit, treeMaxDepth, afterItemToggle } =
            this.props;

        const attrs = {
            items,
            path: '',
            loadMoreSubitems,
            currentLocationId,
            subitemsLimit,
            subitemsLoadLimit,
            treeMaxDepth,
            afterItemToggle,
            isRoot: true,
            onClickItem,
        };

        return (
            <div className="m-tree__scrollable-wrapper" ref={(ref) => (this.containerScrollRef = ref)}>
                {this.checkIsTreeCollapsed() || !items || !items.length ? null : <List {...attrs} />}
            </div>
        );
    }

    renderLoadingSpinner() {
        const { items } = this.props;

        if (this.checkIsTreeCollapsed() || (items && items.length)) {
            return;
        }

        return (
            <div className="m-tree__loading-spinner">
                <Icon name="spinner" extraClasses="ibexa-icon--medium ibexa-spin" />
            </div>
        );
    }

    checkIsTreeCollapsed() {
        const width = this.state.resizedContainerWidth || this.state.containerWidth;

        return width <= MIN_CONTAINER_WIDTH;
    }

    render() {
        const { resizable } = this.props;
        const { isResizing, containerWidth, resizedContainerWidth } = this.state;

        const width = isResizing ? resizedContainerWidth : containerWidth;
        const containerAttrs = { className: 'm-tree', ref: this._refTreeContainer };

        if (width && resizable) {
            containerAttrs.style = { width: `${width}px` };
        }

        return (
            <>
                <div {...containerAttrs}>
                    {this.renderHeader()}
                    {this.renderList()}
                    {this.renderLoadingSpinner()}
                    <div className="m-tree__resize-handler" onMouseDown={this.addWidthChangeListener} />
                </div>
                <div ref={this._refPopupContainer} />
            </>
        );
    }
}

ContentTree.propTypes = {
    items: PropTypes.array.isRequired,
    loadMoreSubitems: PropTypes.func.isRequired,
    currentLocationId: PropTypes.number.isRequired,
    subitemsLimit: PropTypes.number.isRequired,
    subitemsLoadLimit: PropTypes.number,
    treeMaxDepth: PropTypes.number.isRequired,
    afterItemToggle: PropTypes.func.isRequired,
    onCollapseAllItems: PropTypes.func.isRequired,
    onClickItem: PropTypes.func,
    userId: PropTypes.number.isRequired,
    resizable: PropTypes.bool.isRequired,
};

ContentTree.defaultProps = {
    subitemsLoadLimit: null,
    onClickItem: () => {},
};
