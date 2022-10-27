(function (global, doc, ibexa, Routing, Translator) {
    const SELECTOR_INPUTS_TO_VALIDATE = '.ibexa-input[required]:not([disabled]):not([hidden])';
    let targetContainer = null;
    let sourceContainer = null;
    let currentDraggedItem = null;
    let draggedItemPosition = null;
    let isEditFormValid = false;
    const editForm = doc.querySelector('.ibexa-content-type-edit-form');
    let inputsToValidate = editForm.querySelectorAll(SELECTOR_INPUTS_TO_VALIDATE);
    const draggableGroups = [];
    const token = doc.querySelector('meta[name="CSRF-Token"]').content;
    const siteaccess = doc.querySelector('meta[name="SiteAccess"]').content;
    const sectionsNode = doc.querySelector('.ibexa-content-type-edit__sections');
    const filterFieldInput = doc.querySelector('.ibexa-available-field-types__sidebar-filter');
    const popupMenuElement = sectionsNode.querySelector('.ibexa-popup-menu');
    const addGroupTriggerBtn = sectionsNode.querySelector('.ibexa-content-type-edit__add-field-definitions-group-btn');
    const noFieldsAddedError = Translator.trans(
        /*@Desc("You have to add at least one field definition")*/ 'content_type.edit.error.no_added_fields_definition',
        {},
        'content_type',
    );
    const endpoints = {
        add: {
            actionName: 'add_field_definition',
            method: 'POST',
            contentType: 'application/vnd.ibexa.api.ContentTypFieldDefinitionCreate+json',
        },
        remove: {
            actionName: 'remove_field_definition',
            method: 'DELETE',
            contentType: 'application/vnd.ibexa.api.ContentTypeFieldDefinitionDelete+json',
        },
        reorder: {
            actionName: 'reorder_field_definitions',
            method: 'PUT',
            contentType: 'application/vnd.ibexa.api.ContentTypeFieldDefinitionReorder+json',
        },
    };
    new ibexa.core.PopupMenu({
        popupMenuElement,
        triggerElement: addGroupTriggerBtn,
        onItemClick: (event) => {
            const { relatedCollapseSelector } = event.currentTarget.dataset;

            doc.querySelector(relatedCollapseSelector).classList.remove('ibexa-collapse--hidden');
            afterChangeGroup();
            toggleAddGroupTriggerBtnState();
        },
    });
    const toggleAddGroupTriggerBtnState = () => {
        const addGroupBtns = doc.querySelectorAll('.ibexa-content-type-edit__add-field-definitions-group [data-related-collapse-selector]');
        const areEveryAddGroupBtnsDisabled = [...addGroupBtns].every((btn) =>
            btn.classList.contains('ibexa-popup-menu__item-content--disabled'),
        );

        addGroupTriggerBtn.disabled = areEveryAddGroupBtnsDisabled;
    };
    const searchField = (event) => {
        const fieldFilterQueryLowerCase = event.currentTarget.value.toLowerCase();
        const fields = doc.querySelectorAll('.ibexa-available-field-types__list .ibexa-available-field-type');

        fields.forEach((field) => {
            const fieldNameNode = field.querySelector('.ibexa-available-field-type__label');
            const fieldNameLowerCase = fieldNameNode.innerText.toLowerCase();
            const isFieldHidden = !fieldNameLowerCase.includes(fieldFilterQueryLowerCase);

            field.classList.toggle('ibexa-available-field-type--hidden', isFieldHidden);
        });
    };
    const removeDragPlaceholders = () => {
        const placeholderNodes = doc.querySelectorAll(
            '.ibexa-field-definitions-placeholder:not(.ibexa-field-definitions-placeholder--anchored)',
        );

        placeholderNodes.forEach((placeholderNode) => placeholderNode.remove());
    };
    const createFieldDefinitionNode = (fieldNode) => {
        let targetPlace = '';
        const items = targetContainer.querySelectorAll('.ibexa-collapse');

        if (typeof fieldNode === 'string') {
            const container = doc.createElement('div');

            container.insertAdjacentHTML('beforeend', fieldNode);
            fieldNode = container.querySelector('.ibexa-collapse');
        }

        if (draggedItemPosition === -1) {
            targetPlace = targetContainer.querySelector('.ibexa-field-definitions-placeholder--anchored');
        } else if (draggedItemPosition === 0) {
            targetPlace = targetContainer.firstChild;
        } else {
            targetPlace = [...items].find((item, index) => index === draggedItemPosition);
        }

        fieldNode.classList.add('ibexa-collapse--field-definition-highlight');
        targetContainer.insertBefore(fieldNode, targetPlace);

        return fieldNode;
    };
    const attachFieldDefinitionNodeEvents = (fieldNode) => {
        const fieldGroupInput = fieldNode.querySelector('.ibexa-input--field-group');
        const removeFieldsBtn = fieldNode.querySelectorAll('.ibexa-collapse__extra-action-button--remove-field-definitions');
        const fieldInputsToValidate = fieldNode.querySelectorAll(SELECTOR_INPUTS_TO_VALIDATE);
        const groupCollapseNode = targetContainer.closest('.ibexa-collapse--field-definitions-group');
        const { fieldsGroupId } = groupCollapseNode.dataset;

        fieldInputsToValidate.forEach(attachValidateEvents);
        fieldGroupInput.value = fieldsGroupId;
        removeFieldsBtn.forEach((removeFieldBtn) => {
            removeFieldBtn.addEventListener('click', removeField, false);
        });

        const dropdowns = fieldNode.querySelectorAll('.ibexa-dropdown');

        dropdowns.forEach((dropdownContainer) => {
            const dropdown = new ibexa.core.Dropdown({
                container: dropdownContainer,
            });

            dropdown.init();
        });

        draggableGroups.forEach((group) => {
            group.reinit();
        });
    };
    const dispatchInsertFieldDefinitionNode = (fieldNode) => {
        doc.body.dispatchEvent(new CustomEvent('ibexa-inputs:added'));
        doc.body.dispatchEvent(
            new CustomEvent('ibexa-drop-field-definition', {
                detail: { nodes: [fieldNode] },
            }),
        );
    };
    const insertFieldDefinitionNode = (fieldNode) => {
        const fieldDefinitionNode = createFieldDefinitionNode(fieldNode);

        removeDragPlaceholders();
        attachFieldDefinitionNodeEvents(fieldDefinitionNode);
        dispatchInsertFieldDefinitionNode(fieldDefinitionNode);

        return fieldDefinitionNode;
    };
    const generateRequest = (action, bodyData, languageCode) => {
        const { actionName, method, contentType } = endpoints[action];
        const { contentTypeGroupId, contentTypeId } = sectionsNode.dataset;
        let endpointURL = `/api/ibexa/v2/contenttypegroup/${contentTypeGroupId}/contenttype/${contentTypeId}/${actionName}`;

        if (languageCode) {
            endpointURL += `/${languageCode}`;
        }

        return new Request(endpointURL, {
            method,
            mode: 'same-origin',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/html',
                'Content-Type': contentType,
                'X-Siteaccess': siteaccess,
                'X-CSRF-Token': token,
            },
            body: JSON.stringify(bodyData),
        });
    };
    const afterChangeGroup = () => {
        const groups = doc.querySelectorAll('.ibexa-collapse--field-definitions-group');
        const itemsAction = doc.querySelectorAll('.ibexa-content-type-edit__add-field-definitions-group .ibexa-popup-menu__item-content');

        groups.forEach((group) => {
            const groupFieldsDefinitionCount = group.querySelectorAll('.ibexa-collapse--field-definition').length;
            const emptyGroupPlaceholder = group.querySelector('.ibexa-field-definitions-empty-group');
            const anchoredPlaceholder = group.querySelector('.ibexa-field-definitions-placeholder--anchored');
            const removeBtn = group.querySelector('.ibexa-collapse__extra-action-button--remove-field-definitions-group');

            emptyGroupPlaceholder.classList.toggle('ibexa-field-definitions-empty-group--hidden', groupFieldsDefinitionCount !== 0);
            anchoredPlaceholder.classList.toggle('ibexa-field-definitions-placeholder--hidden', groupFieldsDefinitionCount === 0);
            removeBtn.disabled = groupFieldsDefinitionCount > 0;
        });

        itemsAction.forEach((itemAction) => {
            const { relatedCollapseSelector } = itemAction.dataset;
            const isGroupHidden = doc.querySelector(relatedCollapseSelector).classList.contains('ibexa-collapse--hidden');

            itemAction.classList.toggle('ibexa-popup-menu__item-content--disabled', !isGroupHidden);
        });

        doc.querySelectorAll('.ibexa-collapse--field-definition').forEach((fieldDefinition, index) => {
            fieldDefinition.querySelector('.ibexa-input--position').value = index;
        });
    };
    const addField = () => {
        if (!sourceContainer.classList.contains('ibexa-available-field-types__list')) {
            insertFieldDefinitionNode(currentDraggedItem);
            afterChangeGroup();

            return;
        }

        const { languageCode } = sectionsNode.dataset;
        const { itemIdentifier } = currentDraggedItem.dataset;
        const { fieldsGroupId } = targetContainer.closest('.ibexa-collapse--field-definitions-group').dataset;

        const bodyData = {
            FieldDefinitionCreate: {
                fieldTypeIdentifier: itemIdentifier,
                fieldGroupIdentifier: fieldsGroupId,
            },
        };

        if (draggedItemPosition !== -1) {
            bodyData.FieldDefinitionCreate.position = draggedItemPosition;
        }

        fetch(generateRequest('add', bodyData, languageCode))
            .then(ibexa.helpers.request.getTextFromResponse)
            .then((response) => {
                insertFieldDefinitionNode(response);
                afterChangeGroup();
            })
            .catch(ibexa.helpers.notification.showErrorNotification);
    };
    const reorderFields = () => {
        createFieldDefinitionNode(currentDraggedItem);
        removeDragPlaceholders();

        const fieldsOrder = [...doc.querySelectorAll('.ibexa-collapse--field-definition')].map(
            (fieldDefinition) => fieldDefinition.dataset.fieldDefinitionIdentifier,
        );
        const bodyData = {
            FieldDefinitionReorder: {
                fieldDefinitionIdentifiers: fieldsOrder,
            },
        };
        const request = generateRequest('reorder', bodyData);

        fetch(request)
            .then(ibexa.helpers.request.getTextFromResponse)
            .then(() => afterChangeGroup())
            .catch(ibexa.helpers.notification.showErrorNotification);
    };
    const removeFieldsGroup = (event) => {
        if (event.currentTarget.hasAttribute('disabled')) {
            return;
        }

        const collapseNode = event.currentTarget.closest('.ibexa-collapse');
        const fieldsToDelete = [...collapseNode.querySelectorAll('.ibexa-collapse--field-definition')].map(
            (fieldDefinition) => fieldDefinition.dataset.fieldDefinitionIdentifier,
        );
        const bodyData = {
            FieldDefinitionDelete: {
                fieldDefinitionIdentifiers: fieldsToDelete,
            },
        };

        fetch(generateRequest('remove', bodyData))
            .then(ibexa.helpers.request.getTextFromResponse)
            .then(() => {
                collapseNode.classList.add('ibexa-collapse--hidden');
                collapseNode.querySelectorAll('.ibexa-collapse--field-definition').forEach((fieldDefinition) => {
                    fieldDefinition.remove();
                });
                afterChangeGroup();
                toggleAddGroupTriggerBtnState();
            })
            .catch(ibexa.helpers.notification.showErrorNotification);
    };
    const removeField = (event) => {
        const collapseNode = event.currentTarget.closest('.ibexa-collapse');
        const itemToDeleteIdentifiers = collapseNode.dataset.fieldDefinitionIdentifier;
        const bodyData = {
            FieldDefinitionDelete: {
                fieldDefinitionIdentifiers: [itemToDeleteIdentifiers],
            },
        };

        fetch(generateRequest('remove', bodyData))
            .then(ibexa.helpers.request.getTextFromResponse)
            .then(() => {
                collapseNode.remove();
                afterChangeGroup();
            })
            .catch(ibexa.helpers.notification.showErrorNotification);
    };
    const validateInput = (input) => {
        const isInputEmpty = !input.value;
        const field = input.closest('.form-group');
        const labelNode = field.querySelector('.ibexa-label');
        const errorNode = field.querySelector('.ibexa-form-error');

        input.classList.toggle('is-invalid', isInputEmpty);

        if (errorNode) {
            const fieldName = labelNode.innerHTML;
            const errorMessage = ibexa.errors.emptyField.replace('{fieldName}', fieldName);

            errorNode.innerHTML = isInputEmpty ? errorMessage : '';
        }

        isEditFormValid = isEditFormValid && !isInputEmpty;
    };
    const validateForm = () => {
        const fieldDefinitionsStatuses = {};

        isEditFormValid = true;
        inputsToValidate = editForm.querySelectorAll(SELECTOR_INPUTS_TO_VALIDATE);

        inputsToValidate.forEach((input) => {
            const fieldDefinition = input.closest('.ibexa-collapse--field-definition');

            if (fieldDefinition) {
                const { fieldDefinitionIdentifier } = fieldDefinition.dataset;
                const isInputEmpty = !input.value;

                if (!fieldDefinitionsStatuses[fieldDefinitionIdentifier]) {
                    fieldDefinitionsStatuses[fieldDefinitionIdentifier] = [];
                }

                fieldDefinitionsStatuses[fieldDefinitionIdentifier].push(isInputEmpty);
            }

            validateInput(input);
        });

        Object.entries(fieldDefinitionsStatuses).forEach(([fieldDefinitionIdentifier, inputsStatus]) => {
            const isFieldDefinitionValid = inputsStatus.every((hasError) => !hasError);
            const fieldDefinitionNode = doc.querySelector(`[data-field-definition-identifier="${fieldDefinitionIdentifier}"]`);

            fieldDefinitionNode.classList.toggle('is-invalid', !isFieldDefinitionValid);
        });
    };
    const attachValidateEvents = (input) => {
        input.addEventListener('change', validateForm, false);
        input.addEventListener('blur', validateForm, false);
        input.addEventListener('input', validateForm, false);
    };
    const scrollToInvalidInput = () => {
        const firstInvalidInput = editForm.querySelector('.ibexa-input.is-invalid');
        const fieldDefinition = firstInvalidInput.closest('.ibexa-collapse--field-definition');
        const scrollToNode = fieldDefinition ?? firstInvalidInput;

        scrollToNode.scrollIntoView({ behavior: 'smooth' });
    };
    class FieldDefinitionDraggable extends ibexa.core.Draggable {
        onDrop(event) {
            targetContainer = event.currentTarget;

            const dragContainerItems = targetContainer.querySelectorAll(
                '.ibexa-collapse--field-definition, .ibexa-field-definitions-placeholder:not(.ibexa-field-definitions-placeholder--anchored)',
            );
            const currentActiveGroup = doc.querySelector(
                '.ibexa-collapse--field-definitions-group.ibexa-collapse--active-field-definitions-group',
            );
            const targetContainerGroup = targetContainer.closest('.ibexa-collapse--field-definitions-group');

            draggedItemPosition = [...dragContainerItems].findIndex((item, index, array) => {
                return item.classList.contains('ibexa-field-definitions-placeholder') && index < array.length - 1;
            });

            if (sourceContainer.isEqualNode(targetContainer)) {
                reorderFields();
            } else {
                addField();
            }

            currentActiveGroup?.classList.remove('ibexa-collapse--active-field-definitions-group');
            targetContainerGroup.classList.add('ibexa-collapse--active-field-definitions-group');

            removeDragPlaceholders();
        }

        onDragStart(event) {
            super.onDragStart(event);

            currentDraggedItem = event.currentTarget;
            sourceContainer = currentDraggedItem.parentNode;
        }

        onDragEnd() {
            currentDraggedItem.style.removeProperty('display');
        }
    }

    filterFieldInput.addEventListener('keyup', searchField, false);
    filterFieldInput.addEventListener('input', searchField, false);

    const firstFieldDefinitionsGroupContent = doc.querySelector('.ibexa-content-type-edit__section .ibexa-field-definitions-group-content');

    if (firstFieldDefinitionsGroupContent) {
        firstFieldDefinitionsGroupContent.classList.add('ibexa-collapse--active-field-definitions-group');
    }

    doc.querySelectorAll('.ibexa-collapse__extra-action-button--remove-field-definitions').forEach((removeFieldDefinitionsButton) => {
        removeFieldDefinitionsButton.addEventListener('click', removeField, false);
    });
    doc.querySelectorAll('.ibexa-collapse__extra-action-button--remove-field-definitions-group').forEach(
        (removeFieldDefinitionsGroupButton) => {
            const groupFieldsDefinitionCount = removeFieldDefinitionsGroupButton
                .closest('.ibexa-collapse--field-definitions-group')
                .querySelectorAll('.ibexa-collapse--field-definition').length;

            removeFieldDefinitionsGroupButton.toggleAttribute('disabled', groupFieldsDefinitionCount > 0);
            removeFieldDefinitionsGroupButton.addEventListener('click', removeFieldsGroup, false);
        },
    );

    doc.querySelectorAll('.ibexa-available-field-types__list .ibexa-available-field-type').forEach((availableField) => {
        availableField.addEventListener(
            'dragstart',
            (event) => {
                currentDraggedItem = event.currentTarget;
                sourceContainer = currentDraggedItem.parentNode;
                currentDraggedItem.classList.add('ibexa-available-field-type--is-dragging-out');
            },
            false,
        );
        availableField.addEventListener(
            'dragend',
            () => {
                currentDraggedItem.classList.remove('ibexa-available-field-type--is-dragging-out');
            },
            false,
        );
        availableField.addEventListener(
            'click',
            (event) => {
                const activeTargetContainer = doc.querySelector(
                    '.ibexa-collapse--field-definitions-group.ibexa-collapse--active-field-definitions-group .ibexa-content-type-edit__field-definition-drop-zone',
                );

                if (!activeTargetContainer) {
                    return;
                }

                currentDraggedItem = event.currentTarget;
                sourceContainer = currentDraggedItem.parentNode;
                draggedItemPosition = -1;
                targetContainer = activeTargetContainer;

                addField();
            },
            false,
        );
    });
    doc.querySelectorAll('.ibexa-content-type-edit__field-definition-drop-zone').forEach((collapseCotentNode) => {
        const draggable = new FieldDefinitionDraggable({
            itemsContainer: collapseCotentNode,
            selectorItem: '.ibexa-collapse--field-definition',
            selectorPlaceholder: '.ibexa-field-definitions-placeholder',
            selectorPreventDrag: '.ibexa-collapse__body',
        });

        draggable.init();
        draggableGroups.push(draggable);
    });

    inputsToValidate.forEach(attachValidateEvents);

    editForm.addEventListener(
        'submit',
        (event) => {
            const { submitter } = event;

            if (!submitter?.hasAttribute('formnovalidate')) {
                const fieldDefinitionsCount = doc.querySelectorAll('.ibexa-collapse--field-definition').length;

                validateForm();

                if (isEditFormValid) {
                    if (!fieldDefinitionsCount) {
                        event.preventDefault();
                        ibexa.helpers.notification.showErrorNotification(noFieldsAddedError);
                    }
                } else {
                    event.preventDefault();
                    scrollToInvalidInput();
                }
            }
        },
        false,
    );
    toggleAddGroupTriggerBtnState();
})(window, window.document, window.ibexa, window.Routing, window.Translator);
