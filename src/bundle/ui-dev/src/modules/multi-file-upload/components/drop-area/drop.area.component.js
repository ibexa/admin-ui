import React, { Component, createRef } from 'react';
import PropTypes from 'prop-types';

import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { fileSizeToString } from '../../../common/helpers/text.helper';
import { createCssClassNames } from '../../../common/helpers/css.class.names';
import Icon from '../../../common/icon/icon';
export default class DropAreaComponent extends Component {
    constructor(props) {
        super(props);

        this._refFileInput = null;
        this._refForm = createRef();
        this.hasMultiMsgForFileSizes = this.props.maxFileSizes.length > 1;

        this.state = {
            filesSizeExpanded: false,
        };

        this.openFileSelector = this.openFileSelector.bind(this);
        this.handleUpload = this.handleUpload.bind(this);
        this.renderMaxFileMsgsToggler = this.renderMaxFileMsgsToggler.bind(this);
        this.renderMaxFileSizeItems = this.renderMaxFileSizeItems.bind(this);
    }

    /**
     * Opens a browser native file selector
     *
     * @method openFileSelector
     * @param {Event} event
     * @memberof DropAreaComponent
     */
    openFileSelector(event) {
        event.preventDefault();

        this._refFileInput.click();
    }

    /**
     * Handles file upload
     *
     * @method handleUpload
     * @param {Event} event
     * @memberof DropAreaComponent
     */
    handleUpload(event) {
        this.props.preventDefaultAction(event);
        this.props.addItemsToUpload(this.props.processUploadedFiles(event));

        event.currentTarget.value = null;
    }

    renderMaxFileMsgsToggler() {
        const Translator = getTranslator();

        if (!this.hasMultiMsgForFileSizes) {
            return null;
        }

        return (
            <li className="c-drop-area__max-file-size-item">
                <Icon name="about-info" extraClasses="c-drop-area__max-file-size-icon ibexa-icon--small" />
                {Translator.trans(/* @Desc("Max. file size") */ 'max_file_size.message.general', {}, 'ibexa_multi_file_upload')}
                <button
                    type="button"
                    className="c-drop-area__max-file-size-toggle-btn"
                    onClick={() =>
                        this.setState((prevState) => ({
                            filesSizeExpanded: !prevState.filesSizeExpanded,
                        }))
                    }
                />
            </li>
        );
    }

    renderMaxFileSizeItems() {
        const Translator = getTranslator();

        return (
            <>
                {this.props.maxFileSizes.map((contentType) => {
                    const itemKey = contentType.name.replace(/\s/g, '-');

                    return (
                        <li key={itemKey} className="c-drop-area__max-file-size-item">
                            {!this.hasMultiMsgForFileSizes && (
                                <Icon name="about-info" extraClasses="c-drop-area__max-file-size-icon ibexa-icon--small" />
                            )}
                            {Translator.trans(
                                /* @Desc("%contentTypeName% max file size: %maxFileSize%") */ 'max_file_size.message',
                                {
                                    contentTypeName: contentType.name,
                                    maxFileSize: fileSizeToString(contentType.maxFileSize),
                                },
                                'ibexa_multi_file_upload',
                            )}
                        </li>
                    );
                })}
            </>
        );
    }

    componentDidMount() {
        window.addEventListener('drop', this.props.preventDefaultAction, false);
        window.addEventListener('dragover', this.props.preventDefaultAction, false);

        this._refForm.current.addEventListener('drop', this.handleUpload, false);
    }

    componentWillUnmount() {
        window.removeEventListener('drop', this.props.preventDefaultAction, false);
        window.removeEventListener('dragover', this.props.preventDefaultAction, false);
    }

    render() {
        const Translator = getTranslator();
        const dropActionMessage = Translator.trans(/* @Desc("Drag and drop file") */ 'drop_action.message', {}, 'ibexa_multi_file_upload');
        const separatorMessage = Translator.trans(/* @Desc("or") */ 'drop_action.separator', {}, 'ibexa_multi_file_upload');
        const uploadBtnLabel = Translator.trans(/* @Desc("Upload file") */ 'upload_btn.label', {}, 'ibexa_multi_file_upload');
        const maxFilesSizeListClassNames = createCssClassNames({
            'c-drop-area__max-files-size': true,
            'c-drop-area__max-files-size--expanded': this.state.filesSizeExpanded,
        });

        return (
            <form className="c-drop-area" onDrop={this.handleUpload} ref={this._refForm}>
                <div className="c-drop-area__message c-drop-area__message--main">{dropActionMessage}</div>
                <div className="c-drop-area__message c-drop-area__message--separator">{separatorMessage}</div>
                <button
                    type="button"
                    className="btn ibexa-btn ibexa-btn--secondary c-drop-area__btn-select"
                    onClick={this.openFileSelector}
                    tabIndex="-1"
                >
                    {uploadBtnLabel}
                </button>
                <div className="c-drop-area__message c-drop-area__message--filesize">
                    <ul className={maxFilesSizeListClassNames}>
                        {this.renderMaxFileMsgsToggler()}
                        {this.renderMaxFileSizeItems()}
                    </ul>
                </div>
                <input
                    className="c-drop-area__input--hidden"
                    ref={(ref) => (this._refFileInput = ref)}
                    id="mfu-files"
                    type="file"
                    name="files[]"
                    hidden={true}
                    multiple={true}
                    onChange={this.handleUpload}
                />
            </form>
        );
    }
}

DropAreaComponent.propTypes = {
    maxFileSizes: PropTypes.arrayOf(PropTypes.object).isRequired,
    processUploadedFiles: PropTypes.func.isRequired,
    preventDefaultAction: PropTypes.func.isRequired,
    addItemsToUpload: PropTypes.func.isRequired,
};
