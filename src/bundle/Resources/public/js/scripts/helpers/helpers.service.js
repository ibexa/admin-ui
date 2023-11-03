let context = window.ibexa?.adminUiConfig;

export const getContext = () => context;

export const setContext = (fetchedContext) => (context = fetchedContext);
