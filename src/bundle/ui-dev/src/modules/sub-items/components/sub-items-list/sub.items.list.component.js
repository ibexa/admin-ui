import React from 'react';
import PropTypes from 'prop-types';
import { getViewComponent } from '../../services/view.registry';

const SubItemsListComponent = ({
    activeView,
    items = [],
    handleItemPriorityUpdate,
    handleEditItem,
    generateLink,
    languages,
    onItemSelect,
    toggleAllItemsSelect,
    selectedLocationsIds,
    onSortChange,
    sortClause,
    sortOrder,
    languageContainerSelector,
}) => {
    const Component = getViewComponent(activeView);

    return (
        <Component
            {...{
                activeView,
                items,
                handleItemPriorityUpdate,
                handleEditItem,
                generateLink,
                languages,
                onItemSelect,
                toggleAllItemsSelect,
                selectedLocationsIds,
                onSortChange,
                sortClause,
                sortOrder,
                languageContainerSelector,
            }}
        />
    );
};

SubItemsListComponent.propTypes = {
    activeView: PropTypes.string.isRequired,
    items: PropTypes.arrayOf(PropTypes.object),
    handleItemPriorityUpdate: PropTypes.func.isRequired,
    handleEditItem: PropTypes.func.isRequired,
    generateLink: PropTypes.func.isRequired,
    languages: PropTypes.object.isRequired,
    onItemSelect: PropTypes.func.isRequired,
    toggleAllItemsSelect: PropTypes.func.isRequired,
    selectedLocationsIds: PropTypes.instanceOf(Set).isRequired,
    onSortChange: PropTypes.func.isRequired,
    sortClause: PropTypes.string.isRequired,
    sortOrder: PropTypes.string.isRequired,
    languageContainerSelector: PropTypes.string.isRequired,
};

export default SubItemsListComponent;
