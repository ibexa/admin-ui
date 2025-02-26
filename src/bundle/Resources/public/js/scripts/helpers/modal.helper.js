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

const betterControlZIndex = (containers, listenerContainer, resetedZIndex = 'initial') => {
    const listenersAbortController = new AbortController();
    const containersInitialZIndexes = new Map();
    const removeControlZIndexListeners = () => {
        listenersAbortController.abort();
        listenerContainer.dispatchEvent(new CustomEvent('ibexa-control-z-index:events-detached'));
    }

    containers.forEach((container) => {
        containersInitialZIndexes.set(container, container.style.zIndex);
    });

    containers.forEach((container) => {
        listenerContainer.addEventListener('show.bs.modal', () => {
            container.style.zIndex = resetedZIndex;
        });
    });

    // listenerContainer.addEventListener('show.bs.modal', () => {
    //     containers.forEach((container) => {
    //         container.style.zIndex = resetedZIndex;
    //     });
    // }, { signal: listenersAbortController.signal });

    listenerContainer.addEventListener('hidden.bs.modal', () => {
        containers.forEach((container) => {
            container.style.zIndex = containersInitialZIndexes.get(container);
        });
    }, { signal: listenersAbortController.signal });

    listenerContainer.dispatchEvent(new CustomEvent('ibexa-control-z-index:events-attached'));

    return {
        removeControlZIndexListeners,
    }
}

export { controlZIndex, betterControlZIndex };
