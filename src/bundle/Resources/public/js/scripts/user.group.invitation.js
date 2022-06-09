(function (global, doc) {
    const modal = doc.querySelector('.ibexa-user-group-invitation');

    if (!modal) {
        return;
    }

    const processCSVInvitationFile = (file) => {
        return file.text().then((text) => {
            const lineRegexp = /^([^;\r\n]+)$/gm;
            const matchedData = [...text.matchAll(lineRegexp)];
            const invitationsData = matchedData.map(([, email, role]) => [email, role]);

            return invitationsData;
        });
    };
    const handleEntryAdded = (event) => {
        const {
            detail: { insertedEntry, invitationData },
        } = event;
        const email = invitationData?.email ?? null;
        const emailInput = insertedEntry.querySelector('.ibexa-user-invitation-modal__entry-email');

        emailInput.value = email;
    };
    const handleEntryReset = (event) => {
        const {
            detail: { entry },
        } = event;
        const emailInput = entry.querySelector('.ibexa-user-invitation-modal__entry-email');

        emailInput.value = null;
    };
    const handleFileProcess = (event) => {
        const {
            detail: { file, callback },
        } = event;

        processCSVInvitationFile(file).then(callback);
    };

    modal.addEventListener('ibexa-user-invitation-modal:entry-added', handleEntryAdded, false);
    modal.addEventListener('ibexa-user-invitation-modal:reset-entry', handleEntryReset, false);
    modal.addEventListener('ibexa-user-invitation-modal:process-file', handleFileProcess, false);
})(window, window.document, window.ibexa);
