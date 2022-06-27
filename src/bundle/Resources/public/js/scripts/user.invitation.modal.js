import { fileSizeToString } from '@ibexa-admin-ui/src/bundle/ui-dev/src/modules/multi-file-upload/helpers/text.helper';

export class UserInvitationModal {
    constructor(options = {}) {
        if (!options.modal) {
            throw new Error('No valid modal option provided');
        }

        this.modal = options.modal;
        this.addNextBtn = this.modal.querySelector('.ibexa-user-invitation-modal__add-next-btn');
        this.entriesContainer = this.modal.querySelector('.ibexa-user-invitation-modal__entries');
        this.entryPrototype = this.entriesContainer.dataset.prototype;
        this.fileUploadMessage = this.modal.querySelector('.ibexa-user-invitation-modal__upload-file-message');
        this.dropZone = this.modal.querySelector('.ibexa-user-invitation-modal__drop');
        this.uploadLocalFileBtn = this.modal.querySelector('.ibexa-user-invitation-modal__file-select');
        this.fileInput = this.modal.querySelector('.ibexa-user-invitation-modal__file-input');
        this.uploadedFileNode = this.modal.querySelector('.ibexa-user-invitation-modal__uploaded-file');
        this.uploadedItemNameNode = this.uploadedFileNode.querySelector('.ibexa-user-invitation-modal__uploaded-item-name');
        this.uploadedItemSizeNode = this.uploadedFileNode.querySelector('.ibexa-user-invitation-modal__uploaded-item-size');
        this.uploadedFileDeleteBtn = this.uploadedFileNode.querySelector('.ibexa-user-invitation-modal__uploaded-item-delete-btn');
        this.initialEntries = this.entriesContainer.querySelectorAll('.ibexa-user-invitation-modal__entry');

        this.attachEntryListeners = this.attachEntryListeners.bind(this);
        this.preventDefaultAction = this.preventDefaultAction.bind(this);
        this.handleEntryAdd = this.handleEntryAdd.bind(this);
        this.handleEntryDelete = this.handleEntryDelete.bind(this);
        this.handleDropUpload = this.handleDropUpload.bind(this);
        this.handleInputUpload = this.handleInputUpload.bind(this);
        this.handleFileDelete = this.handleFileDelete.bind(this);
    }

    processCSVInvitationFile() {
        throw new Error('processCSVInvitationFile should be overridden in subclass.');
    }

    // eslint-disable-next-line no-unused-vars
    resetEntry(entry) {
        throw new Error('resetEntry should be overridden in subclass.');
    }

    // eslint-disable-next-line no-unused-vars
    isEntryEmpty(entry) {
        throw new Error('isEntryEmpty should be overridden in subclass.');
    }

    // eslint-disable-next-line no-unused-vars
    addEntry(isFileRelated = false, invitationData = null) {
        const entryPrototypeRendered = this.entryPrototype.replaceAll('__name__', this.entryCounter);

        this.entryCounter = this.entryCounter + 1;
        this.entriesContainer.insertAdjacentHTML('beforeend', entryPrototypeRendered);

        const insertedEntry = this.entriesContainer.querySelector(':scope > :last-child');

        if (isFileRelated) {
            insertedEntry.classList.add('ibexa-user-invitation-modal__entry--file-related');
        }

        this.attachEntryListeners(insertedEntry);

        return { insertedEntry };
    }

    deleteEntry(entry, isForceRemove = false) {
        const entryNodes = this.entriesContainer.querySelectorAll('.ibexa-user-invitation-modal__entry');
        const isLastEntry = entryNodes.length === 1;

        if (isLastEntry && !isForceRemove) {
            this.resetEntry(entry);
        } else {
            entry.remove();
        }
    }

    deleteTrailingEntriesIfEmpty() {
        const lastEntry = this.entriesContainer.querySelector(':scope > :last-child');

        if (!lastEntry) {
            return;
        }

        if (this.isEntryEmpty(lastEntry)) {
            this.deleteEntry(lastEntry, true);
            this.deleteTrailingEntriesIfEmpty();
        }
    }

    handleEntryAdd() {
        this.addEntry();
    }

    handleEntryDelete(event) {
        const deleteBtn = event.currentTarget;
        const entry = deleteBtn.closest('.ibexa-user-invitation-modal__entry');

        this.deleteEntry(entry);
    }

    attachEntryListeners(entry) {
        const deleteEntryBtn = entry.querySelector('.ibexa-user-invitation-modal__entry-delete-btn');

        deleteEntryBtn.addEventListener('click', this.handleEntryDelete, false);
    }

    handleFileDelete() {
        const fileRelatedEntries = this.entriesContainer.querySelectorAll('.ibexa-user-invitation-modal__entry--file-related');
        const entriesCount = this.entriesContainer.children.length;
        const areAllEntriesFileRelated = fileRelatedEntries.length === entriesCount;

        fileRelatedEntries.forEach((entry) => this.deleteEntry(entry, true));
        this.toggleUpload(false);
        this.toggleUploadedFileInfo(true);

        if (areAllEntriesFileRelated) {
            this.addEntry();
        }
    }

    toggleUpload(isForceHide) {
        this.fileUploadMessage.classList.toggle('ibexa-user-invitation-modal__upload-file-message--hidden', isForceHide);
        this.dropZone.classList.toggle('ibexa-user-invitation-modal__drop--hidden', isForceHide);
    }

    toggleUploadedFileInfo(isForceHide) {
        this.uploadedFileNode.classList.toggle('ibexa-user-invitation-modal__uploaded-file--hidden', isForceHide);
    }

    setUploadedFileData(name, size) {
        this.uploadedItemNameNode.innerText = name;
        this.uploadedItemSizeNode.innerText = fileSizeToString(size);
    }

    clearForm() {
        const entries = this.entriesContainer.querySelectorAll('.ibexa-user-invitation-modal__entry');

        entries.forEach((entry) => this.deleteEntry(entry));
        this.toggleUpload(false);
        this.toggleUploadedFileInfo(true);
    }

    preventDefaultAction(event) {
        event.preventDefault();
        event.stopPropagation();
    }

    handleInvitationFile(file) {
        this.setUploadedFileData(file.name, file.size);
        this.toggleUpload(true);
        this.toggleUploadedFileInfo(false);
        this.processCSVInvitationFile(file).then((invitationsData) => {
            this.deleteTrailingEntriesIfEmpty();
            invitationsData.forEach((invitationData) => {
                this.addEntry(true, invitationData);
            });
        });
    }

    handleInputUpload(event) {
        this.preventDefaultAction(event);

        const file = this.fileInput.files[0];

        if (file) {
            this.handleInvitationFile(file);
        }
    }

    handleDropUpload(event) {
        this.preventDefaultAction(event);

        const file = event.dataTransfer.files[0];

        if (file) {
            this.handleInvitationFile(file);
        }
    }

    init() {
        this.entryCounter = this.modal.querySelectorAll('.ibexa-user-invitation-modal__entry').length;

        this.initialEntries.forEach(this.attachEntryListeners);

        this.modal.addEventListener('shown.bs.modal', () => {
            window.addEventListener('drop', this.preventDefaultAction, false);
            window.addEventListener('dragover', this.preventDefaultAction, false);
        });

        this.modal.addEventListener('hidden.bs.modal', () => {
            window.removeEventListener('drop', this.preventDefaultAction, false);
            window.removeEventListener('dragover', this.preventDefaultAction, false);
            this.clearForm();
        });

        this.addNextBtn.addEventListener('click', this.handleEntryAdd, false);

        this.dropZone.addEventListener('drop', this.handleDropUpload, false);
        this.uploadLocalFileBtn.addEventListener(
            'click',
            (event) => {
                event.preventDefault();
                this.fileInput.value = '';
                this.fileInput.click();
            },
            false,
        );
        this.fileInput.addEventListener('change', this.handleInputUpload, false);
        this.uploadedFileDeleteBtn.addEventListener('click', this.handleFileDelete, false);
    }
}
