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

const showModalLoader = ({ headerText, descriptionText, modalNode }) => {
    if (!modalNode) {
        return;
    }

    const modalDialog = modalNode.querySelector('.modal-dialog');
    const headerNode = modalNode.querySelector('.modal-header');
    const loaderNode = modalNode.querySelector('.ibexa-modal__loader');
    const headerRect = headerNode ? headerNode.getBoundingClientRect() : { height: 0 };
    const dialogRect = modalDialog.getBoundingClientRect();
    const { height: dialogHeight, width: dialogWidth } = dialogRect;
    const { height: headerHeight } = headerRect;

    loaderNode.style.height = `${dialogHeight - headerHeight}px`;
    loaderNode.style.width = `${dialogWidth}px`;
    loaderNode.style.top = `${headerHeight}px`;

    if (headerText) {
        const headerTextNode = modalNode.querySelector('.ibexa-modal__loader-header-text');

        headerTextNode.innerText = headerText;
    }

    if (descriptionText) {
        const descriptionTextNode = modalNode.querySelector('.ibexa-modal__loader-description-text');

        descriptionTextNode.innerText = descriptionText;
    }

    modalNode.classList.add('ibexa-modal--with-blurred-loader');
};

const hideModalLoader = ({ modalNode }) => {
    if (!modalNode) {
        return;
    }

    const headerTextNode = modalNode.querySelector('.ibexa-modal__loader-header-text');
    const descriptionTextNode = modalNode.querySelector('.ibexa-modal__loader-description-text');

    headerTextNode.innerText = '';
    descriptionTextNode.innerText = '';

    modalNode.classList.remove('ibexa-modal--with-blurred-loader');
};

document.body.addEventListener('hidden.bs.modal', (event) => {
    hideModalLoader({ modalNode: event.target });
});

export { controlZIndex, controlManyZIndexes, showModalLoader, hideModalLoader };
