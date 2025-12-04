const { userAgent } = window.navigator;
const isEdge = () => userAgent.includes('Edg'); // Edge previously had Edge but they changed to Edg
const isChrome = () => userAgent.includes('Chrome') && !isEdge();
const isFirefox = () => userAgent.includes('Firefox');
const isSafari = () => userAgent.includes('Safari') && !isChrome() && !isEdge();
const checkIsClipboardWriteSupported = async () => {
    if (!navigator.clipboard?.writeText) {
        return false;
    }

    const isClipboardWriteSupported = await checkGrantedPermissions('clipboard-write');

    return isClipboardWriteSupported;
};
const checkGrantedPermissions = async (permissionName) => {
    try {
        const result = await navigator.permissions.query({ name: permissionName });

        return result.state === 'granted';
    } catch (error) {
        console.warn(`Permission check failed for "${permissionName}":`, error.message);
        return false;
    }
};

export { isChrome, isFirefox, isSafari, isEdge, checkIsClipboardWriteSupported };
