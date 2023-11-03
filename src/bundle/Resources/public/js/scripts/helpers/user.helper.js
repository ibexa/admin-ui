const { document: doc } = window;

const getId = () => doc.querySelector('meta[name="UserId"]').content;

export { getId };
