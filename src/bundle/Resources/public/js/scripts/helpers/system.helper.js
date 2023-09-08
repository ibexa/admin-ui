(function (global, doc, ibexa) {
    const { userAgent } = window.navigator;
    const isWindows = () => {
        return userAgent.includes('Windows');
    };
    const isMac = () => {
        return userAgent.includes('Mac OS X');
    };
    const isLinux = () => {
        return userAgent.includes('Linux');
    };
    const getDefaultShortcutForLetter = (event, letter) => {
        if (isMac()) {
            return event.metaKey && event.key === letter;
        }

        if (isWindows() || isLinux()) {
            return event.ctrlKey && event.key === letter;
        }

        return false;
    };
    const isUndoPressed = (event) => {
        if (isMac()) {
            return event.metaKey && !event.shiftKey && event.key === 'z';
        }

        if (isWindows() || isLinux()) {
            return event.ctrlKey && event.key === 'y';
        }

        return false;
    };

    const isRedoPressed = (event) => {
        if (isMac()) {
            return event.metaKey && event.shiftKey && event.key === 'z';
        }

        if (isWindows() || isLinux()) {
            return event.ctrlKey && event.key === 'y';
        }

        return false;
    };
    const isSavePressed = (event) => {
        return getDefaultShortcutForLetter(event, 's');
    };
    const isCopyPressed = (event) => {
        return getDefaultShortcutForLetter(event, 'c');
    };
    const isCutPressed = (event) => {
        return getDefaultShortcutForLetter(event, 'x');
    };
    const isPastePressed = (event) => {
        return getDefaultShortcutForLetter(event, 'v');
    };
    const isPrintPressed = (event) => {
        return getDefaultShortcutForLetter(event, 'p');
    };
    const isSelectAllPressed = (event) => {
        return getDefaultShortcutForLetter(event, 'a');
    };

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
    });
})(window, window.document, window.ibexa);
