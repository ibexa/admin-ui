import { getAdminUiConfig } from './context.helper';

const { document: doc } = window;

const getId = () => {
    const { user } = getAdminUiConfig();
    const metaUserId = doc.querySelector('meta[name="UserId"]')?.content;
    const userId = metaUserId ?? user.user?.User;

    return userId ? parseInt(userId, 10) : 0;
};

export { getId };
