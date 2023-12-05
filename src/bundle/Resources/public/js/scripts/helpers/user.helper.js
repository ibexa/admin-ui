const { document: doc } = window;

const getId = () => {
    doc.querySelector('meta[name="UserId"]')?.content ?? 0;
}

export { getId };
