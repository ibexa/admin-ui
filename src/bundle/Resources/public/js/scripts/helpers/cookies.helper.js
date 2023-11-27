import { getAdminUiConfig } from './context.helper';

const { document: doc } = window;

const setBackOfficeCookie = (name, value, maxAgeDays = 356, path = getAdminUiConfig().backOfficePath) => {
    setCookie(name, value, maxAgeDays, path);
};
const setCookie = (name, value, maxAgeDays = 356, path = '/') => {
    const maxAge = maxAgeDays * 24 * 60 * 60;

    doc.cookie = `${name}=${value};max-age=${maxAge};path=${path}`;
};
const getCookie = (name) => {
    const decodedCookie = decodeURIComponent(doc.cookie);
    const cookiesArray = decodedCookie.split(';');

    const cookieValue = cookiesArray.find((cookie) => {
        const cookieString = cookie.trim();
        const seachingString = `${name}=`;

        return cookieString.indexOf(seachingString) === 0;
    });

    return cookieValue ? cookieValue.split('=')[1] : null;
};

export { getCookie, setCookie, setBackOfficeCookie };
