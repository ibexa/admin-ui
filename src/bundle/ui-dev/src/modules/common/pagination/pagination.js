import React from 'react';
import PropTypes from 'prop-types';

import { computePages as computePagesPagination } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/pagination.helper';
import PaginationButton from './pagination.button';

const DOTS = '...';

const Pagination = ({ totalCount, itemsPerPage, proximity, activePageIndex, onPageChange, disabled: paginationDisabled }) => {
    const pagesCount = Math.ceil(totalCount / itemsPerPage);

    if (pagesCount <= 1) {
        return null;
    }

    const previousPage = activePageIndex - 1;
    const nextPage = activePageIndex + 1;
    const isFirstPage = activePageIndex === 0;
    const isLastPage = activePageIndex + 1 === pagesCount;
    const pages = computePagesPagination({ proximity, activePageIndex, pagesCount, separator: DOTS });
    const paginationButtons = pages.map((page, index) => {
        if (page === DOTS) {
            const key = `dots-${index}`;

            return <PaginationButton key={key} label={DOTS} disabled={true} />;
        }

        const isCurrentPage = page === activePageIndex + 1;
        const additionalClasses = isCurrentPage ? 'active' : '';
        const label = `${page}`;

        return (
            <PaginationButton
                key={page}
                pageIndex={page - 1}
                label={label}
                additionalClasses={additionalClasses}
                onPageChange={onPageChange}
                disabled={paginationDisabled}
            />
        );
    });

    return (
        <ul className="c-pagination pagination ibexa-pagination__navigation">
            <PaginationButton
                pageIndex={previousPage}
                additionalClasses="prev"
                disabled={isFirstPage || paginationDisabled}
                onPageChange={onPageChange}
            />
            {paginationButtons}
            <PaginationButton
                pageIndex={nextPage}
                additionalClasses="next"
                disabled={isLastPage || paginationDisabled}
                onPageChange={onPageChange}
            />
        </ul>
    );
};

Pagination.propTypes = {
    proximity: PropTypes.number.isRequired,
    itemsPerPage: PropTypes.number.isRequired,
    activePageIndex: PropTypes.number.isRequired,
    totalCount: PropTypes.number.isRequired,
    onPageChange: PropTypes.func.isRequired,
    disabled: PropTypes.bool.isRequired,
};

export default Pagination;
