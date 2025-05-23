import React, { useContext, useEffect, useRef } from 'react';
import PropTypes from 'prop-types';

import ContentTableItem from './content.table.item';
import Pagination from '../../../common/pagination/pagination';
import { MultipleConfigContext } from '../../universal.discovery.module';

import { getTranslator } from '../../../../../../Resources/public/js/scripts/helpers/context.helper';
import { parse as parseTooltip } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/tooltips.helper';

const ContentTable = ({ count, itemsPerPage, items, activePageIndex, title = '', onPageChange, renderCustomHeader = null }) => {
    const Translator = getTranslator();
    const [multiple] = useContext(MultipleConfigContext);
    const refContentTable = useRef(null);
    const nameLabel = Translator.trans(/* @Desc("Name") */ 'content_table.name', {}, 'ibexa_universal_discovery_widget');
    const modifiedLabel = Translator.trans(/* @Desc("Modified") */ 'content_table.modified', {}, 'ibexa_universal_discovery_widget');
    const contentTypeLabel = Translator.trans(
        /* @Desc("Content type") */ 'content_table.content_type',
        {},
        'ibexa_universal_discovery_widget',
    );
    const renderHeaderCell = (label) => (
        <th className="ibexa-table__header-cell">
            <span className="ibexa-table__header-cell-text-wrapper">{label}</span>
        </th>
    );

    useEffect(() => {
        parseTooltip(refContentTable.current);
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

export default ContentTable;
