const { userAgent } = window.navigator;
const isEdg = () => userAgent.includes('Edg');
const isChrome = () => userAgent.includes('Chrome') && !isEdg();
const isFirefox = () => userAgent.includes('Firefox');
const isSafari = () => userAgent.includes('Safari') && !isChrome() && !isEdg();

export { isChrome, isFirefox, isSafari, isEdg };
