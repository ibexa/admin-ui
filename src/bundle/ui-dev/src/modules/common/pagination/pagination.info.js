import React from 'react';
import PropTypes from 'prop-types';
import { createCssClassNames } from '../helpers/css.class.names';

import { getTranslator } from '../../../../../Resources/public/js/scripts/helpers/context.helper';

const PaginationInfo = ({ totalCount, viewingCount, extraClasses = '' }) => {
    if (totalCount === 0) {
        return null;
    }
    const Translator = getTranslator();
    const className = createCssClassNames({
        'ibexa-pagination__info': true,
        [extraClasses]: true,
    });
    const message = Translator.trans(
        /* @Desc("Viewing %viewingCount% out of %totalCount% items") */ 'pagination.info.viewing_message',
        {
            viewingCount,
            totalCount,
        },
        'ibexa_universal_discovery_widget',
    );

    return <div className={className} dangerouslySetInnerHTML={{ __html: message }} />;
};

PaginationInfo.propTypes = {
    totalCount: PropTypes.number.isRequired,
    viewingCount: PropTypes.number.isRequired,
    extraClasses: PropTypes.string,
};

export default PaginationInfo;
