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
        this.badFileAlert = this.modal.querySelector('.ibexa-user-invitation-modal__bad-file-alert');
        this.badFileAlertCloseBtn = this.badFileAlert.querySelector('.ibexa-alert__close-btn');
        this.issuesAlert = this.modal.querySelector('.ibexa-user-invitation-modal__issues-alert');
        this.issuesAlertIssuesContainer = this.modal.querySelector('.ibexa-user-invitation-modal__issues-alert-issues');
        this.issuesAlertCloseBtn = this.issuesAlert.querySelector('.ibexa-alert__close-btn');
        this.goToNextIssueBtn = this.issuesAlert.querySelector('.ibexa-user-invitation-modal__next-issue-btn');
        this.addNextBtn = this.modal.querySelector('.ibexa-user-invitation-modal__add-next-btn');
        this.entriesContainer = this.modal.querySelector('.ibexa-user-invitation-modal__entries');
        this.entryPrototype = this.entriesContainer.dataset.prototype;
        this.fileUploadMessage = this.modal.querySelector('.ibexa-user-invitation-modal__upload-file-message');
        this.dropZone = this.modal.querySelector('.ibexa-user-invitation-modal__drop');
        this.uploadLocalFileBtn = this.modal.querySelector('.ibexa-user-invitation-modal__file-select');
        this.fileInput = this.modal.querySelector('.ibexa-user-invitation-modal__file-input');
        this.lastScrolledToEntryWithIssue = null;

        this.attachEntryListeners = this.attachEntryListeners.bind(this);
        this.preventDefaultAction = this.preventDefaultAction.bind(this);
        this.handleEntryAdd = this.handleEntryAdd.bind(this);
        this.handleEntryDelete = this.handleEntryDelete.bind(this);
        this.handleDropUpload = this.handleDropUpload.bind(this);
        this.handleInputUpload = this.handleInputUpload.bind(this);
        this.handleSearch = this.handleSearch.bind(this);
        this.handleEmailValidation = this.handleEmailValidation.bind(this);
        this.scrollToNextIssue = this.scrollToNextIssue.bind(this);
    }

    // eslint-disable-next-line no-unused-vars
    processCSVInvitationFile(file) {
        throw new Error('processCSVInvitationFile should be overridden in subclass.');
    }

    countFilledLinesInFile(file) {
        return file.text().then((text) => {
            const nonEmptyLineRegexp = /^([^\r\n]+)$/gm;
            const matchedData = [...text.matchAll(nonEmptyLineRegexp)];

            return matchedData.length;
        });
    }

    resetEntry(entry) {
        this.toggleInvalidEmailState(entry, false);
        this.toggleDuplicateEntryState(entry, false);
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
    checkIsEntryDuplicate(invitationData, entryToCompare) {
        throw new Error('checkIsEntryDuplicate should be overridden in subclass.');
    }

    checkHasEntryIssue(entry) {
        const hasInvalidEmailIssue = !!entry.querySelector('.ibexa-user-invitation-modal__issue-email-not-valid');
        const hasDuplicateIssue = !!entry.querySelector('.ibexa-user-invitation-modal__issue-duplicate');

        return hasInvalidEmailIssue || hasDuplicateIssue;
    }

    findDuplicateEntry(invitationData, entriesToCompare) {
        for (const entryToCompare of entriesToCompare) {
            if (this.checkIsEntryDuplicate(invitationData, entryToCompare)) {
                return entryToCompare;
            }
        }

        return null;
    }

    toggleDuplicateEntryState(entry, isDuplicate) {
        const duplicateEntryError = entry.querySelector('.ibexa-user-invitation-modal__issue-duplicate');

        if (isDuplicate) {
            if (!duplicateEntryError) {
                const template = this.entriesContainer.dataset.duplicateInfoTemplate;
                const entryIssuesContainer = entry.querySelector('.ibexa-user-invitation-modal__entry-issues');

                entryIssuesContainer.insertAdjacentHTML('beforeend', template);
            }
        } else {
            if (duplicateEntryError) {
                duplicateEntryError.remove();
            }
        }
    }

    toggleInvalidEmailState(entry, isError) {
        const invalidEmailError = entry.querySelector('.ibexa-user-invitation-modal__issue-email-not-valid');
        const emailInput = entry.querySelector('.ibexa-user-invitation-modal__email-wrapper .ibexa-input--text');

        if (isError) {
            if (!invalidEmailError) {
                const template = this.entriesContainer.dataset.invalidEmailTemplate;
                const entryIssuesContainer = entry.querySelector('.ibexa-user-invitation-modal__entry-issues');

                entryIssuesContainer.insertAdjacentHTML('afterbegin', template);
            }
        } else {
            if (invalidEmailError) {
                invalidEmailError.remove();
            }
        }

        emailInput.classList.toggle('is-invalid', isError);
    }

    validateEmail(emailInput) {
        const isEmpty = !emailInput.value.trim();
        const isValid = ibexa.errors.emailRegexp.test(emailInput.value);
        const isError = !isEmpty && !isValid;

        return isError;
    }

    validateEntryEmail(entry) {
        const emailInput = entry.querySelector('.ibexa-user-invitation-modal__email-wrapper .ibexa-input--text');
        const isError = this.validateEmail(emailInput);

        this.toggleInvalidEmailState(entry, isError);
        this.manageIssuesAlert();
    }

    handleEmailValidation(event) {
        const emailInput = event.currentTarget;
        const entry = emailInput.closest('.ibexa-user-invitation-modal__entry');

        this.validateEntryEmail(entry);
    }

    prepareIssuesAlert(invalidEmailsCount, duplicateEntryCount) {
        const messages = [];

        if (invalidEmailsCount) {
            const invalidEmailsMessage = Translator.trans(
                /*@Desc("Invalid emails (%count%)")*/ 'modal.entry_issues.alert.invalid_emails',
                { count: invalidEmailsCount },
                'user_invitation',
            );

            messages.push(invalidEmailsMessage);
        }

        if (duplicateEntryCount) {
            const duplicatedEmailsMessage = Translator.trans(
                /*@Desc("Duplicated emails (%count%)")*/ 'modal.entry_issues.alert.duplicate_emails',
                { count: duplicateEntryCount },
                'user_invitation',
            );

            messages.push(duplicatedEmailsMessage);
        }

        this.issuesAlertIssuesContainer.innerText = messages.join(' | ');
    }

    manageIssuesAlert() {
        const invalidEmailsCount = this.entriesContainer.querySelectorAll('.ibexa-user-invitation-modal__issue-email-not-valid').length;
        const duplicateEntryCount = this.entriesContainer.querySelectorAll('.ibexa-user-invitation-modal__issue-duplicate').length;
        const isAnyIssue = invalidEmailsCount || duplicateEntryCount;

        if (isAnyIssue) {
            this.prepareIssuesAlert(invalidEmailsCount, duplicateEntryCount);
        }

        this.toggleIssuesAlert(isAnyIssue);
    }

    toggleIssuesAlert(show) {
        this.issuesAlert.classList.toggle('ibexa-user-invitation-modal__issues-alert--hidden', !show);
    }

    toggleBadFileAlert(show) {
        this.badFileAlert.classList.toggle('ibexa-user-invitation-modal__bad-file-alert--hidden', !show);
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
            this.manageIssuesAlert();
            this.updateModalTitle();
        }
    }

    handleEntryAdd() {
        this.addEntry();
        this.manageIssuesAlert();
        this.updateModalTitle();
    }

    handleEntryDelete(event) {
        const deleteBtn = event.currentTarget;
        const entry = deleteBtn.closest('.ibexa-user-invitation-modal__entry');

        this.deleteEntry(entry);
        this.manageIssuesAlert();
        this.updateModalTitle();
    }

    attachEntryListeners(entry) {
        const deleteEntryBtn = entry.querySelector('.ibexa-user-invitation-modal__entry-delete-btn');
        const emailInput = entry.querySelector('.ibexa-user-invitation-modal__email-wrapper .ibexa-input--text');

        deleteEntryBtn.addEventListener('click', this.handleEntryDelete, false);
        emailInput.addEventListener('blur', this.handleEmailValidation, false);
    }

    getNextEntryWithIssue() {
        const entries = this.entriesContainer.querySelectorAll('.ibexa-user-invitation-modal__entry');
        const firstEntryWithIssue = [...entries].find(this.checkHasEntryIssue);

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

                if (this.checkHasEntryIssue(currentlyCheckedEntry)) {
                    nextEntryWithIssue = currentlyCheckedEntry;
                    break;
                }
            }

            if (!nextEntryWithIssue) {
                nextEntryWithIssue = firstEntryWithIssue;
            }
        }

        return nextEntryWithIssue;
    }

    scrollToNextIssue() {
        const nextEntryWithIssue = this.getNextEntryWithIssue();
        const scrollTopOffset = 124;
        const entryScrollPosition = nextEntryWithIssue.getBoundingClientRect().top + window.pageYOffset - scrollTopOffset;

        this.modal.scrollTo({ top: entryScrollPosition, behavior: 'smooth' });
        this.lastScrolledToEntryWithIssue = nextEntryWithIssue;
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
            /*@Desc("File %fileName% was uploaded")*/ 'modal.file_uploaded.message',
            { fileName },
            'user_invitation',
        );

        ibexa.helpers.notification.showInfoNotification(message);
    }

    clearForm() {
        const entries = this.entriesContainer.querySelectorAll('.ibexa-user-invitation-modal__entry');

        entries.forEach((entry) => this.deleteEntry(entry));
        this.manageIssuesAlert();
        this.updateModalTitle();
        this.toggleUpload(false);
    }

    preventDefaultAction(event) {
        event.preventDefault();
        event.stopPropagation();
    }

    async handleInvitationFile(file) {
        this.toggleUpload(true);
        this.showUploadedFileNotification(file.name);

        const numberOfNonEmptyLines = await this.countFilledLinesInFile(file);
        const invitationsData = await this.processCSVInvitationFile(file);

        if (numberOfNonEmptyLines === 0 || numberOfNonEmptyLines !== invitationsData.length) {
            this.toggleBadFileAlert(true);
            this.toggleUpload(false);

            return;
        }

        this.toggleBadFileAlert(false);
        this.deleteTrailingEntriesIfEmpty();

        const entriesBeforeFileAdded = this.entriesContainer.querySelectorAll('.ibexa-user-invitation-modal__entry');

        invitationsData.forEach((invitationData) => {
            const duplicateEntry = this.findDuplicateEntry(invitationData, entriesBeforeFileAdded);

            if (duplicateEntry) {
                this.toggleDuplicateEntryState(duplicateEntry, true);
                this.manageIssuesAlert();
            } else {
                this.addEntry(true, invitationData);
            }
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

    updateModalTitle() {
        const titleNode = this.modal.querySelector('.modal-title');
        const invitationsCount = this.entriesContainer.querySelectorAll('.ibexa-user-invitation-modal__entry').length;

        titleNode.innerText = Translator.trans(
            /*@Desc("Invite members (%invitations_count%)")*/ 'modal.title',
            { invitations_count: invitationsCount },
            'user_invitation',
        );
    }

    init() {
        this.initialEntries = this.entriesContainer.querySelectorAll('.ibexa-user-invitation-modal__entry');
        this.entryCounter = this.initialEntries.length;

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

        this.badFileAlertCloseBtn.addEventListener('click', () => this.toggleBadFileAlert(false), false);
        this.issuesAlertCloseBtn.addEventListener('click', () => this.toggleIssuesAlert(false), false);
        this.goToNextIssueBtn.addEventListener('click', this.scrollToNextIssue, false);

        this.searchInput.addEventListener('keyup', this.handleSearch, false);
        this.searchBtn.addEventListener('keyup', this.handleSearch, false);

        this.updateModalTitle();
    }
}
