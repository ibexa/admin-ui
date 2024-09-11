import React, { Component } from 'react';
import { createPortal } from 'react-dom';
import PropTypes from 'prop-types';

import { getTranslator, getRootDOMElement } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

import UploadPopupComponent from './components/upload-popup/upload.popup.component';
import { createFileStruct, publishFile, deleteFile, checkCanUpload } from './services/multi.file.upload.service';
import Icon from '../common/icon/icon';
import { createCssClassNames } from '../common/helpers/css.class.names';

export const UDW_TRIGGER_ID = 'UDW';
export const SUBITEMS_TRIGGER_ID = 'SUBITEMS';

export default class MultiFileUploadModule extends Component {
    constructor(props) {
        super(props);

        let popupVisible = true;

        this.configRootNode = getRootDOMElement();
        this._itemsUploaded = [];

        if (!props.itemsToUpload || !props.itemsToUpload.length) {
            popupVisible = false;
        }

        this.handleDropOnWindow = this.handleDropOnWindow.bind(this);
        this.handleAfterUpload = this.handleAfterUpload.bind(this);
        this.handleAfterDelete = this.handleAfterDelete.bind(this);
        this.showUploadPopup = this.showUploadPopup.bind(this);
        this.hidePopup = this.hidePopup.bind(this);
        this.confirmPopup = this.confirmPopup.bind(this);
        this.processUploadedFiles = this.processUploadedFiles.bind(this);
        this.setUdwStateOpened = this.setUdwStateOpened.bind(this);
        this.setUdwStateClosed = this.setUdwStateClosed.bind(this);
        this.addItemsToUpload = this.addItemsToUpload.bind(this);
        this.removeItemsToUpload = this.removeItemsToUpload.bind(this);

        this.state = {
            udwOpened: false,
            popupVisible,
            itemsToUpload: props.itemsToUpload,
            allowDropOnWindow: true,
            uploadDisabled: Object.values(props.contentCreatePermissionsConfig).every((isEnabled) => !isEnabled),
        };
    }

    componentDidMount() {
        this.manageDropEvent();

        this.configRootNode.addEventListener('ibexa-udw-opened', this.setUdwStateOpened, false);
        this.configRootNode.addEventListener('ibexa-udw-closed', this.setUdwStateClosed, false);
    }

    componentDidUpdate() {
        this.manageDropEvent();
    }

    componentWillUnmount() {
        this.configRootNode.removeEventListener('ibexa-udw-opened', this.setUdwStateOpened, false);
        this.configRootNode.removeEventListener('ibexa-udw-closed', this.setUdwStateClosed, false);
    }

    setUdwStateOpened() {
        this.setState({ udwOpened: true });
    }

    setUdwStateClosed() {
        this.setState({ udwOpened: false });
    }

    manageDropEvent() {
        const { uploadDisabled, popupVisible, itemsToUpload } = this.state;

        if (!uploadDisabled && !popupVisible && !itemsToUpload.length) {
            this.configRootNode.addEventListener('drop', this.handleDropOnWindow, false);
            this.configRootNode.addEventListener('dragover', this.preventDefaultAction, false);
        }
    }

    hidePopup() {
        this.setState((state) => ({ ...state, popupVisible: false, allowDropOnWindow: true }));
        this.props.onPopupClose(this._itemsUploaded);
        this._itemsUploaded = [];
    }

    confirmPopup() {
        this.setState((state) => ({ ...state, popupVisible: false, allowDropOnWindow: true }));
        this.props.onPopupConfirm(this._itemsUploaded);

        if (this.props.triggerId === SUBITEMS_TRIGGER_ID && !!this._itemsUploaded.length) {
            window.location.reload();
        }

        this._itemsUploaded = [];
    }

    showUploadPopup() {
        this.setState((state) => ({ ...state, popupVisible: true, itemsToUpload: [], allowDropOnWindow: false }));
    }

    handleAfterUpload(itemsUploaded) {
        this._itemsUploaded = itemsUploaded;
    }

    handleAfterDelete(deletedItem) {
        this._itemsUploaded = this._itemsUploaded.filter((data) => data.id !== deletedItem.id);
    }

    handleDropOnWindow(event) {
        this.preventDefaultAction(event);
        event.stopImmediatePropagation();

        const itemsToUpload = this.processUploadedFiles(event);

        // Covers the case when dragging and dropping page elements inside the browser,
        // like links, images, etc.
        if (!this.state.allowDropOnWindow || !itemsToUpload.length || this.state.udwOpened) {
            return;
        }

        this.configRootNode.removeEventListener('drop', this.handleDropOnWindow, false);
        this.configRootNode.removeEventListener('dragover', this.preventDefaultAction, false);

        this.setState((state) => ({ ...state, itemsToUpload, popupVisible: true, allowDropOnWindow: false }));
    }

    extractDroppedFilesList(event) {
        let list;

        if (event.nativeEvent) {
            list = event.nativeEvent.dataTransfer || event.nativeEvent.target;
        } else {
            list = event.dataTransfer;
        }

        return list;
    }

    processUploadedFiles(event) {
        const list = this.extractDroppedFilesList(event);

        return Array.from(list.files).map((file) => ({
            id: Math.floor(Math.random() * Date.now()),
            file,
        }));
    }

    preventDefaultAction(event) {
        event.preventDefault();
        event.stopPropagation();
    }

    renderBtn() {
        if (!this.props.withUploadButton) {
            return null;
        }

        const Translator = getTranslator();
        const { uploadDisabled } = this.state;
        const label = Translator.trans(/*@Desc("Upload")*/ 'multi_file_upload_open_btn.label', {}, 'ibexa_multi_file_upload');
        const isTriggeredBySubitems = this.props.triggerId === SUBITEMS_TRIGGER_ID;
        const buttonClassName = createCssClassNames({
            'ibexa-btn btn': true,
            'ibexa-btn--secondary ibexa-btn--small': !isTriggeredBySubitems,
            'ibexa-btn--ghost': isTriggeredBySubitems,
        });
        return (
            <button type="button" className={buttonClassName} onClick={this.showUploadPopup} disabled={uploadDisabled}>
                <Icon name="upload" extraClasses="ibexa-icon--small" /> {label}
            </button>
        );
    }

    addItemsToUpload(items) {
        this.setState((prevState) => {
            const newItems = items.filter((item) => !prevState.itemsToUpload.find((stateItem) => stateItem.id === item.id));

            if (newItems.length) {
                return {
                    itemsToUpload: [...prevState.itemsToUpload, ...newItems],
                };
            }
        });
    }

    removeItemsToUpload(items) {
        const itemsIds = items.map((item) => item.id);

        this.setState((prevState) => {
            const itemsToUpload = prevState.itemsToUpload.filter((stateItem) => !itemsIds.includes(stateItem.id));

            if (itemsToUpload.length !== prevState.itemsToUpload.length) {
                return {
                    itemsToUpload,
                };
            }
        });
    }

    renderPopup() {
        if (!this.state.popupVisible) {
            return null;
        }

        const Translator = getTranslator();
        const subtitle = Translator.trans(
            /*@Desc("Under %name%")*/ 'multi_file_upload_popup.subtitle',
            { name: this.props.parentInfo.name },
            'ibexa_multi_file_upload',
        );

        const attrs = {
            ...this.props,
            subtitle: this.props.parentInfo.name ? subtitle : '',
            visible: true,
            onClose: this.hidePopup,
            onConfirm: this.confirmPopup,
            itemsToUpload: this.state.itemsToUpload,
            uploadedItems: this._itemsUploaded,
            onAfterUpload: this.handleAfterUpload,
            onAfterDelete: this.handleAfterDelete,
            preventDefaultAction: this.preventDefaultAction,
            processUploadedFiles: this.processUploadedFiles,
            addItemsToUpload: this.addItemsToUpload,
            removeItemsToUpload: this.removeItemsToUpload,
            contentCreatePermissionsConfig: this.props.contentCreatePermissionsConfig,
            enableUploadedItemEdit: this.props.triggerId === SUBITEMS_TRIGGER_ID,
        };

        return createPortal(<UploadPopupComponent {...attrs} />, this.configRootNode);
    }

    render() {
        return (
            <div className="m-mfu">
                {this.renderBtn()}
                {this.renderPopup()}
            </div>
        );
    }
}

MultiFileUploadModule.propTypes = {
    adminUiConfig: PropTypes.shape({
        multiFileUpload: PropTypes.shape({
            defaultMappings: PropTypes.arrayOf(PropTypes.object).isRequired,
            fallbackContentType: PropTypes.object.isRequired,
            locationMappings: PropTypes.arrayOf(PropTypes.object).isRequired,
            maxFileSize: PropTypes.number.isRequired,
        }).isRequired,
    }).isRequired,
    parentInfo: PropTypes.shape({
        contentTypeIdentifier: PropTypes.string.isRequired,
        locationPath: PropTypes.string.isRequired,
        language: PropTypes.string.isRequired,
        name: PropTypes.string.isRequired,
    }).isRequired,
    checkCanUpload: PropTypes.func,
    createFileStruct: PropTypes.func,
    deleteFile: PropTypes.func,
    onPopupClose: PropTypes.func,
    onPopupConfirm: PropTypes.func,
    publishFile: PropTypes.func,
    itemsToUpload: PropTypes.array,
    withUploadButton: PropTypes.bool,
    contentCreatePermissionsConfig: PropTypes.object,
    contentTypesMap: PropTypes.object.isRequired,
    currentLanguage: PropTypes.string,
    triggerId: PropTypes.string,
};

MultiFileUploadModule.defaultProps = {
    checkCanUpload,
    createFileStruct,
    deleteFile,
    onPopupClose: () => {},
    onPopupConfirm: () => {},
    publishFile,
    itemsToUpload: [],
    withUploadButton: true,
    currentLanguage: '',
    contentCreatePermissionsConfig: {},
    triggerId: SUBITEMS_TRIGGER_ID,
};
