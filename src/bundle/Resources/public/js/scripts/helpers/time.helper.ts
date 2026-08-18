import { getAdminUiConfig } from './context.helper';

type TimeDiffUnit = 'minute' | 'hour' | 'day' | 'month' | 'year';

interface TimeDiff {
    count: number;
    unit: TimeDiffUnit;
}

const MINUTE = 60_000;
const HOUR = 3_600_000;
const DAY = 86_400_000;
const MONTH = 2_592_000_000;
const YEAR = 31_536_000_000;

let formatter: Intl.RelativeTimeFormat | null = null;

const getFormatter = (): Intl.RelativeTimeFormat => {
    if (formatter) {
        return formatter;
    }

    const { backOfficeLanguage }: { backOfficeLanguage: string } = getAdminUiConfig();
    const locale = backOfficeLanguage.replace('_', '-') || 'en';

    formatter = new Intl.RelativeTimeFormat(locale, { style: 'short' });

    return formatter;
};

const getTimeDiff = (from: Date | string | number): TimeDiff | null => {
    const timestamp = new Date(from).getTime();

    if (Number.isNaN(timestamp)) {
        return null;
    }

    const diff = Math.max(0, Date.now() - timestamp);

    if (diff < HOUR) {
        return { count: Math.max(1, Math.round(diff / MINUTE)), unit: 'minute' };
    }

    if (diff < DAY) {
        return { count: Math.round(diff / HOUR), unit: 'hour' };
    }

    if (diff < MONTH) {
        return { count: Math.round(diff / DAY), unit: 'day' };
    }

    if (diff < YEAR) {
        return { count: Math.round(diff / MONTH), unit: 'month' };
    }

    return { count: Math.round(diff / YEAR), unit: 'year' };
};

export const formatTimeDiff = (from: Date | string | number): string => {
    const diff = getTimeDiff(from);

    if (diff === null) {
        return '';
    }

    return getFormatter().format(-diff.count, diff.unit);
};
