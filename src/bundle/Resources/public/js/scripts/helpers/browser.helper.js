const { userAgent } = window.navigator;
const browserObj = {
    get isChrome() {
        return userAgent.includes('Chrome');
    },
    get isFirefox() {
        return userAgent.includes('Firefox');
    },
    get isSafari() {
        return userAgent.includes('Safari') && !userAgent.includes('Chrome');
    },
    get isEdge() {
        return userAgent.includes('Edg');
    },
};

export const { isChrome, isFirefox, isSafari, isEdge } = browserObj;
