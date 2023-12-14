const { document: doc } = window;

const getId = () => {
    return doc.querySelector('meta[name="UserId"]')?.content ?? 0;
};

export { getId };
