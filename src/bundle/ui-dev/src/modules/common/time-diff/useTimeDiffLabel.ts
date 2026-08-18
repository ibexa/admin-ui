import { useEffect, useState } from 'react';

import { formatTimeDiff } from '../../../../../Resources/public/js/scripts/helpers/time.helper';

const TICK_INTERVAL = 60_000;
const subscribers = new Set<() => void>();

let intervalId: ReturnType<typeof setInterval> | null = null;

const notifyAllSubscribers = (): void => {
    subscribers.forEach((notifySubscriber) => {
        notifySubscriber();
    });
};

const addSubscriber = (notify: () => void): void => {
    subscribers.add(notify);

    intervalId ??= setInterval(notifyAllSubscribers, TICK_INTERVAL);
};

const removeSubscriber = (notify: () => void): void => {
    subscribers.delete(notify);

    if (subscribers.size === 0 && intervalId !== null) {
        clearInterval(intervalId);
        intervalId = null;
    }
};

export const useTimeDiffLabel = (from: Date | string | number): string => {
    const [label, setLabel] = useState(() => formatTimeDiff(from));

    useEffect(() => {
        const updateLabel = (): void => {
            setLabel(formatTimeDiff(from));
        };

        updateLabel();
        addSubscriber(updateLabel);

        return () => {
            removeSubscriber(updateLabel);
        };
    }, [from]);

    return label;
};
