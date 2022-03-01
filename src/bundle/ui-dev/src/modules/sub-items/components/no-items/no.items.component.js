import React from 'react';

const NoItemsComponent = () => {
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
