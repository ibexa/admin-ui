import React, { Component } from 'react';
import PropTypes from 'prop-types';

import { getTranslator, getRootDOMElement } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { parse as parseTooltips } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/tooltips.helper';
import { getContentTypeName } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/content.type.helper';

import TooltipPopup from '../../../common/tooltip-popup/tooltip.popup.component';
import DropAreaComponent from '../drop-area/drop.area.component';
import UploadListComponent from '../upload-list/upload.list.component';

const CLASS_SCROLL_DISABLED = 'ibexa-scroll-disabled';

export default class UploadPopupModule extends Component {
    constructor(props) {
        super(props);

        this.refTooltip = React.createRef();
        this.rootDOMElement = getRootDOMElement();
    }

    componentDidMount() {
        this.rootDOMElement.classList.add(CLASS_SCROLL_DISABLED);
        parseTooltips(this.refTooltip.current);
    }

    componentWillUnmount() {
        window.document.body.classList.remove(CLASS_SCROLL_DISABLED);
    }

    getContentTypesMaxFileSize() {
        const { locationMappings, defaultMappings, maxFileSize: defaultMaxFileSize } = this.props.adminUiConfig.multiFileUpload;
        const mappings = locationMappings.length ? locationMappings : defaultMappings;
        const contentTypeIdentifiers = Object.keys(this.props.contentCreatePermissionsConfig);

        return contentTypeIdentifiers.reduce((maxFileSizes, contentTypeIdentifier) => {
            const contentTypeName = getContentTypeName(contentTypeIdentifier);
            const contentTypeMapping = mappings.find((item) => item.contentTypeIdentifier === contentTypeIdentifier);

            maxFileSizes.push({
                name: contentTypeName,
                maxFileSize: contentTypeMapping?.maxFileSize || defaultMaxFileSize,
            });

            return maxFileSizes;
        }, []);
    }

    render() {
        const Translator = getTranslator();
        const hasAnyUploadedItems = !!this.props.uploadedItems.length;
        const hasAnyItemsToUpload = !!this.props.itemsToUpload.length;
        const label = Translator.trans(/*@Desc("Upload")*/ 'upload_popup.label', {}, 'ibexa_multi_file_upload');
        const confirmBtnLabel =
            !hasAnyUploadedItems && !hasAnyItemsToUpload
                ? Translator.trans(/*@Desc("Close")*/ 'upload_popup.close_label', {}, 'ibexa_multi_file_upload')
                : Translator.trans(/*@Desc("Confirm and close")*/ 'upload_popup.confirm_label', {}, 'ibexa_multi_file_upload');
        const closeBtnLabel = Translator.trans(
            /*@Desc("Cancel pending upload")*/ 'upload_popup.cancel_label',
            {},
            'ibexa_multi_file_upload',
        );
        const {
            addItemsToUpload,
            subtitle,
            visible,
            onConfirm,
            onClose,
            onAfterUpload,
            createFileStruct,
            publishFile,
            deleteFile,
            checkCanUpload,
            adminUiConfig,
            parentInfo,
            contentCreatePermissionsConfig,
            contentTypesMap,
            currentLanguage,
            onAfterDelete,
            itemsToUpload,
            removeItemsToUpload,
            preventDefaultAction,
            processUploadedFiles,
            enableUploadedItemEdit,
        } = this.props;
        const tooltipAttrs = {
            subtitle,
            visible,
            onConfirm,
            onClose,
            title: Translator.trans(/*@Desc("Multi-file upload")*/ 'upload_popup.title', {}, 'ibexa_multi_file_upload'),
            confirmLabel: confirmBtnLabel,
            closeLabel: closeBtnLabel,
            confirmBtnAttrs: {
                disabled: itemsToUpload.length,
            },
            closeBtnAttrs: {
                disabled: !itemsToUpload.length,
            },
            ...((hasAnyUploadedItems || hasAnyItemsToUpload) && { extraClasses: 'c-tooltip-popup--with-uploaded-items' }),
        };
        const listAttrs = {
            onAfterUpload,
            createFileStruct,
            publishFile,
            deleteFile,
            checkCanUpload,
            adminUiConfig,
            parentInfo,
            contentCreatePermissionsConfig,
            contentTypesMap,
            currentLanguage,
            onAfterDelete,
            itemsToUpload,
            removeItemsToUpload,
            enableUploadedItemEdit,
        };

        return (
            <div className="c-upload-popup" ref={this.refTooltip}>
                <TooltipPopup {...tooltipAttrs}>
                    <div className="c-upload-popup__label">{label}</div>
                    <DropAreaComponent
                        addItemsToUpload={addItemsToUpload}
                        maxFileSizes={this.getContentTypesMaxFileSize()}
                        preventDefaultAction={preventDefaultAction}
                        processUploadedFiles={processUploadedFiles}
                    />
                    <UploadListComponent {...listAttrs} />
                </TooltipPopup>
            </div>
        );
    }
}

UploadPopupModule.propTypes = {
    subtitle: PropTypes.string,
    visible: PropTypes.bool,
    itemsToUpload: PropTypes.array,
    uploadedItems: PropTypes.array,
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
    }).isRequired,
    parentInfo: PropTypes.shape({
        contentTypeIdentifier: PropTypes.string.isRequired,
        locationPath: PropTypes.string.isRequired,
        language: PropTypes.string.isRequired,
    }).isRequired,
    preventDefaultAction: PropTypes.func.isRequired,
    processUploadedFiles: PropTypes.func.isRequired,
    contentTypesMap: PropTypes.object.isRequired,
    currentLanguage: PropTypes.string,
    addItemsToUpload: PropTypes.func.isRequired,
    removeItemsToUpload: PropTypes.func.isRequired,
    contentCreatePermissionsConfig: PropTypes.object,
    onConfirm: PropTypes.func,
    onClose: PropTypes.func,
    onAfterDelete: PropTypes.func,
    enableUploadedItemEdit: PropTypes.bool,
};

UploadPopupModule.defaultProps = {
    subtitle: '',
    visible: true,
    itemsToUpload: [],
    uploadedItems: [],
    currentLanguage: '',
    contentCreatePermissionsConfig: {},
    onConfirm: () => {},
    onClose: () => {},
    onAfterDelete: () => {},
    enableUploadedItemEdit: true,
};
