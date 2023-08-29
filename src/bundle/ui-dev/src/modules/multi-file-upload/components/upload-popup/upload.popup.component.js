import React, { Component } from 'react';
import PropTypes from 'prop-types';

import TooltipPopup from '../../../common/tooltip-popup/tooltip.popup.component';
import DropAreaComponent from '../drop-area/drop.area.component';
import UploadListComponent from '../upload-list/upload.list.component';

const { Translator } = window;

const CLASS_SCROLL_DISABLED = 'ibexa-scroll-disabled';

export default class UploadPopupModule extends Component {
    constructor(props) {
        super(props);

        this.refTooltip = React.createRef();
    }

    componentDidMount() {
        window.document.body.classList.add(CLASS_SCROLL_DISABLED);
        window.ibexa.helpers.tooltips.parse(this.refTooltip.current);
    }

    componentWillUnmount() {
        window.document.body.classList.remove(CLASS_SCROLL_DISABLED);
    }

    render() {
        const tooltipAttrs = this.props;
        const listAttrs = {
            ...tooltipAttrs,
            itemsToUpload: this.props.itemsToUpload,
            removeItemsToUpload: this.props.removeItemsToUpload,
        };
        const title = Translator.trans(/*@Desc("Multi-file upload")*/ 'upload_popup.close', {}, 'ibexa_multi_file_upload');

        return (
            <div className="c-upload-popup" ref={this.refTooltip}>
                <TooltipPopup title={title} showFooter={false} {...tooltipAttrs}>
                    <DropAreaComponent
                        addItemsToUpload={this.props.addItemsToUpload}
                        maxFileSize={this.props.adminUiConfig.multiFileUpload.maxFileSize}
                        preventDefaultAction={this.props.preventDefaultAction}
                        processUploadedFiles={this.props.processUploadedFiles}
                    />
                    <UploadListComponent {...listAttrs} />
                </TooltipPopup>
            </div>
        );
    }
}

UploadPopupModule.propTypes = {
    popupTitle: PropTypes.string.isRequired,
    visible: PropTypes.bool,
    itemsToUpload: PropTypes.array,
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
    preventDefaultAction: PropTypes.func.isRequired,
    processUploadedFiles: PropTypes.func.isRequired,
    contentTypesMap: PropTypes.object.isRequired,
    currentLanguage: PropTypes.string,
    addItemsToUpload: PropTypes.func.isRequired,
    removeItemsToUpload: PropTypes.func.isRequired,
};

UploadPopupModule.defaultProps = {
    visible: true,
    itemsToUpload: [],
    currentLanguage: '',
};
