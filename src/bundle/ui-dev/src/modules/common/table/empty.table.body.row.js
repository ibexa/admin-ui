import React from 'react';
import PropTypes from 'prop-types';

import { getTranslator } from '../../../../../Resources/public/js/scripts/helpers/context.helper';

import TableBodyRow from './table.body.row';

const EmptyTableBodyRow = ({
    extraClasses,
    infoText: customInfoText,
    actionText,
    extraActions,
    emptyTableImageSrc: customEmptyTableImageSrc,
    colspan,
}) => {
    const Translator = getTranslator();
    const defaultEmptyTableInfoText = Translator.trans(
        /*@Desc("Table is empty")*/ 'table.empty_table_body_row.info_text.default',
        {},
        'ibexa_universal_discovery_widget',
    );
    const infoText = customInfoText ?? defaultEmptyTableInfoText;
    const emptyTableImageSrc = customEmptyTableImageSrc ?? '/bundles/ibexaadminui/img/ibexa-empty-table.svg';

    return (
        <TableBodyRow extraClasses={extraClasses}>
            <td className="ibexa-table__empty-table-cell" colSpan={colspan}>
                <img className="ibexa-table__empty-table-image" src={emptyTableImageSrc} alt={infoText} />
                <div className="ibexa-table__empty-table-text">
                    <div className="ibexa-table__empty-table-info-text">{infoText}</div>
                    {actionText && <div className="ibexa-table__empty-table-action-text">{actionText}</div>}
                    {extraActions}
                </div>
            </td>
        </TableBodyRow>
    );
};

EmptyTableBodyRow.propTypes = {
    extraClasses: PropTypes.string,
    infoText: PropTypes.string,
    actionText: PropTypes.string,
    extraActions: PropTypes.element,
    emptyTableImageSrc: PropTypes.string,
    colspan: PropTypes.number,
};

EmptyTableBodyRow.defaultProps = {
    extraClasses: '',
    infoText: null,
    actionText: null,
    extraActions: null,
    emptyTableImageSrc: null,
    colspan: 9999,
};

export default EmptyTableBodyRow;
