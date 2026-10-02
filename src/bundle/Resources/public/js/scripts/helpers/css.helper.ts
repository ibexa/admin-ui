const calculateRem = (sizeInPx: number): string => {
    const rootFontSize = parseFloat(getComputedStyle(document.documentElement).fontSize);

    return `${sizeInPx / rootFontSize}rem`;
};

export { calculateRem };
