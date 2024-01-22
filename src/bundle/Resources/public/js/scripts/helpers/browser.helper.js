const { userAgent } = window.navigator;
const isChrome = () => userAgent.indexOf('Chrome') != -1;
const isFirefox = () => userAgent.indexOf('Firefox') != -1;
const isSafari = () => userAgent.indexOf('Safari') != -1 && userAgent.indexOf('Chrome') == -1;
const isEdge = () => userAgent.indexOf('Edg') !== -1;

export { isChrome, isFirefox, isSafari, isEdge };
