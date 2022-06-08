import { fileSizeToString } from '@ibexa-admin-ui/src/bundle/ui-dev/src/modules/multi-file-upload/helpers/text.helper';

(function (global, doc, ibexa) {
    const modal = doc.querySelector('.ibexa-user-invitation-modal');

    if (!modal) {
        return;
    }

    const addNextBtn = doc.querySelector('.ibexa-user-invitation-modal__add-next-btn');
    const entriesContainer = doc.querySelector('.ibexa-user-invitation-modal__entries');
    const entryPrototype = entriesContainer.dataset.prototype;
    const fileUploadMessage = doc.querySelector('.ibexa-user-invitation-modal__upload-file-message');
    const dropZone = doc.querySelector('.ibexa-user-invitation-modal__drop');
    const uploadLocalFileBtn = doc.querySelector('.ibexa-user-invitation-modal__file-select');
    const fileInput = doc.querySelector('.ibexa-user-invitation-modal__file-input');
    const uploadedFileNode = doc.querySelector('.ibexa-user-invitation-modal__uploaded-file');
    const uploadedItemNameNode = uploadedFileNode.querySelector('.ibexa-user-invitation-modal__uploaded-item-name');
    const uploadedItemSizeNode = uploadedFileNode.querySelector('.ibexa-user-invitation-modal__uploaded-item-size');
    const uploadedFileDeleteBtn = uploadedFileNode.querySelector('.ibexa-user-invitation-modal__uploaded-item-delete-btn');
    const initialEntries = entriesContainer.querySelectorAll('.ibexa-user-invitation-modal__entry');
    let entryCounter = doc.querySelectorAll('.ibexa-user-invitation-modal__entry').length;
    const addEntry = (isFileRelated = false, invitationData = null) => {
        const entryPrototypeRendered = entryPrototype.replaceAll('__name__', entryCounter);

        entryCounter = entryCounter + 1;
        entriesContainer.insertAdjacentHTML('beforeend', entryPrototypeRendered);

        const insertedEntry = entriesContainer.querySelector(':scope > :last-child');

        if (isFileRelated) {
            insertedEntry.classList.add('ibexa-user-invitation-modal__entry--file-related');
        }

        attachEntryListeners(insertedEntry);

        modal.dispatchEvent(
            new CustomEvent('ibexa-user-invitation-modal:entry-added', {
                detail: {
                    insertedEntry,
                    isFileRelated,
                    invitationData,
                },
            }),
        );
    };
    const deleteEntry = (entry, isForceRemove = false) => {
        const entryNodes = entriesContainer.querySelectorAll('.ibexa-user-invitation-modal__entry');
        const isLastEntry = entryNodes.length === 1;

        if (isLastEntry && !isForceRemove) {
            modal.dispatchEvent(
                new CustomEvent('ibexa-user-invitation-modal:reset-entry', {
                    detail: {
                        entry,
                    },
                }),
            );
        } else {
            entry.remove();
        }
    };
    const deleteTrailingEntriesIfEmpty = () => {
        const lastEntry = entriesContainer.querySelector(':scope > :last-child');

        if (!lastEntry) {
            return;
        }

        const emailInput = lastEntry.querySelector('.ibexa-user-invitation-modal__entry-email');
        const dropdownNode = lastEntry.querySelector('.ibexa-dropdown');
        const dropdown = ibexa.helpers.objectInstances.getInstance(dropdownNode);
        const dropdownSelectedOption = dropdown.getSelectedItems()[0];
        const dropdownFirstOption = dropdownNode.querySelector('.ibexa-dropdown__source option');

        if (!emailInput.value && dropdownSelectedOption === dropdownFirstOption) {
            deleteEntry(lastEntry, true);
            deleteTrailingEntriesIfEmpty();
        }
    };
    const handleEntryAdd = () => {
        addEntry();
    };
    const handleEntryDelete = (event) => {
        const deleteBtn = event.currentTarget;
        const entry = deleteBtn.closest('.ibexa-user-invitation-modal__entry');

        deleteEntry(entry);
    };
    const attachEntryListeners = (entry) => {
        const deleteEntryBtn = entry.querySelector('.ibexa-user-invitation-modal__entry-delete-btn');

        deleteEntryBtn.addEventListener('click', handleEntryDelete, false);
    };
    const handleFileDelete = () => {
        const fileRelatedEntries = entriesContainer.querySelectorAll('.ibexa-user-invitation-modal__entry--file-related');
        const entriesCount = entriesContainer.children.length;
        const areAllEntriesFileRelated = fileRelatedEntries.length === entriesCount;

        fileRelatedEntries.forEach((entry) => deleteEntry(entry));
        toggleUpload(false);
        toggleUploadedFileInfo(true);

        if (areAllEntriesFileRelated) {
            addEntry();
        }
    };
    const toggleUpload = (isForceHide) => {
        fileUploadMessage.classList.toggle('ibexa-user-invitation-modal__upload-file-message--hidden', isForceHide);
        dropZone.classList.toggle('ibexa-user-invitation-modal__drop--hidden', isForceHide);
    };
    const toggleUploadedFileInfo = (isForceHide) => {
        uploadedFileNode.classList.toggle('ibexa-user-invitation-modal__uploaded-file--hidden', isForceHide);
    };
    const setUploadedFileData = (name, size) => {
        uploadedItemNameNode.innerText = name;
        uploadedItemSizeNode.innerText = fileSizeToString(size);
    };
    const clearForm = () => {
        const entries = entriesContainer.querySelectorAll('.ibexa-user-invitation-modal__entry');

        entries.forEach((entry) => deleteEntry(entry));
        toggleUpload(false);
        toggleUploadedFileInfo(true);
    };
    const preventDefaultAction = (event) => {
        event.preventDefault();
        event.stopPropagation();
    };
    const handleInvitationFile = (file) => {
        setUploadedFileData(file.name, file.size);
        toggleUpload(true);
        toggleUploadedFileInfo(false);

        modal.dispatchEvent(
            new CustomEvent('ibexa-user-invitation-modal:process-file', {
                detail: {
                    file,
                    callback: (invitationsData)=> {
                        deleteTrailingEntriesIfEmpty();
                        invitationsData.forEach((invitationData) => {
                            addEntry(true, invitationData);
                        })
                    }
                },
            }),
        );
    };
    const handleInputUpload = (event) => {
        preventDefaultAction(event);

        const file = fileInput.files[0];

        if (file) {
            handleInvitationFile(file);
        }
    };
    const handleDropUpload = (event) => {
        preventDefaultAction(event);

        const file = event.dataTransfer.files[0];

        if (file) {
            handleInvitationFile(file);
        }
    };

    initialEntries.forEach(attachEntryListeners);

    modal.addEventListener('shown.bs.modal', function () {
        window.addEventListener('drop', preventDefaultAction, false);
        window.addEventListener('dragover', preventDefaultAction, false);
    });

    modal.addEventListener('hidden.bs.modal', function () {
        window.removeEventListener('drop', preventDefaultAction, false);
        window.removeEventListener('dragover', preventDefaultAction, false);
        clearForm();
    });

    addNextBtn.addEventListener('click', handleEntryAdd, false);

    dropZone.addEventListener('drop', handleDropUpload, false);
    uploadLocalFileBtn.addEventListener(
        'click',
        (event) => {
            event.preventDefault();
            fileInput.value = '';
            fileInput.click();
        },
        false,
    );
    fileInput.addEventListener('change', handleInputUpload, false);
    uploadedFileDeleteBtn.addEventListener('click', handleFileDelete, false);
})(window, window.document, window.ibexa);
