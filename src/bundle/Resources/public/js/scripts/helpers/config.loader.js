import {
    getContentTypeIconUrl,
    getContentTypeName,
    getContentTypeIconUrlByHref,
    getContentTypeDataByHref,
    getContentTypeNameByHref,
} from './content.type.helper';
import { getCookie, setCookie, setBackOfficeCookie } from './cookies.helper';
import { formatErrorLine, validateIsEmptyField } from './form.validation.helper';
import { getIconPath } from './icon.helper';
import { removeRootFromPathString, findLocationsByIds, buildLocationsBreadcrumbs } from './location.helper';
import { getJsonFromResponse, getTextFromResponse, getStatusFromResponse } from './request.helper';
import { parse as parseTooltips, hideAll as hideAllTooltips, observe as observerTooltips } from './tooltips.helper';
import { convertDateToTimezone, formatFullDateTime, formatShortDateTime, getBrowserTimezone } from './timezone.helper';
import {
    showNotification,
    showInfoNotification,
    showSuccessNotification,
    showWarningNotification,
    showErrorNotification,
} from './notification.helper';
import { parse as parseMiddleEllipsis, parseAll as parseAllMiddleEllipsis, update as updateMiddleEllipsis } from './middle.ellipsis';
import { setInstance, getInstance, clearInstance } from './object.instances';
import { computePages } from './pagination.helper';
import {
    isWindows,
    isMac,
    isLinux,
    isUndoPressed,
    isRedoPressed,
    isSavePressed,
    isCopyPressed,
    isCutPressed,
    isPastePressed,
    isPrintPressed,
    isSelectAllPressed,
    isShortcutWithLetter,
} from './system.helper';
import { parseCheckbox as parseCheckboxTable } from './table.helper';
import { buildItemsFromUDWResponse } from './tag.view.select.helper';
import { escapeHTML } from './text.helper';
import { getId as getUserId } from './user.helper';

(function (ibexa) {
    ibexa.addConfig('helpers.contentType', {
        getContentTypeIconUrl,
        getContentTypeName,
        getContentTypeIconUrlByHref,
        getContentTypeDataByHref,
        getContentTypeNameByHref,
    });
    ibexa.addConfig('helpers.cookies', { getCookie, setCookie, setBackOfficeCookie });
    ibexa.addConfig('helpers.formValidation', { formatErrorLine, validateIsEmptyField });
    ibexa.addConfig('helpers.icon', { getIconPath });
    ibexa.addConfig('helpers.location', { removeRootFromPathString, findLocationsByIds, buildLocationsBreadcrumbs });
    ibexa.addConfig('helpers.request', { getJsonFromResponse, getTextFromResponse, getStatusFromResponse });
    ibexa.addConfig('helpers.tooltips', {
        parse: parseTooltips,
        hideAll: hideAllTooltips,
        observe: observerTooltips,
    });
    ibexa.addConfig('helpers.timezone', { convertDateToTimezone, formatFullDateTime, formatShortDateTime, getBrowserTimezone });
    ibexa.addConfig('helpers.notification', {
        showNotification,
        showInfoNotification,
        showSuccessNotification,
        showWarningNotification,
        showErrorNotification,
    });
    ibexa.addConfig('helpers.ellipsis.middle', {
        parse: parseMiddleEllipsis,
        parseAll: parseAllMiddleEllipsis,
        update: updateMiddleEllipsis,
    });
    ibexa.addConfig('helpers.objectInstances', {
        setInstance,
        getInstance,
        clearInstance,
    });
    ibexa.addConfig('helpers.pagination', { computePages });
    ibexa.addConfig('helpers.system', {
        isWindows: isWindows(),
        isMac: isMac(),
        isLinux: isLinux(),
        isUndoPressed,
        isRedoPressed,
        isSavePressed,
        isCopyPressed,
        isCutPressed,
        isPastePressed,
        isPrintPressed,
        isSelectAllPressed,
        isShortcutWithLetter,
    });
    ibexa.addConfig('helpers.table', { parseCheckbox: parseCheckboxTable });
    ibexa.addConfig('helpers.tagViewSelect', { buildItemsFromUDWResponse });
    ibexa.addConfig('helpers.text', { escapeHTML });
    ibexa.addConfig('helpers.user', { getId: getUserId });
})(window.ibexa);
