const controlZIndex = (container) => {
    const initialZIndex = container.style.zIndex;

    container.addEventListener('show.bs.modal', () => {
        container.style.zIndex = 'initial';
    });
    container.addEventListener('hide.bs.modal', () => {
        container.style.zIndex = initialZIndex;
    });
};

export { controlZIndex };
