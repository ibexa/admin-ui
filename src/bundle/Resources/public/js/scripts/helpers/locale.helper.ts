import { getAdminUiConfig } from './context.helper';

const getBackOfficeLocale = (): string => {
    const { backOfficeLanguage }: { backOfficeLanguage: string } = getAdminUiConfig();

    return backOfficeLanguage.replaceAll('_', '-') || 'en';
};

export { getBackOfficeLocale };
