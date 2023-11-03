import React from 'react';
import PropTypes from 'prop-types';
import { createCssClassNames } from '../helpers/css.class.names';

const PaginationInfo = ({ totalCount, viewingCount, extraClasses, Translator }) => {
    if (totalCount === 0) {
        return null;
    }

    const className = createCssClassNames({
        'ibexa-pagination__info': true,
        [extraClasses]: true,
    });
    const message = Translator.trans(
        /*@Desc("Viewing %viewingCount% out of %totalCount% items")*/ 'pagination.info.viewing_message',
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
    Translator: PropTypes.object,
};

PaginationInfo.defaultProps = {
    extraClasses: '',
    Translator: window.Translator
};

export default PaginationInfo;
