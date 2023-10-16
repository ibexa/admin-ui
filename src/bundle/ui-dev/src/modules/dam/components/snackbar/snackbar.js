import React, { useContext, useState, useEffect } from 'react';
import PropTypes from 'prop-types';
import Icon from '../../../common/icon/icon';
import { ConfirmContext, SelectedLocationsContext } from '../../../universal-discovery/universal.discovery.module';

const Snackbar = ({ selectedItems }) => {
    const onConfirm = useContext(ConfirmContext);
    const [selectedLocations, dispatchSelectedLocationsAction] = useContext(SelectedLocationsContext);
    const isAnyLocationSelected = !!selectedLocations.length;

    if (!isAnyLocationSelected) {
        return null;
    }

    return (
        <div className="c-dwb-snackbar">
            <div className="c-dwb-snackbar__selection-info">
                <div className="c-dwb-snackbar__selection-info-label">Selected</div>
                {selectedLocations.map((selectedLocation) => (
                    <div key={selectedLocation.location.id} className="c-dwb-snackbar__selection-info-item">
                        {selectedLocation.location.ContentInfo.Content.TranslatedName}
                    </div>
                ))}
            </div>
            <button
                className="c-dwb-snackbar__insert-btn btn ibexa-btn ibexa-btn--small ibexa-btn--dark"
                type="button"
                onClick={() => onConfirm()}
            >
                <Icon name="upload-image" extraClasses="ibexa-icon--small" />
                Insert
            </button>
        </div>
    );
};

Snackbar.propTypes = {
    selectedItems: PropTypes.array.isRequired,
};

Snackbar.defaultProps = {};

export default Snackbar;
