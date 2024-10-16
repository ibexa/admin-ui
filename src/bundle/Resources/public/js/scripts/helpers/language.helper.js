const getLanguageCode = () => {
    return window.document.querySelector('meta[name="LanguageCode"]')?.content;
};

export { getLanguageCode };
