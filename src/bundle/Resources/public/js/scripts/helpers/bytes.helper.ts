const formatBytesToLargestUnit = (bytes = 0): string => {
    const units = ['B', 'KB', 'MB', 'GB'];
    const kilobyte = 1024;
    const twoDigitsThreshold = 10;

    let size = bytes;
    let unitIndex = 0;

    while (size >= kilobyte) {
        size = size / kilobyte;
        unitIndex++;
    }

    const decimalUnits = unitIndex < 1 ? 0 : 1;
    const sizeFixed = size.toFixed(Number(size >= twoDigitsThreshold || decimalUnits));

    return `${sizeFixed} ${units[unitIndex]}`;
};

export { formatBytesToLargestUnit };
