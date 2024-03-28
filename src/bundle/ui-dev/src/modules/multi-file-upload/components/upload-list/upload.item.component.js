import React, { Component } from 'react';
import PropTypes from 'prop-types';

import { getContentTypeIconUrl } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/content.type.helper';
import { getTranslator, isExternalInstance } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

import { createCssClassNames } from '../../../common/helpers/css.class.names';
import Icon from '../../../common/icon/icon';
import { fileSizeToString } from '../../helpers/text.helper';
import ProgressBarComponent from '../progress-bar/progress.bar.component';

export default class UploadItemComponent extends Component {
    constructor(props) {
        super(props);

        this.isExternalInstance = isExternalInstance();
        this.handleFileValidationError = this.handleFileValidationError.bind(this);
        this.handleEditBtnClick = this.handleEditBtnClick.bind(this);
        this.handleUploadAbort = this.handleUploadAbort.bind(this);
        this.handleUploadError = this.handleUploadError.bind(this);
        this.handleUploadLoad = this.handleUploadLoad.bind(this);
        this.handleUploadProgress = this.handleUploadProgress.bind(this);
        this.handleUploadEnd = this.handleUploadEnd.bind(this);
        this.handleLoadStart = this.handleLoadStart.bind(this);
        this.handleFileDeleted = this.handleFileDeleted.bind(this);
        this.abortUploading = this.abortUploading.bind(this);
        this.initPublishFile = this.initPublishFile.bind(this);
        this.deleteFile = this.deleteFile.bind(this);
        this.handleContentError = this.handleContentError.bind(this);
        this.handleFileDeletedError = this.handleFileDeletedError.bind(this);
        this.contentInfoInput = null;
        this.contentVersionInfoInput = null;
        this.contentVersionNoInput = null;
        this.contentEditBtn = null;
        this.state = {
            uploading: false,
            uploaded: props.isUploaded,
            aborted: false,
            failed: props.isFailed,
            deleted: false,
            progress: 0,
            xhr: null,
            struct: props.item.struct ?? null,
            totalSize: fileSizeToString(props.item.file.size),
            uploadedSize: '0',
            errorMsgs: props.item.errorMsgs ?? [],
            hasMultipleErrorsExpanded: false,
        };
    }

    componentDidMount() {
        const {
            item,
            adminUiConfig,
            parentInfo,
            createFileStruct,
            isUploaded,
            isFailed,
            checkCanUpload,
            contentCreatePermissionsConfig,
            currentLanguage,
        } = this.props;

        this.contentInfoInput = window.document.querySelector('#form_subitems_content_edit_content_info');
        this.contentVersionInfoInput = window.document.querySelector('#form_subitems_content_edit_version_info_content_info');
        this.contentVersionNoInput = window.document.querySelector('#form_subitems_content_edit_version_info_version_no');
        this.contentEditBtn = window.document.querySelector('#form_subitems_content_edit_create');

        if (isUploaded || isFailed) {
            return;
        }

        const config = {
            ...adminUiConfig.multiFileUpload,
            contentCreatePermissionsConfig,
        };

        if (!checkCanUpload(item.file, parentInfo, config, this.handleFileValidationError)) {
            this.setState(() => ({
                uploading: false,
                uploaded: false,
                aborted: false,
                failed: true,
            }));

            return;
        }

        const createFileStructParams = {
            parentInfo,
            config: adminUiConfig,
            languageCode: currentLanguage,
        };

        createFileStruct(item.file, createFileStructParams, this.handleContentError).then(this.initPublishFile);
    }

    initPublishFile(struct) {
        this.props.publishFile(
            struct,
            {
                upload: {
                    onabort: this.handleUploadAbort,
                    onerror: this.handleUploadError,
                    onload: this.handleUploadLoad,
                    onprogress: this.handleUploadProgress,
                },
                onloadstart: this.handleLoadStart,
                onerror: this.handleUploadError,
            },
            this.handleUploadEnd,
            this.handleContentError,
        );
    }

    handleFileDeletedError(errorMsg) {
        this.setState((prevState) => ({
            failed: true,
            deleted: false,
            errorMsgs: [...prevState.errorMsgs, errorMsg],
        }));
    }

    handleContentError(errorMsg) {
        this.setState(
            (prevState) => ({
                failed: true,
                errorMsgs: [...prevState.errorMsgs, errorMsg],
            }),
            () => this.props.onCreateError({ ...this.props.item, errorMsgs: this.state.errorMsgs }),
        );
    }

    handleFileValidationError(errorMsgs) {
        this.setState(
            () => ({
                uploading: false,
                uploaded: false,
                aborted: false,
                failed: true,
                errorMsgs,
            }),
            () => this.props.onCreateError({ ...this.props.item, errorMsgs: this.state.errorMsgs }),
        );
    }

    handleLoadStart(event) {
        this.setState(() => ({
            uploading: true,
            uploaded: false,
            aborted: false,
            failed: false,
            xhr: event.target,
        }));
    }

    handleUploadAbort() {
        this.setState(() => ({
            uploading: false,
            uploaded: false,
            aborted: true,
            failed: false,
        }));
    }

    handleUploadError() {
        this.setState((state) => ({
            uploading: false,
            uploaded: false,
            aborted: state.aborted,
            failed: true,
        }));
    }

    handleUploadLoad() {
        this.setState(() => ({
            uploading: false,
            uploaded: true,
            aborted: false,
            failed: false,
        }));
    }

    handleUploadProgress(event) {
        const fraction = event.loaded / event.total;
        const progress = parseInt(fraction * 100, 10);
        this.setState(() => ({
            uploadedSize: fileSizeToString(fraction * parseInt(this.props.item.file.size, 10)),
            uploading: true,
            uploaded: false,
            aborted: false,
            failed: false,
            progress,
        }));
    }

    handleUploadEnd() {
        this.setState(
            (state) => {
                const struct = JSON.parse(state.xhr.response);

                return {
                    struct,
                    uploading: false,
                    uploaded: true,
                    aborted: false,
                    failed: false,
                };
            },
            () => {
                const { item } = this.props;

                this.props.onAfterUpload({ ...item, struct: this.state.struct });
            },
        );
    }

    abortUploading() {
        this.state.xhr.abort();
        this.props.onAfterAbort(this.props.item);
    }

    deleteFile() {
        const { failed } = this.state;
        const { item } = this.props;

        if (failed) {
            this.props.removeItemsToUpload([item]);
            this.handleFileDeleted(item);
        } else {
            this.props.deleteFile(this.state.struct, this.handleFileDeleted, this.handleFileDeletedError);
        }
    }

    handleFileDeleted() {
        this.props.onAfterDelete(this.props.item);
        this.setState({ deleted: true });
    }

    getContentTypeIdentifier() {
        const { contentTypesMap, item } = this.props;

        if (!item.struct?.Content) {
            return null;
        }

        const contentTypeHref = item.struct.Content.ContentType._href;
        const contentType = contentTypesMap ? contentTypesMap[contentTypeHref] : null;
        const contentTypeIdentifier = contentType ? contentType.identifier : null;

        return contentTypeIdentifier;
    }

    renderIcon() {
        const { failed } = this.state;
        const contentTypeIdentifier = this.getContentTypeIdentifier();

        if (!contentTypeIdentifier || failed) {
            return null;
        }

        const contentTypeIconUrl = getContentTypeIconUrl(contentTypeIdentifier);
        const [, iconName] = contentTypeIconUrl.split('#');

        if (this.isExternalInstance) {
            return <Icon name={iconName} extraClasses="ibexa-icon--small" defaultIconName="file" />;
        }

        return <Icon customPath={contentTypeIconUrl} extraClasses="ibexa-icon--small" />;
    }

    renderProgressBar() {
        const { uploaded, aborted, progress, totalSize, uploadedSize, failed } = this.state;

        if (uploaded || aborted || failed) {
            return null;
        }

        return <ProgressBarComponent progress={progress} uploaded={uploadedSize} total={totalSize} />;
    }

    renderErrorInfo() {
        const { failed, errorMsgs } = this.state;

        if (!failed) {
            return null;
        }

        const Translator = getTranslator();
        const hasMultipleErrors = errorMsgs.length > 1;
        const label = hasMultipleErrors
            ? Translator.trans(/*@Desc("Failed to upload ")*/ 'multierror.label', {}, 'ibexa_multi_file_upload')
            : errorMsgs[0];

        return (
            <div className="c-upload-list-item__message c-upload-list-item__message--error">
                <Icon name="warning" extraClasses="ibexa-icon--tiny-small" />
                {label}
                {hasMultipleErrors && (
                    <button
                        type="button"
                        className="c-upload-list-item__multiple-errors-toggle-btn"
                        onClick={() =>
                            this.setState((prevState) => ({
                                hasMultipleErrorsExpanded: !prevState.hasMultipleErrorsExpanded,
                            }))
                        }
                    >
                        <Icon name="caret-down" extraClasses="ibexa-icon--tiny-small" />
                    </button>
                )}
            </div>
        );
    }

    renderSuccessMessage() {
        const Translator = getTranslator();
        const { uploaded } = this.state;

        if (!uploaded) {
            return;
        }

        const message = Translator.trans(/*@Desc("100% Uploaded")*/ 'upload.success.message', {}, 'ibexa_multi_file_upload');

        return (
            <div className="c-upload-list-item__message c-upload-list-item__message--success">
                <Icon name="checkmark" extraClasses="ibexa-icon--tiny-small" />
                {message}
            </div>
        );
    }

    renderAbortBtn() {
        const Translator = getTranslator();
        const { uploading } = this.state;

        if (!uploading) {
            return null;
        }

        const label = Translator.trans(/*@Desc("Abort")*/ 'abort.label', {}, 'ibexa_multi_file_upload');

        return (
            <div
                className="btn ibexa-btn ibexa-btn--ghost ibexa-btn--no-text ibexa-btn--small c-upload-list-item__action c-upload-list-item__action--abort"
                onClick={this.abortUploading}
                title={label}
                tabIndex="-1"
            >
                <Icon name="trash" extraClasses="ibexa-icon--small" />
            </div>
        );
    }

    handleEditBtnClick(event) {
        event.preventDefault();

        const { struct } = this.state;
        const content = struct.Content;
        const contentId = content._id;
        const { languageCode } = content.CurrentVersion.Version.VersionInfo.VersionTranslationInfo.Language['0'];
        const { versionNo } = content.CurrentVersion.Version.VersionInfo;

        this.contentInfoInput.value = contentId;
        this.contentVersionInfoInput.value = contentId;
        this.contentVersionNoInput.value = versionNo;

        window.document.querySelector(`#form_subitems_content_edit_language_${languageCode}`).checked = true;

        this.contentEditBtn.click();
    }

    renderEditBtn() {
        const Translator = getTranslator();
        const { uploaded, failed } = this.state;
        const canEdit = uploaded && !failed;

        if (!canEdit || this.isExternalInstance) {
            return null;
        }

        const label = Translator.trans(/*@Desc("Edit")*/ 'edit.label', {}, 'ibexa_multi_file_upload');

        return (
            <div
                className="btn ibexa-btn ibexa-btn--ghost ibexa-btn--no-text ibexa-btn--small c-upload-list-item__action c-upload-list-item__action--edit"
                title={label}
                onClick={this.handleEditBtnClick}
                tabIndex="-1"
            >
                <Icon name="edit" extraClasses="ibexa-icon--small" />
            </div>
        );
    }

    renderDeleteBtn() {
        const { uploaded, aborted, failed } = this.state;
        const canDelete = (uploaded && !aborted) || failed;

        if (!canDelete) {
            return null;
        }

        const Translator = getTranslator();
        const label = Translator.trans(/*@Desc("Delete")*/ 'delete.label', {}, 'ibexa_multi_file_upload');

        return (
            <div
                className="btn ibexa-btn ibexa-btn--ghost ibexa-btn--no-text ibexa-btn--small c-upload-list-item__action c-upload-list-item__action--delete"
                onClick={this.deleteFile}
                title={label}
                tabIndex="-1"
            >
                <Icon name="trash" extraClasses="ibexa-icon--small" />
            </div>
        );
    }

    render() {
        const { failed, deleted, totalSize, errorMsgs, hasMultipleErrorsExpanded } = this.state;
        const hasMultipleErrors = errorMsgs.length > 1;
        const wrapperClassName = createCssClassNames({
            'c-upload-list-item': true,
            'c-upload-list-item--errored': failed,
            'c-upload-list-item--expanded-multiple-errors': hasMultipleErrorsExpanded,
        });

        if (deleted) {
            return null;
        }

        return (
            <div className={wrapperClassName}>
                <div className="c-upload-list-item__icon-wrapper">{this.renderIcon()}</div>
                <div className="c-upload-list-item__meta">
                    <div className="c-upload-list-item__name">{this.props.item.file.name}</div>
                    <div className="c-upload-list-item__size">{totalSize}</div>
                </div>
                <div className="c-upload-list-item__info">
                    {this.renderErrorInfo()}
                    {this.renderSuccessMessage()}
                    {this.renderProgressBar()}
                </div>
                <div className="c-upload-list-item__actions">
                    {this.renderAbortBtn()}
                    {this.renderEditBtn()}
                    {this.renderDeleteBtn()}
                </div>
                {hasMultipleErrors && (
                    <ul className="c-upload-list-item__multiple-errors-list">
                        {errorMsgs.map((errorMsg) => (
                            <li key={errorMsg.replace(/\s/g, '-')} className="c-upload-list-item__multiple-errors-item">
                                {errorMsg}
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        );
    }
}

UploadItemComponent.propTypes = {
    item: PropTypes.object.isRequired,
    onAfterUpload: PropTypes.func.isRequired,
    onAfterAbort: PropTypes.func.isRequired,
    onAfterDelete: PropTypes.func.isRequired,
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
        locationPath: PropTypes.string.isRequired,
        language: PropTypes.string.isRequired,
    }).isRequired,
    contentCreatePermissionsConfig: PropTypes.object,
    contentTypesMap: PropTypes.object.isRequired,
    currentLanguage: PropTypes.string,
    isUploaded: PropTypes.bool,
    isFailed: PropTypes.bool,
    removeItemsToUpload: PropTypes.func,
    onCreateError: PropTypes.func,
    errorMsgs: PropTypes.array,
};

UploadItemComponent.defaultProps = {
    isUploaded: false,
    isFailed: false,
    currentLanguage: '',
    contentCreatePermissionsConfig: {},
    removeItemsToUpload: () => {},
    onCreateError: () => {},
    errorMsgs: [],
};
