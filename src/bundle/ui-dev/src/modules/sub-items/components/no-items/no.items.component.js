import React from 'react';

const NoItemsComponent = () => {
    const noItemsMessage = Translator.trans(/*@Desc("This location has no sub-items")*/ 'no_items.message', {}, 'sub_items');

    return (
        <table className="ibexa-table table">
            <tbody className="ibexa-table__body">
                <td className="ibexa-table__empty-table-cell">
                    <img className="ibexa-table__empty-table-image" src="/bundles/ibexaadminui/img/ibexa-empty-table.svg" />
                </td>
            </tbody>
        </table>
    );
};

export default NoItemsComponent;
