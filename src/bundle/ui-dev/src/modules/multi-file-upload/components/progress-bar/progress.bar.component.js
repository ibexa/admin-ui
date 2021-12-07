import React from 'react';
import PropTypes from 'prop-types';

const ProgressBarComponent = (props) => {
    const message = Translator.trans(/*@Desc("Uploading...")*/ 'upload.progress_bar.uploading', {}, 'multi_file_upload');

    return (
        <div className="c-progress-bar">
            <div className="c-progress-bar__value" style={{ '--progress': `${props.progress}%` }} />
            <div className="c-progress-bar__label">
                {props.progress}% {message}
            </div>
        </div>
    );
};

ProgressBarComponent.propTypes = {
    progress: PropTypes.number.isRequired,
    uploaded: PropTypes.string.isRequired,
    total: PropTypes.string.isRequired,
};

export default ProgressBarComponent;
