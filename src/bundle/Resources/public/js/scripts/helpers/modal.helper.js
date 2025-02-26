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

const betterControlZIndex = (items, listenerContainer) => {
    const listenersAbortController = new AbortController();
    const containersInitialZIndexes = new Map();
    const removeControlZIndexListeners = () => {
        listenersAbortController.abort();
        listenerContainer.dispatchEvent(new CustomEvent('ibexa-control-z-index:events-detached'));
    }

    items.forEach(({ container }) => {
        containersInitialZIndexes.set(container, container.style.zIndex);
    });

    listenerContainer.addEventListener('show.bs.modal', () => {
        items.forEach(({ container, zIndex = 'initial' }) => {
            container.style.zIndex = zIndex;
        });
    }, { signal: listenersAbortController.signal });

    listenerContainer.addEventListener('hidden.bs.modal', () => {
        items.forEach(({ container }) => {
            container.style.zIndex = containersInitialZIndexes.get(container);
        });
    }, { signal: listenersAbortController.signal });

    listenerContainer.dispatchEvent(new CustomEvent('ibexa-control-z-index:events-attached'));

    return {
        removeControlZIndexListeners,
    }
}

export { controlZIndex, betterControlZIndex };
