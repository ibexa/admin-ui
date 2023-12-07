let { bootstrap, flatpickr, moment, Popper, Routing, Translator } = window;
let adminUiConfig = window.ibexa?.adminUiConfig;
const restInfo = {
    accessToken: null,
    instanceUrl: window.location.origin,
    token: document.querySelector('meta[name="CSRF-Token"]')?.content,
    siteaccess: document.querySelector('meta[name="SiteAccess"]')?.content,
};

export const setRestInfo = ({ instanceUrl, token, csrfToken, siteaccess }) => {
    restInfo.instanceUrl = instanceUrl ?? restInfo.instanceUrl;
    restInfo.token = token ?? restInfo.token;
    restInfo.csrfToken = csrfToken ?? restInfo.csrfToken;
    restInfo.siteaccess = siteaccess ?? restInfo.siteaccess;
};
export const setAdminUiConfig = (loadedAdminUiConfig) => (adminUiConfig = loadedAdminUiConfig);
export const setBootstrap = (bootstrapInstance, forceSet = false) => {
    if (!bootstrap || forceSet) {
        bootstrap = bootstrapInstance;
    }
};
export const setFlatpickr = (flatpickrInstance, forceSet = false) => {
    if (!flatpickr || forceSet) {
        flatpickr = flatpickrInstance;
    }
};
export const setMoment = (momentInstance, forceSet = false) => {
    if (!moment || forceSet) {
        moment = momentInstance;
    }
};
export const setPopper = (PopperInstance, forceSet = false) => {
    if (!Popper || forceSet) {
        Popper = PopperInstance;
    }
};
export const setRouting = (RoutingInstance, forceSet = false) => {
    if (!Routing || forceSet) {
        Routing = RoutingInstance;
    }
};
export const setTranslator = (TranslatorInstance, forceSet = false) => {
    if (!Translator || forceSet) {
        Translator = TranslatorInstance;
    }
};

export const getAdminUiConfig = () => adminUiConfig;
export const getBootstrap = () => bootstrap;
export const getFlatpickr = () => flatpickr;
export const getMoment = () => moment;
export const getPopper = () => Popper;
export const getRouting = () => Routing;
export const getTranslator = () => Translator;
export const getRestInfo = () => restInfo;
