import { useEffect, useState } from 'react';

import { formatTimeDiff } from './time.diff.helper';

const TICK_INTERVAL = 60_000;
const subscribers = new Set<() => void>();

let intervalId: ReturnType<typeof setInterval> | null = null;

const subscribe = (notify: () => void): (() => void) => {
    subscribers.add(notify);

    intervalId ??= setInterval(() => {
        subscribers.forEach((subscriber) => {
            subscriber();
        });
    }, TICK_INTERVAL);

    return () => {
        subscribers.delete(notify);

        if (subscribers.size === 0 && intervalId !== null) {
            clearInterval(intervalId);
            intervalId = null;
        }
    };
};

export const useTimeDiffLabel = (from: Date | string | number): string => {
    const [label, setLabel] = useState(() => formatTimeDiff(from));

    useEffect(() => {
        const update = (): void => {
            setLabel(formatTimeDiff(from));
        };

        update();

        return subscribe(update);
    }, [from]);

    return label;
};
