const { userAgent } = window.navigator;
const isEdge = () => userAgent.includes('Edg'); // Edge previously had Edge but they changed to Edg
const isChrome = () => userAgent.includes('Chrome') && !isEdge();
const isFirefox = () => userAgent.includes('Firefox');
const isSafari = () => userAgent.includes('Safari') && !isChrome() && !isEdge();

export { isChrome, isFirefox, isSafari, isEdge };
