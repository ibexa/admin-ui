const { ibexa, Translator } = window;

export class UserInvitationModal {
    constructor(options = {}) {
        if (!options.modal) {
            throw new Error('No valid modal option provided');
        }

        this.modal = options.modal;
        this.search = this.modal.querySelector('.ibexa-user-invitation-modal__search');
        this.searchInput = this.search.querySelector('.ibexa-user-invitation-modal__search-input');
        this.searchBtn = this.search.querySelector('.ibexa-input-text-wrapper__action-btn--search');
        this.searchNoEntries = this.modal.querySelector('.ibexa-user-invitation-modal__search-no-entries');
        this.addNextBtn = this.modal.querySelector('.ibexa-user-invitation-modal__add-next-btn');
        this.entriesContainer = this.modal.querySelector('.ibexa-user-invitation-modal__entries');
        this.entryPrototype = this.entriesContainer.dataset.prototype;
        this.fileUploadMessage = this.modal.querySelector('.ibexa-user-invitation-modal__upload-file-message');
        this.dropZone = this.modal.querySelector('.ibexa-user-invitation-modal__drop');
        this.uploadLocalFileBtn = this.modal.querySelector('.ibexa-user-invitation-modal__file-select');
        this.fileInput = this.modal.querySelector('.ibexa-user-invitation-modal__file-input');
        this.initialEntries = this.entriesContainer.querySelectorAll('.ibexa-user-invitation-modal__entry');
        this.lastScrolledToEntryWithIssue = null;

        this.attachEntryListeners = this.attachEntryListeners.bind(this);
        this.preventDefaultAction = this.preventDefaultAction.bind(this);
        this.handleEntryAdd = this.handleEntryAdd.bind(this);
        this.handleEntryDelete = this.handleEntryDelete.bind(this);
        this.handleDropUpload = this.handleDropUpload.bind(this);
        this.handleInputUpload = this.handleInputUpload.bind(this);
        this.handleSearch = this.handleSearch.bind(this);
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
    checkEntryMatchesSearch(entry, searchText) {
        throw new Error('checkEntryMatchesSearch should be overridden in subclass.');
    }

    // eslint-disable-next-line no-unused-vars
    checkEntriesAreDuplicate(entry, entryToCompare) {
        throw new Error('checkEntriesAreDuplicate should be overridden in subclass.');
    }

    findDuplicateEntry(entry, entriesToCompare) {
        for (const entryToCompare of entriesToCompare) {
            if (this.checkEntriesAreDuplicate(entry, entryToCompare)) {
                return entryToCompare
            }
        }

        return null;
    }

    markEntryAsDuplicate (entry) {

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
            if (entry === this.lastScrolledToEntryWithIssue) {
                this.lastScrolledToEntryWithIssue = this.lastScrolledToEntryWithIssue.previousElementSibling;
            }

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

    scrollToNextIssue() {
        const firstEntryWithIssue = this.entriesContainer.querySelector('.ibexa-user-invitation-modal__entry--has-issue');

        if (!firstEntryWithIssue) {
            return;
        }

        let nextEntryWithIssue = null;

        if (!this.lastScrolledToEntryWithIssue) {
            nextEntryWithIssue = firstEntryWithIssue;
        } else {
            let currentlyCheckedEntry = this.lastScrolledToEntryWithIssue;

            while (currentlyCheckedEntry.nextElementSibling) {
                currentlyCheckedEntry = currentlyCheckedEntry.nextElementSibling;

                if (currentlyCheckedEntry.nextElementSibling) {
                    nextEntryWithIssue = currentlyCheckedEntry;
                    break;
                }
            }

            if (!nextEntryWithIssue) {
                nextEntryWithIssue = firstEntryWithIssue;
            }
        }

        nextEntryWithIssue.scrollIntoView();
    }

    searchEntries(searchText) {
        const entries = this.entriesContainer.querySelectorAll('.ibexa-user-invitation-modal__entry');

        entries.forEach((entry) => {
            const doesEntryMatchSearch = this.checkEntryMatchesSearch(entry, searchText);

            entry.classList.toggle('ibexa-user-invitation-modal__entry--not-matching-search', !doesEntryMatchSearch);
        });
    }

    toggleSearchNoEntriesBasedOnSearch() {
        const isAnyEntryShowed = !!this.modal.querySelectorAll(
            '.ibexa-user-invitation-modal__entry:not(.ibexa-user-invitation-modal__entry--not-matching-search)',
        ).length;

        this.searchNoEntries.classList.toggle('ibexa-user-invitation-modal__search-no-entries--hidden', isAnyEntryShowed);
    }

    toggleUpload(isForceHide) {
        this.fileUploadMessage.classList.toggle('ibexa-user-invitation-modal__upload-file-message--hidden', isForceHide);
        this.dropZone.classList.toggle('ibexa-user-invitation-modal__drop--hidden', isForceHide);
    }

    showUploadedFileNotification(fileName) {
        const message = Translator.trans(
            /*@Desc("File %fileName% was uploaded")*/ 'modal.file_uploaded.notification.message',
            { fileName },
            'user_invitation',
        );

        ibexa.helpers.notification.showInfoNotification(message);
    }

    clearForm() {
        const entries = this.entriesContainer.querySelectorAll('.ibexa-user-invitation-modal__entry');

        entries.forEach((entry) => this.deleteEntry(entry));
        this.toggleUpload(false);
    }

    preventDefaultAction(event) {
        event.preventDefault();
        event.stopPropagation();
    }

    handleInvitationFile(file) {
        this.toggleUpload(true);
        this.showUploadedFileNotification(file.name);
        this.processCSVInvitationFile(file).then((invitationsData) => {
            if (!invitationsData.length) {
                return;
            }

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

    handleSearch() {
        this.searchEntries(this.searchInput.value);
        this.toggleSearchNoEntriesBasedOnSearch();
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

        this.searchInput.addEventListener('keyup', this.handleSearch, false);
        this.searchBtn.addEventListener('keyup', this.handleSearch, false);
    }
}
