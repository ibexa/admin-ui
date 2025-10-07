const controlZIndex = (container) => {
    const initialZIndex = container.style.zIndex;
	 container.style.zIndex = 'initial';

    container.addEventListener('show.bs.modal', () => {
        container.style.zIndex = 'initial';
    });
    container.addEventListener('hide.bs.modal', () => {
        container.style.zIndex = initialZIndex;
    });

    document.body.dispatchEvent(new CustomEvent('ibexa-control-z-index:events-attached'));
};

const controlManyZIndexes = (items, listenerContainer) => {
    const listenersAbortController = new AbortController();
    const containersInitialZIndexes = new Map();
    const removeControlManyZIndexesListeners = () => {
        listenersAbortController.abort();
        listenerContainer.dispatchEvent(new CustomEvent('ibexa-control-z-index:events-detached'));
    };

    items.forEach(({ container }) => {
        containersInitialZIndexes.set(container, container.style.zIndex);
    });

    listenerContainer.addEventListener(
        'show.bs.modal',
        () => {
            items.forEach(({ container, zIndex = 'initial' }) => {
                container.style.zIndex = zIndex;
            });
        },
        { signal: listenersAbortController.signal },
    );

    listenerContainer.addEventListener(
        'hide.bs.modal',
        () => {
            items.forEach(({ container }) => {
                container.style.zIndex = containersInitialZIndexes.get(container);
            });
        },
        { signal: listenersAbortController.signal },
    );

    listenerContainer.dispatchEvent(new CustomEvent('ibexa-control-z-index:events-attached'));

    return {
        removeControlManyZIndexesListeners,
    };
};

export { controlZIndex, controlManyZIndexes };
