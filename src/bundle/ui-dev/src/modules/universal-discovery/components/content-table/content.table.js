import React, { useContext, useEffect, useRef } from 'react';
import PropTypes from 'prop-types';

import ContentTableItem from './content.table.item';
import Pagination from '../../../common/pagination/pagination';
import { MultipleConfigContext } from '../../universal.discovery.module';

const ContentTable = ({ count, itemsPerPage, items, activePageIndex, title, onPageChange, renderCustomHeader }) => {
    const [multiple, multipleItemsLimit] = useContext(MultipleConfigContext);
    const refContentTable = useRef(null);
    const nameLabel = Translator.trans(/*@Desc("Name")*/ 'content_table.name', {}, 'universal_discovery_widget');
    const modifiedLabel = Translator.trans(/*@Desc("Modified")*/ 'content_table.modified', {}, 'universal_discovery_widget');
    const contentTypeLabel = Translator.trans(/*@Desc("Content Type")*/ 'content_table.content_type', {}, 'universal_discovery_widget');
    const renderHeaderCell = (label) => (
        <th className="ibexa-table__header-cell">
            <span className="ibexa-table__header-cell-text-wrapper">{label}</span>
        </th>
    );

    useEffect(() => {
        window.ibexa.helpers.tooltips.parse(refContentTable.current);
    }, []);

    return (
        <div className="c-content-table" ref={refContentTable}>
            {renderCustomHeader ? (
                renderCustomHeader()
            ) : (
                <div className="ibexa-table-header">
                    <div className="ibexa-table-header__headline">{title}</div>
                </div>
            )}
            <div className="ibexa-scrollable-wrapper">
                <table className="ibexa-table table">
                    <thead>
                        <tr className="ibexa-table__head-row">
                            {multiple && renderHeaderCell()}
                            {renderHeaderCell()}
                            {renderHeaderCell(nameLabel)}
                            {renderHeaderCell(modifiedLabel)}
                            {renderHeaderCell(contentTypeLabel)}
                        </tr>
                    </thead>
                    <tbody className="ibexa-table__body">
                        {items.map((item) => (
                            <ContentTableItem key={item.id} location={item} />
                        ))}
                    </tbody>
                </table>
            </div>
            <div className="c-content-table__pagination">
                <Pagination
                    proximity={1}
                    itemsPerPage={itemsPerPage}
                    activePageIndex={activePageIndex}
                    totalCount={count}
                    onPageChange={onPageChange}
                    disabled={false}
                />
            </div>
        </div>
    );
};

ContentTable.propTypes = {
    count: PropTypes.number.isRequired,
    itemsPerPage: PropTypes.number.isRequired,
    activePageIndex: PropTypes.number.isRequired,
    items: PropTypes.array.isRequired,
    title: PropTypes.string,
    onPageChange: PropTypes.func.isRequired,
    renderCustomHeader: PropTypes.func,
};

ContentTable.defaultProps = {
    title: '',
};

export default ContentTable;
