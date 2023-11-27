let { bootstrap, flatpickr, moment, Popper, Routing, Translator } = window;
let adminUiConfig = window.ibexa?.adminUiConfig;
let token = document.querySelector('meta[name="CSRF-Token"]')?.content;
let siteaccess = document.querySelector('meta[name="SiteAccess"]')?.content;

export const setToken = (loadedToken) => (token = loadedToken);
export const setSiteaccess = (loadedSiteaccess) => (siteaccess = loadedSiteaccess);
export const setAdminUiConfig = (loadedAdminUiConfig) => (adminUiConfig = loadedAdminUiConfig);
export const setBootstrap = (bootstrapInstance) => (bootstrap = bootstrapInstance);
export const setFlatpickr = (flatpickrInstance) => (flatpickr = flatpickrInstance);
export const setMoment = (momentInstance) => (moment = momentInstance);
export const setPopper = (PopperInstance) => (Popper = PopperInstance);
export const setRouting = (RoutingInstance) => (Routing = RoutingInstance);
export const setTranslator = (TranslatorInstance) => (Translator = TranslatorInstance);

export const getToken = () => token;
export const getSiteaccess = () => siteaccess;
export const getAdminUiConfig = () => adminUiConfig;
export const getBootstrap = () => bootstrap;
export const getFlatpickr = () => flatpickr;
export const getMoment = () => moment;
export const getPopper = () => Popper;
export const getRouting = () => Routing;
export const getTranslator = () => Translator;
