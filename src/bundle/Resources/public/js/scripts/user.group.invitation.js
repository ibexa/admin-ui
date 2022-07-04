import { UserInvitationModal } from './user.invitation.modal';

(function (global, doc) {
    const modal = doc.querySelector('.ibexa-user-group-invitation');

    if (!modal) {
        return;
    }

    class UserGroupInvitationModal extends UserInvitationModal {
        processCSVInvitationFile(file) {
            return file.text().then((text) => {
                const lineRegexp = /^([^;\r\n]+)$/gm;
                const matchedData = [...text.matchAll(lineRegexp)];
                const invitationsData = matchedData.map(([email]) => ({ email }));

                return invitationsData;
            });
        }

        resetEntry(entry) {
            const emailInput = entry.querySelector('.ibexa-user-group-invitation__entry-email');

            emailInput.value = null;
        }

        isEntryEmpty(entry) {
            const emailInput = entry.querySelector('.ibexa-user-group-invitation__entry-email');

            return !emailInput.value;
        }

        addEntry(isFileRelated = false, invitationData = null) {
            const { insertedEntry } = super.addEntry(isFileRelated, invitationData);

            const email = invitationData?.email ?? null;
            const emailInput = insertedEntry.querySelector('.ibexa-user-group-invitation__entry-email');

            emailInput.value = email;
        }
    }

    const userInvitationModal = new UserGroupInvitationModal({ modal });

    userInvitationModal.init();
})(window, window.document, window.ibexa);
