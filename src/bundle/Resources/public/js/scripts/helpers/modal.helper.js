const controlZIndex = (container, listenerContainer, resetedZIndex = 'initial') => {
    const initialZIndex = container.style.zIndex;
    const finalListenerContainer = listenerContainer ?? container;
    const handleShowModal = () => {
        container.style.zIndex = resetedZIndex;
    };
    const handleCloseModal = () => {
        container.style.zIndex = initialZIndex;
    };
    const removeControlZIndexListeners = () => {
        finalListenerContainer.removeEventListener('show.bs.modal', handleShowModal, false);
        finalListenerContainer.removeEventListener('hidden.bs.modal', handleCloseModal, false);

        document.body.dispatchEvent(new CustomEvent('ibexa-control-z-index:events-detached'));
    };

    finalListenerContainer.addEventListener('show.bs.modal', handleShowModal, false);
    finalListenerContainer.addEventListener('hidden.bs.modal', handleCloseModal, false);

    document.body.dispatchEvent(new CustomEvent('ibexa-control-z-index:events-attached'));

    return {
        removeControlZIndexListeners,
    };
};

const controlZIndexBulk = (items) => {
    const storedControlZIndex = items.map((item) => {
        return controlZIndex(...item);
    });
    const removeControlZIndexListeners = () => {
        storedControlZIndex.forEach((item) => {
            item.removeControlZIndexListeners();
        });
    };

    return {
        removeControlZIndexListeners,
    };
};

export { controlZIndex, controlZIndexBulk };
