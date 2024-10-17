const getEditLanguageCode = () => {
    return window.document.querySelector('meta[name="LanguageCode"]')?.content;
};

export { getEditLanguageCode };
