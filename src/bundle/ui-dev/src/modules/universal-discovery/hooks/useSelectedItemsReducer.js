import { useReducer } from 'react';

export const ADD_SELECTED_ITEMS = 'ADD_SELECTED_ITEMS';
export const REMOVE_SELECTED_ITEMS = 'REMOVE_SELECTED_ITEMS';
export const TOGGLE_SELECTED_ITEMS = 'TOGGLE_SELECTED_ITEMS';
export const CLEAR_SELECTED_ITEMS = 'CLEAR_SELECTED_ITEMS';
export const CHANGE_MULTIPLE_SETTING = 'CHANGE_MULTIPLE_SETTING';

const checkIsItemSelected = (selectedItems, item) =>
    selectedItems.some((selectedItem) => selectedItem.type === item.type && selectedItem.id === item.id);

const filterOutSelectedItems = (selectedItems, items) => items.filter((item) => !checkIsItemSelected(selectedItems, item));

const checkIsValidSelection = (items, isMultiple, multipleItemsLimit) =>
    (!isMultiple && items.length > 1) || (isMultiple && multipleItemsLimit !== 0 && items.length > multipleItemsLimit);

const selectedItemsReducer = (state, action) => {
    const { items, isMultiple, multipleItemsLimit } = state;

    switch (action.type) {
        case ADD_SELECTED_ITEMS: {
            const oldItemsWithoutNewItems = filterOutSelectedItems(action.items, items);
            const newItems = [...oldItemsWithoutNewItems, ...action.items];

            if (checkIsValidSelection(newItems, isMultiple, multipleItemsLimit)) {
                throw new Error('useSelectedItemsReducer ADD_SELECTED_ITEMS: cannot select more than one item with single select.');
            }

            return {
                ...state,
                items: newItems,
            };
        }
        case REMOVE_SELECTED_ITEMS:
            return {
                ...state,
                items: filterOutSelectedItems(action.itemsIdsWithTypes, items),
            };
        case TOGGLE_SELECTED_ITEMS: {
            const oldItemsWithoutDeselectedItems = filterOutSelectedItems(action.items, items);
            const newItemsWithoutDeselectedItems = filterOutSelectedItems(items, action.items);
            const newItems = [...oldItemsWithoutDeselectedItems, ...newItemsWithoutDeselectedItems];

            if (checkIsValidSelection(newItems, isMultiple, multipleItemsLimit)) {
                throw new Error('useSelectedItemsReducer ADD_SELECTED_ITEMS: cannot select more than one item with single select.');
            }

            return {
                ...state,
                items: newItems,
            };
        }
        case CLEAR_SELECTED_ITEMS:
            return {
                ...state,
                items: [],
            };
        case CHANGE_MULTIPLE_SETTING:
            if (!action.isMultiple && items.length > 1) {
                throw new Error(
                    'useSelectedItemsReducer CHANGE_MULTIPLE_SETTING: cannot set to single select when multiple items are selected.',
                );
            }

            return {
                ...state,
                isMultiple: action.isMultiple,
            };
        default:
            throw new Error();
    }
};

export const useSelectedItemsReducer = ({ items = [], isMultiple, multipleItemsLimit }) => {
    const initialState = {
        isMultiple,
        multipleItemsLimit,
        items,
    };
    const [{ items: selectedItems }, dispatchSelectedItemsAction] = useReducer(selectedItemsReducer, initialState);

    return { selectedItems, dispatchSelectedItemsAction };
};
