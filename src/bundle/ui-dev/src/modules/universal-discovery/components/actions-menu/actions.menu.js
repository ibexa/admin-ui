import React, { useContext } from 'react';

import { AllowConfirmationContext, ConfirmContext, CancelContext, SelectedLocationsContext } from '../../universal.discovery.module';

const { Translator } = window;

const ActionsMenu = () => {
    const onConfirm = useContext(ConfirmContext);
    const cancelUDW = useContext(CancelContext);
    const allowConfirmation = useContext(AllowConfirmationContext);
    const [selectedLocations] = useContext(SelectedLocationsContext);
    const confirmLabel = Translator.trans(/*@Desc("Confirm")*/ 'actions_menu.confirm', {}, 'ibexa_universal_discovery_widget');
    const cancelLabel = Translator.trans(/*@Desc("Cancel")*/ 'actions_menu.cancel', {}, 'ibexa_universal_discovery_widget');
    const isConfirmDisabled = selectedLocations.length === 0;
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
                        onClick={() => onConfirm()}
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
