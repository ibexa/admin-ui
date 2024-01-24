const { userAgent } = window.navigator;
const isChrome = () => userAgent.includes('Chrome');
const isFirefox = () => userAgent.includes('Firefox');
const isSafari = () => userAgent.includes('Safari') && !isChrome();
const isEdge = () => userAgent.includes('Edg');

export { isChrome, isFirefox, isSafari, isEdge };
