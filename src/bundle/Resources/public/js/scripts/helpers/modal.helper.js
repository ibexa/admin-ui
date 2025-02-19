const controlZIndex = (container) => {
    const initialZIndex = container.style.zIndex;

    container.addEventListener('show.bs.modal', () => {
        container.style.zIndex = 'initial';
    });
    container.addEventListener('hide.bs.modal', () => {
        container.style.zIndex = initialZIndex;
    });

    document.body.dispatchEvent(new CustomEvent('ibexa-control-z-index:events-attached'));
};

export { controlZIndex };
