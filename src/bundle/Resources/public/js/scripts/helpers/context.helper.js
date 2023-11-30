let { bootstrap, flatpickr, moment, Popper, Routing, Translator } = window;
let adminUiConfig = window.ibexa?.adminUiConfig;
let token = document.querySelector('meta[name="CSRF-Token"]')?.content;
let siteaccess = document.querySelector('meta[name="SiteAccess"]')?.content;
let restInfo = {
    accessToken: null,
    instanceUrl: window.location.origin,
    token: document.querySelector('meta[name="CSRF-Token"]')?.content,
    siteaccess: document.querySelector('meta[name="SiteAccess"]')?.content,
};

export const setRestInfo = ({ instanceUrl, token, csrfToken, siteaccess }) => {
    restInfo.instanceUrl = restInfo.instanceUrl ?? instanceUrl;
    restInfo.token = restInfo.token ?? token;
    restInfo.csrfToken = restInfo.csrfToken ?? csrfToken;
    restInfo.siteaccess = restInfo.siteaccess ?? siteaccess;
};
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
export const getRestInfo = () => restInfo;
