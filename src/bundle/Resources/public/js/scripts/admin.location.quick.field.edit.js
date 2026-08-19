import { getRestInfo } from './helpers/context.helper';

(function (global, doc, ibexa, bootstrap, Translator) {
    const SELECTOR_QUICK_EDIT_FIELD = '.ibexa-content-field[data-quick-edit]';
    // The DOM contract, not the class: `.ibexa-content-preview` is also used as a body_class in
    // content_preview.html.twig, and matching that instead would put `undefined` in the REST URL
    // rather than bail out.
    const SELECTOR_FIELDS_WRAPPER = '[data-content-id]';
    const SELECTOR_FIELD_NAME = '.ibexa-content-field__name';
    const SELECTOR_FIELD_VALUE = '.ibexa-content-field__value';
    const SELECTOR_MODAL_TITLE = '.modal-title';
    const SELECTOR_MODAL_EXPLANATION = '.ibexa-modal-body__explanation';
    const SELECTOR_MODAL_DRAFT_LIST = '.modal-body ul';
    const SELECTOR_MODAL_CONFIRM_BTN = '[data-quick-edit-action="confirm"]';
    const SELECTOR_MODAL_CANCEL_BTN = '[data-quick-edit-action="cancel"]';
    const SELECTOR_SESSION_CONTROLS = 'input, textarea, select, button';
    const CLASS_EDITOR = 'ibexa-quick-edit';
    const CLASS_EDITOR_INPUT = 'ibexa-quick-edit__input';
    const CLASS_EDITOR_ACTIONS = 'ibexa-quick-edit__actions';
    // Set on the fields wrapper for the whole save transaction, so a double-click refused on a
    // *different* row while this one is saving still gets visible feedback: aria-busy is only set
    // on the row being opened, never on the row that refused, so it cannot cover this case.
    const CLASS_FIELDS_BUSY = 'ibexa-content-preview--busy';
    const EVENT_MODAL_HIDDEN = 'hidden.bs.modal';
    const KEY_ENTER = 'Enter';
    const KEY_ESCAPE = 'Escape';
    const KEY_SPACE = ' ';
    const REST_PATH_PREFIX = '/api/ibexa/v2';
    // Any `application/vnd.ibexa.api.*+json` media type selects the REST JSON visitor, so a
    // single value is enough for every call here, including the ones whose success is 204 and
    // carries no body at all. What matters is that Accept is never left unset: the visitor
    // regexp list ends with `.*/.*`, so no Accept header resolves to the XML visitor and the
    // user is then shown a JSON parse failure instead of the server's own error message.
    const MEDIA_TYPE_VERSION = 'application/vnd.ibexa.api.Version+json';
    const MEDIA_TYPE_VERSION_LIST = 'application/vnd.ibexa.api.VersionList+json';
    const MEDIA_TYPE_VERSION_UPDATE = 'application/vnd.ibexa.api.VersionUpdate+json';
    const STATUS_FORBIDDEN = 403;
    const VERSION_STATUS_DRAFT = 'DRAFT';

    const quickEditFields = [...doc.querySelectorAll(SELECTOR_QUICK_EDIT_FIELD)];

    // Graceful degradation: a project overriding content_view_fields.html.twig loses the data
    // attributes, so there is nothing to bind. Leave without touching the DOM and without
    // logging anything - no listeners, no console output.
    if (quickEditFields.length === 0) {
        return;
    }

    // The one busy guard. It is held for the whole save transaction, the draft conflict modal
    // included, and every entry point is refused while it is held: both open paths, the confirm
    // path and all three cancel paths. That is what makes two overlapping
    // COPY -> PATCH -> PUBLISH chains impossible, and a fourth entry point added later inherits
    // the guard simply by calling openEditor()/saveSession()/cancelSession() rather than
    // carrying a flag of its own.
    let isBusy = false;
    // Generation token for opens. Opens are deliberately not exclusive - a user retargeting to
    // another field must not be locked out by a slow prefill - so two of them can overlap.
    // Only the newest generation may touch the DOM, which is what stops a superseded open from
    // appending a second editor whose confirm button would then harvest and publish a different
    // field than the one the user typed into.
    let openGeneration = 0;
    // The row whose prefill is in flight, if any. A pending open has no editor and no session yet,
    // so it has nothing for the cancel paths to act on - this is what gives it an abort affordance.
    let pendingOpenNode = null;
    let activeSession = null;

    const getRestBaseUrl = () => `${getRestInfo().instanceUrl}${REST_PATH_PREFIX}`;
    const toAbsoluteUrl = (href) => (href.startsWith(REST_PATH_PREFIX) ? `${getRestInfo().instanceUrl}${href}` : href);
    const getRequestOptions = (method, accept, extraHeaders = {}) => {
        const { token, siteaccess, accessToken, instanceUrl } = getRestInfo();

        return {
            method,
            headers: ibexa.helpers.request.getRequestHeaders({ token, siteaccess, accessToken, accept, extraHeaders }),
            mode: ibexa.helpers.request.getRequestMode({ instanceUrl }),
            credentials: 'same-origin',
        };
    };
    // `errorMessage` only ever holds the HTTP status phrase; the message worth showing is in
    // `errorDescription`, and field validation failures put the per-field messages in
    // `errorDetails.fields[].errors[].message`.
    const getRestErrorMessage = (error = {}) => {
        const fieldErrorMessages = [];

        (error.errorDetails?.fields ?? []).forEach((field) => {
            (field.errors ?? []).forEach((fieldError) => {
                if (fieldError.message) {
                    fieldErrorMessages.push(fieldError.message);
                }
            });
        });

        return fieldErrorMessages.join(' ') || error.errorDescription || error.errorMessage;
    };

    // showErrorNotification() renders whatever it is given, so an Error object must be unwrapped
    // to its message here rather than shown as "Error: <message>". Non-Error values (a plain
    // string, e.g. a validation message) are passed through unchanged.
    const getNotificationMessage = (error) => (error instanceof Error ? error.message : error);

    /**
     * Turns any non-2xx response into an Error carrying the server's own message, and any 2xx
     * one into its status code.
     *
     * Only getJsonFromResponse() accepts a custom error message extractor, and calls whose
     * success is 204 have no body to parse, so success is short-circuited here and the helper is
     * reached only on failure - where it never gets as far as parsing the response as the
     * requested type.
     *
     * @function assertResponseOk
     * @param {Response} response
     * @returns {Promise<Number>}
     */
    const assertResponseOk = async (response) => {
        if (response.ok) {
            return response.status;
        }

        return ibexa.helpers.request.getJsonFromResponse(response, getRestErrorMessage);
    };

    const getEditor = (fieldNode) => ibexa.quickEdit?.editors?.[fieldNode.dataset.fieldTypeIdentifier];
    const getFieldName = (fieldNode) => fieldNode.querySelector(SELECTOR_FIELD_NAME)?.textContent.trim() ?? '';
    const getFieldValidators = (fieldNode) => {
        try {
            return JSON.parse(fieldNode.dataset.fieldValidators ?? '{}');
        } catch {
            return {};
        }
    };

    /**
     * Reads the current version of the content item and returns the REST hash of one field.
     *
     * GET /content/objects/{id}/currentversion answers 307 (ibexa.rest.redirect_current_version)
     * pointing at /versions/{n}. The redirect is followed by fetch itself (its default
     * `redirect: 'follow'`), which for a 307 replays the same method and the same headers, so the
     * Accept header set here still reaches the version endpoint. That is one round trip instead
     * of the two that resolving the version href up front would cost.
     */
    const loadFieldValueHash = async (contentId, fieldDefinitionIdentifier, languageCode) => {
        const response = await fetch(
            `${getRestBaseUrl()}/content/objects/${contentId}/currentversion`,
            getRequestOptions('GET', MEDIA_TYPE_VERSION),
        );
        const data = await ibexa.helpers.request.getJsonFromResponse(response, getRestErrorMessage);
        // Ibexa's JSON generator emits every list through startList()/endList(), so
        // Version.Fields.field is always an array - .find() is safe with no normalisation.
        const fields = data.Version.Fields.field;
        // Matched on the language too, with no fallback to another translation: prefilling from a
        // different language would make the PATCH copy that value into the displayed one, and
        // silently corrupting a translation is far worse than refusing to open.
        const field = fields.find(
            (item) => item.fieldDefinitionIdentifier === fieldDefinitionIdentifier && item.languageCode === languageCode,
        );

        if (field === undefined) {
            throw new Error(
                Translator.trans(
                    /* @Desc("The current value of this field is not available in the language being displayed. Use the full editor instead.") */ 'content.quick_edit.error.missing_field_value',
                    {},
                    'ibexa_locationview',
                ),
            );
        }

        return field.fieldValue;
    };

    const loadDrafts = async (contentId) => {
        const response = await fetch(
            `${getRestBaseUrl()}/content/objects/${contentId}/versions`,
            getRequestOptions('GET', MEDIA_TYPE_VERSION_LIST),
        );
        const data = await ibexa.helpers.request.getJsonFromResponse(response, getRestErrorMessage);

        return data.VersionList.VersionItem.filter((item) => item.VersionInfo.status === VERSION_STATUS_DRAFT);
    };

    /**
     * Branches a new draft off the published version and returns its href.
     *
     * The href comes from the 201 Location header and the 201 body is never parsed. Deriving it
     * from the body would mean a structurally unexpected 201 throws after the draft already
     * exists, with no href left to clean up - a permanent orphan draft.
     */
    const createDraft = async (contentId) => {
        const response = await fetch(
            `${getRestBaseUrl()}/content/objects/${contentId}/currentversion`,
            getRequestOptions('COPY', MEDIA_TYPE_VERSION),
        );

        // A 403 here means the current version already is a draft, i.e. the content item was
        // never published. Permission denial and CSRF failure both come back as 401, so this
        // branch can safely carry a message about the published version.
        if (response.status === STATUS_FORBIDDEN) {
            throw new Error(
                Translator.trans(
                    /* @Desc("This content item has no published version yet. Use the full editor instead.") */ 'content.quick_edit.error.no_published_version',
                    {},
                    'ibexa_locationview',
                ),
            );
        }

        await assertResponseOk(response);

        const draftHref = response.headers.get('Location');

        if (!draftHref) {
            throw new Error(
                Translator.trans(
                    /* @Desc("The server did not return the location of the new draft, so the change was not published.") */ 'content.quick_edit.error.missing_draft_location',
                    {},
                    'ibexa_locationview',
                ),
            );
        }

        return draftHref;
    };

    const patchDraft = async (draftHref, { fieldDefinitionIdentifier, languageCode, fieldValue }) => {
        const options = getRequestOptions('PATCH', MEDIA_TYPE_VERSION, { 'Content-Type': MEDIA_TYPE_VERSION_UPDATE });
        const response = await fetch(toAbsoluteUrl(draftHref), {
            ...options,
            body: JSON.stringify({
                VersionUpdate: {
                    // ContentService::updateContent() falls back to the content item's main
                    // language when initialLanguageCode is absent, which would write the value
                    // into the wrong translation, so it is always sent explicitly.
                    initialLanguageCode: languageCode,
                    fields: {
                        field: [{ fieldDefinitionIdentifier, languageCode, fieldValue }],
                    },
                },
            }),
        });

        return assertResponseOk(response);
    };

    // Symfony's Request::getMethod() honours X-HTTP-Method-Override on a POST, which is the only
    // way to reach the PUBLISH-only route from fetch.
    const publishDraft = async (draftHref) => {
        const response = await fetch(
            toAbsoluteUrl(draftHref),
            getRequestOptions('POST', MEDIA_TYPE_VERSION, { 'X-HTTP-Method-Override': 'PUBLISH' }),
        );

        return assertResponseOk(response);
    };

    const deleteDraft = async (draftHref) => {
        const response = await fetch(toAbsoluteUrl(draftHref), getRequestOptions('DELETE', MEDIA_TYPE_VERSION));

        return assertResponseOk(response);
    };

    const buildDraftConflictModal = (drafts) => {
        const modalNode = doc.createElement('div');

        modalNode.classList.add('modal', 'fade', 'ibexa-modal');
        modalNode.setAttribute('tabindex', '-1');
        modalNode.setAttribute('role', 'dialog');
        modalNode.insertAdjacentHTML(
            'afterbegin',
            `<div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"></h5>
                    </div>
                    <div class="modal-body">
                        <p class="ibexa-modal-body__explanation"></p>
                        <ul></ul>
                        <p class="ibexa-modal-body__explanation"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn ibexa-btn ibexa-btn--primary" data-quick-edit-action="confirm"></button>
                        <button type="button" class="btn ibexa-btn ibexa-btn--secondary" data-quick-edit-action="cancel"></button>
                    </div>
                </div>
            </div>`,
        );

        const [countNode, consequencesNode] = modalNode.querySelectorAll(SELECTOR_MODAL_EXPLANATION);
        const draftListNode = modalNode.querySelector(SELECTOR_MODAL_DRAFT_LIST);

        modalNode.querySelector(SELECTOR_MODAL_TITLE).textContent = Translator.trans(
            /* @Desc("Draft conflict") */ 'content.quick_edit.draft_conflict.title',
            {},
            'ibexa_locationview',
        );
        countNode.textContent =
            drafts.length === 1
                ? Translator.trans(
                      /* @Desc("There is already one draft for this content item:") */ 'content.quick_edit.draft_conflict.number.one',
                      {},
                      'ibexa_locationview',
                  )
                : Translator.trans(
                      /* @Desc("There are already %number% drafts for this content item:") */ 'content.quick_edit.draft_conflict.number.multiple',
                      { number: drafts.length },
                      'ibexa_locationview',
                  );
        consequencesNode.textContent = Translator.trans(
            /* @Desc("Your change will be published in a new, separate draft. The existing drafts stay untouched.") */ 'content.quick_edit.draft_conflict.consequences',
            {},
            'ibexa_locationview',
        );
        drafts.forEach((draft) => {
            const draftNode = doc.createElement('li');

            draftNode.textContent = Translator.trans(
                /* @Desc("Version %versionNo%, last modified %modificationDate%") */ 'content.quick_edit.draft_conflict.draft',
                {
                    versionNo: draft.VersionInfo.versionNo,
                    modificationDate: ibexa.helpers.timezone.formatShortDateTime(draft.VersionInfo.modificationDate),
                },
                'ibexa_locationview',
            );
            draftListNode.append(draftNode);
        });
        modalNode.querySelector(SELECTOR_MODAL_CONFIRM_BTN).textContent = Translator.trans(
            /* @Desc("Continue") */ 'content.quick_edit.draft_conflict.confirm',
            {},
            'ibexa_locationview',
        );
        modalNode.querySelector(SELECTOR_MODAL_CANCEL_BTN).textContent = Translator.trans(
            /* @Desc("Cancel") */ 'content.quick_edit.draft_conflict.cancel',
            {},
            'ibexa_locationview',
        );

        return modalNode;
    };

    /**
     * Asks the user to confirm publishing a new draft while other drafts exist, naming them.
     * Focus trapping and Escape handling are Bootstrap's own; Escape, the backdrop and the
     * Cancel button all resolve to false.
     */
    const confirmDraftConflict = (drafts) =>
        new Promise((resolve) => {
            const modalNode = buildDraftConflictModal(drafts);

            doc.body.append(modalNode);

            const modal = new bootstrap.Modal(modalNode);
            let isConfirmed = false;

            modalNode.querySelector(SELECTOR_MODAL_CONFIRM_BTN).addEventListener(
                'click',
                () => {
                    isConfirmed = true;
                    modal.hide();
                },
                false,
            );
            modalNode.querySelector(SELECTOR_MODAL_CANCEL_BTN).addEventListener('click', () => modal.hide(), false);
            modalNode.addEventListener(
                EVENT_MODAL_HIDDEN,
                () => {
                    modal.dispose();
                    modalNode.remove();
                    resolve(isConfirmed);
                },
                false,
            );
            modal.show();
        });

    const setSessionDisabled = (isDisabled) => {
        if (activeSession === null) {
            return;
        }

        activeSession.editorRow.querySelectorAll(SELECTOR_SESSION_CONTROLS).forEach((control) => {
            control.disabled = isDisabled;
        });
    };

    const restoreAttribute = (node, name, value) => {
        if (value === null) {
            node.removeAttribute(name);

            return;
        }

        node.setAttribute(name, value);
    };

    const closeSession = ({ restoreFocus }) => {
        if (activeSession === null) {
            return;
        }

        // Closing a session also invalidates a prefill still in flight, which would otherwise
        // render, append and steal focus after the user had already cancelled. openEditor() calls
        // this too, but only after its own generation gate, so the bump can never invalidate the
        // open that is in the middle of rendering. A pending open with no session behind it yet is
        // not reachable from here - abortPendingOpen() is what cancels that one.
        openGeneration += 1;

        const { fieldNode, valueNode, editorRow, rowRole, rowTabIndex } = activeSession;

        editorRow.remove();
        valueNode.hidden = false;
        // Restored before focus() - a row with no tabindex cannot take focus back.
        restoreAttribute(fieldNode, 'role', rowRole);
        restoreAttribute(fieldNode, 'tabindex', rowTabIndex);
        activeSession = null;

        if (restoreFocus) {
            fieldNode.focus();
        }
    };

    /**
     * Cancels a prefill that has not rendered yet.
     *
     * Bumping the token makes the pending open fail its own gate, so it renders nothing and stores
     * nothing. Nothing else is touched - in particular this is allowed while the busy guard is
     * held, because dropping a pending open cannot disturb a save transaction.
     *
     * @function abortPendingOpen
     * @returns {void}
     */
    const abortPendingOpen = () => {
        if (pendingOpenNode === null) {
            return;
        }

        openGeneration += 1;
        pendingOpenNode = null;
    };

    const cancelSession = ({ restoreFocus }) => {
        if (isBusy) {
            return;
        }

        // Cancelling this session leaves nothing for another field's in-flight prefill to belong
        // to, so it is aborted here too - otherwise pendingOpenNode would keep pointing at a node
        // whose open already settled, with no pending open left to eventually clear it. Both calls
        // bump openGeneration; a later requestOpen() always captures a fresh generation after this
        // function returns, so a legitimate subsequent open is never invalidated by it.
        abortPendingOpen();
        closeSession({ restoreFocus });
    };

    const saveSession = async () => {
        if (activeSession === null || isBusy) {
            return;
        }

        const { editor, input, contentId, languageCode, fieldDefinitionIdentifier, fieldNode, rowRole, rowTabIndex } = activeSession;
        const validationError = editor.validate?.(input) ?? null;

        if (validationError) {
            ibexa.helpers.notification.showErrorNotification(validationError);
            input.focus();

            return;
        }

        const fieldValue = editor.harvest(input);
        const fieldsWrapper = fieldNode.closest(SELECTOR_FIELDS_WRAPPER);
        let draftHref = null;
        let isPublished = false;

        isBusy = true;
        setSessionDisabled(true);
        // Mirrors isBusy: added the moment the guard is taken, removed only where isBusy itself is
        // reset below, so it is still held into the reload on success just like the guard is.
        fieldsWrapper?.classList.add(CLASS_FIELDS_BUSY);

        try {
            const drafts = await loadDrafts(contentId);

            if (drafts.length > 0 && !(await confirmDraftConflict(drafts))) {
                return;
            }

            draftHref = await createDraft(contentId);

            await patchDraft(draftHref, { fieldDefinitionIdentifier, languageCode, fieldValue });
            await publishDraft(draftHref);

            // Published: the version is no longer a draft, so it must not be deleted.
            draftHref = null;
            isPublished = true;

            ibexa.helpers.notification.showSuccessNotification(
                Translator.trans(/* @Desc("Field updated and published.") */ 'content.quick_edit.success', {}, 'ibexa_locationview'),
            );

            // Restored even though a reload is about to replace the page: if the reload is slow or
            // blocked, the row must not stay inert with an aria-label that names a field on a
            // roleless, non-focusable div. This does not touch the "restore before returning focus"
            // ordering below - there is no focus() call on this path, the page is being replaced.
            restoreAttribute(fieldNode, 'role', rowRole);
            restoreAttribute(fieldNode, 'tabindex', rowTabIndex);

            // Reloading is always correct and refreshes the content tree, the breadcrumb and the
            // version list together with the value. Patching the value node in place instead is
            // known UX debt and a deliberate follow-up, not an oversight here.
            global.location.reload();
        } catch (error) {
            // Never leak a draft: everything that can fail after COPY succeeded lands here, and
            // the draft goes away before the error reaches the user. The cleanup itself is best
            // effort - its own failure must not replace the message explaining what went wrong.
            if (draftHref !== null) {
                await deleteDraft(draftHref).catch(() => {});
            }

            ibexa.helpers.notification.showErrorNotification(getNotificationMessage(error));
        } finally {
            // The guard stays taken on the way to a reload, so nothing can start another
            // transaction against a page that is about to be replaced. Every other way out -
            // a declined draft conflict, a failed call - hands the editor and the focus back,
            // and re-enabling has to come before focus() because a disabled input cannot take it.
            if (!isPublished) {
                setSessionDisabled(false);
                isBusy = false;
                fieldsWrapper?.classList.remove(CLASS_FIELDS_BUSY);
                input.focus();
            }
        }
    };

    const createActionButton = (iconName, label, onClick) => {
        const button = doc.createElement('button');

        button.type = 'button';
        button.className = 'btn ibexa-btn ibexa-btn--ghost ibexa-btn--no-text ibexa-btn--small';
        button.title = label;
        button.setAttribute('aria-label', label);
        button.insertAdjacentHTML(
            'afterbegin',
            `<svg class="ibexa-icon ibexa-icon--small"><use xlink:href="${ibexa.helpers.icon.getIconPath(iconName)}"></use></svg>`,
        );
        button.addEventListener('click', onClick, false);

        return button;
    };

    const buildEditorRow = (input) => {
        const editorRow = doc.createElement('div');
        const actions = doc.createElement('div');
        const confirmButton = createActionButton(
            'check-circle',
            Translator.trans(/* @Desc("Save and publish") */ 'content.quick_edit.confirm_btn.label', {}, 'ibexa_locationview'),
            () => saveSession(),
        );
        const cancelButton = createActionButton(
            'discard',
            Translator.trans(/* @Desc("Discard changes") */ 'content.quick_edit.cancel_btn.label', {}, 'ibexa_locationview'),
            () => cancelSession({ restoreFocus: true }),
        );

        editorRow.classList.add(CLASS_EDITOR);
        actions.classList.add(CLASS_EDITOR_ACTIONS);
        actions.append(confirmButton, cancelButton);
        input.classList.add(CLASS_EDITOR_INPUT);
        editorRow.append(input, actions);
        editorRow.addEventListener(
            'keydown',
            (event) => {
                const isMultiline = event.target.tagName === 'TEXTAREA';

                if (event.key === KEY_ENTER && (!isMultiline || event.ctrlKey || event.metaKey)) {
                    event.preventDefault();
                    saveSession();
                } else if (event.key === KEY_ESCAPE) {
                    event.preventDefault();
                    cancelSession({ restoreFocus: true });
                }
            },
            false,
        );

        return editorRow;
    };

    const openEditor = async (fieldNode) => {
        if (isBusy || activeSession?.fieldNode === fieldNode) {
            return;
        }

        const editor = getEditor(fieldNode);
        const valueNode = fieldNode.querySelector(SELECTOR_FIELD_VALUE);
        const { contentId, languageCode } = fieldNode.closest(SELECTOR_FIELDS_WRAPPER)?.dataset ?? {};
        const { fieldDefinitionIdentifier, fieldTypeIdentifier } = fieldNode.dataset;

        // No editor for this field type, or an overridden template that dropped the value node or
        // part of the data contract: nothing to do, and nothing to complain about either.
        if (!editor || !valueNode || !contentId || !languageCode || !fieldDefinitionIdentifier) {
            return;
        }

        const generation = ++openGeneration;
        let fieldValueHash = null;

        pendingOpenNode = fieldNode;
        fieldNode.setAttribute('aria-busy', 'true');

        try {
            fieldValueHash = await loadFieldValueHash(contentId, fieldDefinitionIdentifier, languageCode);
        } catch (error) {
            ibexa.helpers.notification.showErrorNotification(getNotificationMessage(error));

            return;
        } finally {
            fieldNode.removeAttribute('aria-busy');

            // Only the newest generation may clear the flag: an older, superseded prefill settling
            // late must not report that the open the user is still waiting for has finished.
            if (generation === openGeneration) {
                pendingOpenNode = null;
            }
        }

        // A superseded open stops here, before anything is rendered, appended or stored: the
        // only editor that exists afterwards is the newest one, so the confirm button can only
        // ever harvest the field the user actually typed into.
        //
        // The guard is re-checked as well, because it can have been taken while this prefill was in
        // flight: rendering then would run the unguarded part of closeSession() over the row that is
        // being saved and overwrite activeSession, leaving the transaction's finally block acting on
        // a detached session and the user's typed value gone with nowhere to retry.
        if (generation !== openGeneration || isBusy) {
            return;
        }

        const fieldName = getFieldName(fieldNode);
        // ARIA makes the children of a role="button" element presentational, so an editor row
        // rendered inside the field row can be flattened to a label by conforming assistive tech
        // and its input and buttons never exposed. The row gives up its button semantics for as
        // long as a session lives inside it; closeSession() puts them back.
        const rowRole = fieldNode.getAttribute('role');
        const rowTabIndex = fieldNode.getAttribute('tabindex');
        const input = editor.render(fieldValueHash, {
            validators: getFieldValidators(fieldNode),
            fieldTypeIdentifier,
            fieldName,
        });
        const editorRow = buildEditorRow(input);

        input.setAttribute(
            'aria-label',
            Translator.trans(
                /* @Desc("Edit %fieldName% field") */ 'content.field.quick_edit_aria_label',
                { fieldName },
                'ibexa_locationview',
            ),
        );

        // Replacing any editor already open happens here, atomically with rendering this one, so
        // a failed or superseded open never leaves the user without an editor.
        closeSession({ restoreFocus: false });

        fieldNode.removeAttribute('role');
        fieldNode.removeAttribute('tabindex');
        valueNode.hidden = true;
        valueNode.after(editorRow);

        activeSession = {
            fieldNode,
            valueNode,
            editorRow,
            input,
            editor,
            contentId,
            languageCode,
            fieldDefinitionIdentifier,
            rowRole,
            rowTabIndex,
        };

        input.focus();
    };

    /**
     * Entry point wrapper: openEditor() is fired and forgotten from the listeners, so its rejection
     * has to be handled here. `quickEdit.editors` is an advertised extension point, and a
     * third-party editor whose render() throws must degrade to a visible error instead of a
     * console-only unhandled rejection with a pending open left behind. The stack is still logged
     * to the console alongside the notification, so a throwing render() stays diagnosable.
     *
     * @function requestOpen
     * @param {HTMLElement} fieldNode
     * @returns {void}
     */
    const requestOpen = (fieldNode) => {
        openEditor(fieldNode).catch((error) => {
            abortPendingOpen();
            console.error(error);
            ibexa.helpers.notification.showErrorNotification(getNotificationMessage(error));
        });
    };

    quickEditFields.forEach((fieldNode) => {
        fieldNode.addEventListener(
            'dblclick',
            (event) => {
                // A double click bubbling out of the open editor keeps its native behaviour, so
                // double-click-to-select-a-word still works inside the input.
                if (activeSession?.editorRow.contains(event.target)) {
                    return;
                }

                event.preventDefault();
                global.getSelection()?.removeAllRanges();
                requestOpen(fieldNode);
            },
            false,
        );
        fieldNode.addEventListener(
            'keydown',
            (event) => {
                const isActivationKey = event.key === KEY_ENTER || event.key === KEY_SPACE;

                // Enter and Space both activate, as the WAI-ARIA button pattern requires of the
                // row's role="button". Only the row itself activates: the editor row lives inside
                // it, so its own keys must not bubble up into another open.
                if (!isActivationKey || event.target !== fieldNode) {
                    return;
                }

                // Space would otherwise scroll the page.
                event.preventDefault();
                requestOpen(fieldNode);
            },
            false,
        );
    });

    doc.addEventListener(
        'mousedown',
        (event) => {
            if (activeSession === null) {
                // Clicking away from the row that is still loading cancels it, the same way
                // clicking away from an open editor does. Clicks inside that row are left alone,
                // so the second half of a double click cannot cancel the open it just started.
                if (pendingOpenNode !== null && !pendingOpenNode.contains(event.target)) {
                    abortPendingOpen();
                }

                return;
            }

            if (activeSession.editorRow.contains(event.target)) {
                return;
            }

            cancelSession({ restoreFocus: true });
        },
        false,
    );

    // Escape reaches a pending open wherever the focus is. The editor row has its own Escape
    // handler for a rendered session; this one covers the window before anything is rendered,
    // where there is no editor and no session to cancel.
    doc.addEventListener(
        'keydown',
        (event) => {
            if (event.key === KEY_ESCAPE) {
                abortPendingOpen();
            }
        },
        false,
    );
})(window, window.document, window.ibexa, window.bootstrap, window.Translator);
