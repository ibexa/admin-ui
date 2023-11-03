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
const isShortcutWithLetter = (event, letter) => {
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
        return event.ctrlKey && event.key === 'z';
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
    return isShortcutWithLetter(event, 's');
};
const isCopyPressed = (event) => {
    return isShortcutWithLetter(event, 'c');
};
const isCutPressed = (event) => {
    return isShortcutWithLetter(event, 'x');
};
const isPastePressed = (event) => {
    return isShortcutWithLetter(event, 'v');
};
const isPrintPressed = (event) => {
    return isShortcutWithLetter(event, 'p');
};
const isSelectAllPressed = (event) => {
    return isShortcutWithLetter(event, 'a');
};

export {
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
};
