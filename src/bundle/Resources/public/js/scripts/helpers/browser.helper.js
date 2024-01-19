const { userAgent } = window.navigator;
const isChrome = () => {
    return userAgent.indexOf('Safari') != -1 && userAgent.indexOf('Chrome') == -1;
};
const isFirefox = () => {};
const isSafari = () => {
    return userAgent.indexOf('Safari') != -1 && userAgent.indexOf('Chrome') == -1;
};
const isEdge = () => {};

export { isChrome, isFirefox, isSafari, isEdge };
