import React, { Component } from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import UploadItemComponent from './upload.item.component';

export default class UploadListComponent extends Component {
    constructor(props) {
        super(props);

        this.state = {
            items: [],
            erroredItems: [],
        };
    }

    componentDidUpdate() {
        this.props.onAfterUpload(this.state.items);
    }

    handleAfterUpload(item) {
        this.props.removeItemsToUpload([item]);
        this.setState((state) => ({
            items: [...state.items, item],
        }));
    }

    handleAfterAbort(item) {
        this.props.removeItemsToUpload([item]);
        this.setState((state) => {
            const items = state.items.filter((data) => data.id !== item.id);

            return { uploaded: items.length, items };
        });
    }

    handleAfterDelete(item) {
        this.setState(
            (state) => {
                const items = state.items.filter((data) => data.id !== item.id);
                const erroredItems = state.erroredItems.filter((data) => data.id !== item.id);

                return { uploaded: items.length, items, erroredItems };
            },
            () => this.props.onAfterDelete(item),
        );
    }

    handleCreateError(item) {
        this.props.removeItemsToUpload([item]);
        this.setState((state) => ({
            erroredItems: [...state.erroredItems, item],
        }));
    }

    removeErroredItems(items) {
        const itemsIds = items.map((item) => item.id);

        this.setState((prevState) => {
            const erroredItems = prevState.erroredItems.filter((stateItem) => !itemsIds.includes(stateItem.id));

            if (erroredItems.length !== prevState.erroredItems.length) {
                return {
                    erroredItems,
                };
            }
        });
    }

    renderItemToUpload(item) {
        return this.renderItem(item, {
            isUploaded: false,
            createFileStruct: this.props.createFileStruct,
            publishFile: this.props.publishFile,
            onAfterAbort: this.handleAfterAbort.bind(this),
            onAfterUpload: this.handleAfterUpload.bind(this),
            onCreateError: this.handleCreateError.bind(this),
            checkCanUpload: this.props.checkCanUpload,
            removeItemsToUpload: this.props.removeItemsToUpload,
        });
    }

    renderUploadedItem(item) {
        return this.renderItem(item, {
            isUploaded: true,
            deleteFile: this.props.deleteFile,
            onAfterDelete: this.handleAfterDelete.bind(this),
        });
    }

    renderErroredItem(item) {
        return this.renderItem(item, {
            isFailed: true,
            deleteFile: this.props.deleteFile,
            onAfterDelete: this.handleAfterDelete.bind(this),
        });
    }

    renderItem(item, customAttrs) {
        const { adminUiConfig, parentInfo, contentCreatePermissionsConfig, contentTypesMap, currentLanguage } = this.props;
        const attrs = {
            item,
            key: item.id,
            adminUiConfig,
            parentInfo,
            contentCreatePermissionsConfig,
            contentTypesMap,
            currentLanguage,
            ...customAttrs,
        };

        return <UploadItemComponent {...attrs} />;
    }

    render() {
        const { itemsToUpload } = this.props;
        const { items, erroredItems } = this.state;
        const uploadListClassName = createCssClassNames({
            'c-upload-list': true,
        });

        return (
            <div className={uploadListClassName}>
                <div className="c-upload-list__items">
                    {itemsToUpload.map(this.renderItemToUpload.bind(this))}
                    {erroredItems.map(this.renderErroredItem.bind(this))}
                    {items.map(this.renderUploadedItem.bind(this))}
                </div>
            </div>
        );
    }
}

UploadListComponent.propTypes = {
    itemsToUpload: PropTypes.arrayOf(PropTypes.object),
    onAfterUpload: PropTypes.func.isRequired,
    createFileStruct: PropTypes.func.isRequired,
    publishFile: PropTypes.func.isRequired,
    deleteFile: PropTypes.func.isRequired,
    checkCanUpload: PropTypes.func.isRequired,
    adminUiConfig: PropTypes.shape({
        multiFileUpload: PropTypes.shape({
            defaultMappings: PropTypes.arrayOf(PropTypes.object).isRequired,
            fallbackContentType: PropTypes.object.isRequired,
            locationMappings: PropTypes.arrayOf(PropTypes.object).isRequired,
            maxFileSize: PropTypes.number.isRequired,
        }).isRequired,
        token: PropTypes.string.isRequired,
        siteaccess: PropTypes.string.isRequired,
    }).isRequired,
    parentInfo: PropTypes.shape({
        contentTypeIdentifier: PropTypes.string.isRequired,
        contentTypeId: PropTypes.number.isRequired,
        locationPath: PropTypes.string.isRequired,
        language: PropTypes.string.isRequired,
    }).isRequired,
    contentCreatePermissionsConfig: PropTypes.object.isRequired,
    contentTypesMap: PropTypes.object.isRequired,
    currentLanguage: PropTypes.string,
    removeItemsToUpload: PropTypes.func.isRequired,
    onAfterDelete: PropTypes.func,
};

UploadListComponent.defaultProps = {
    itemsToUpload: [],
    currentLanguage: '',
    onAfterDelete: () => {},
};
