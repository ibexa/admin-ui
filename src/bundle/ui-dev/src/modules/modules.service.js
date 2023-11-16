let token = document.querySelector('meta[name="CSRF-Token"]')?.content;
let siteaccess = document.querySelector('meta[name="SiteAccess"]')?.content;
let RoutingInstance = window.Routing;
let TranslatorInstance = window.Translator;
let adminUiConfig = window.ibexa?.adminUiConfig;

export const setToken = (loadedToken) => (token = loadedToken);
export const setSiteaccess = (loadedSiteaccess) => (siteaccess = loadedSiteaccess);
export const setRouting = (loadedRoutingInstance) => (RoutingInstance = loadedRoutingInstance);
export const setAdminUiConfig = (loadedAdminUiConfig) => (adminUiConfig = loadedAdminUiConfig);
export const setTranslator = (loadedTranslatorInstance) => (TranslatorInstance = loadedTranslatorInstance);

export const getToken = () => token;
export const getSiteaccess = () => siteaccess;
export const getRouting = () => RoutingInstance;
export const getAdminUiConfig = () => adminUiConfig;
export const getTranslator = () => TranslatorInstance;
