import React, { useCallback, useContext } from 'react';

import {
    AllowConfirmationContext,
    ConfirmContext,
    CancelContext,
    SelectedLocationsContext,
    SelectedItemsContext,
    ConfirmItemsContext,
} from '../../universal.discovery.module';
import { getTranslator } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';

const ActionsMenu = () => {
    const Translator = getTranslator();
    const onConfirm = useContext(ConfirmContext);
    const onItemsConfirm = useContext(ConfirmItemsContext);
    const cancelUDW = useContext(CancelContext);
    const allowConfirmation = useContext(AllowConfirmationContext);
    const [selectedLocations] = useContext(SelectedLocationsContext);
    const { selectedItems } = useContext(SelectedItemsContext);
    const confirmLabel = Translator.trans(/*@Desc("Confirm")*/ 'actions_menu.confirm', {}, 'ibexa_universal_discovery_widget');
    const cancelLabel = Translator.trans(/*@Desc("Discard")*/ 'actions_menu.cancel', {}, 'ibexa_universal_discovery_widget');
    const isConfirmDisabled = selectedLocations.length === 0 && selectedItems.length === 0;
    const handleConfirmBtnClick = useCallback(() => {
        if (selectedLocations.length > 0) {
            onConfirm();

            return;
        }

        onItemsConfirm();
    }, [onConfirm, selectedLocations, onItemsConfirm, selectedItems]);
    const renderActionsContent = () => {
        if (!allowConfirmation) {
            return null;
        }

        return (
            <>
                <span className="c-actions-menu__confirm-btn-wrapper">
                    <button
                        className="c-actions-menu__confirm-btn btn ibexa-btn ibexa-btn--primary"
                        type="button"
                        onClick={handleConfirmBtnClick}
                        disabled={isConfirmDisabled}
                    >
                        {confirmLabel}
                    </button>
                </span>
                <span className="c-actions-menu__cancel-btn-wrapper">
                    <button className="c-actions-menu__cancel-btn btn ibexa-btn ibexa-btn--secondary" type="button" onClick={cancelUDW}>
                        {cancelLabel}
                    </button>
                </span>
            </>
        );
    };

    return <div className="c-actions-menu">{renderActionsContent()}</div>;
};

export default ActionsMenu;
