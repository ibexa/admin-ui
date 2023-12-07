let { bootstrap,Popper } = window;
let context = window.ibexa?.adminUiConfig;

export const getContext = () => context;
export const getBootstrap = () => bootstrap;
export const getPopper = () => Popper;

export const setContext = (fetchedContext) => (context = fetchedContext);
export const setBootstrap = (bootstrapInstance) => (bootstrap = bootstrapInstance);
export const setPopper = (PopperInstance) => (Popper = PopperInstance);
