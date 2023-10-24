import React from 'react';
import PropTypes from 'prop-types';

const { Translator } = window;

const PaginationInfo = ({ totalCount, viewingCount }) => {
    if (totalCount === 0) {
        return null;
    }

    const message = Translator.trans(
        /*@Desc("Viewing %viewingCount% out of %totalCount% items")*/ 'viewing_message',
        {
            viewingCount,
            totalCount,
        },
        'ibexa_sub_items',
    );

    return <div className="m-sub-items__pagination-info ibexa-pagination__info" dangerouslySetInnerHTML={{ __html: message }} />;
};

PaginationInfo.propTypes = {
    totalCount: PropTypes.number.isRequired,
    viewingCount: PropTypes.number.isRequired,
};

export default PaginationInfo;
