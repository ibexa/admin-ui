import React from 'react';
import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

const NoItemsComponent = () => {
    const Translator = getTranslator();

    return (
        <table className="ibexa-table table">
            <tbody className="ibexa-table__body">
                <tr className="ibexa-table__row">
                    <td className="ibexa-table__empty-table-cell">
                        <img className="ibexa-table__empty-table-image" src="/bundles/ibexaadminui/img/ibexa-empty-table.svg" />
                        <div className="ibexa-table__empty-table-text">
                            <div className="ibexa-table__empty-table-info-text">
                                {Translator.trans(/* @Desc("Add first sub-item") */ 'no_items.info', {}, 'ibexa_sub_items')}
                            </div>
                            <div className="ibexa-table__empty-table-action-text">
                                {Translator.trans(
                                    /* @Desc("Add sub-items by uploading or use the ‘Create’ button in the top right corner to populate this section.") */ 'no_items.action',
                                    {},
                                    'ibexa_sub_items',
                                )}
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    );
};

export default NoItemsComponent;
