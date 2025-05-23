import React from 'react';
import PropTypes from 'prop-types';

import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

const ProgressBarComponent = (props) => {
    const Translator = getTranslator();
    const message = Translator.trans(/* @Desc("Uploading...") */ 'upload.progress_bar.uploading', {}, 'ibexa_multi_file_upload');

    return (
        <div className="c-progress-bar">
            <div className="c-progress-bar__value" style={{ '--progress': `${props.progress}%` }} />
            <div className="c-progress-bar__label">
                {props.progress}%{message}
            </div>
        </div>
    );
};

ProgressBarComponent.propTypes = {
    progress: PropTypes.number.isRequired,
};

export default ProgressBarComponent;
