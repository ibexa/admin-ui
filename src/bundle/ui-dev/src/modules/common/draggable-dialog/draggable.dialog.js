import React, { useRef, createContext, useState, useEffect } from 'react';
import PropTypes from 'prop-types';

import { getRootDOMElement } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { createCssClassNames } from '@ibexa-admin-ui/src/bundle/ui-dev/src/modules/common/helpers/css.class.names';

export const DraggableContext = createContext();

const DraggableDialog = ({ children, referenceElement, positionOffset }) => {
    const rootDOMElement = getRootDOMElement();
    const containerRef = useRef();
    const dragOffsetPosition = useRef({ x: 0, y: 0 });
    const containerSize = useRef({ width: 0, height: 0 });
    const [isDragging, setIsDragging] = useState(false);
    const [coords, setCoords] = useState({ x: null, y: null });
    const dialogClasses = createCssClassNames({
        'c-draggable-dialog': true,
        'c-draggable-dialog--hidden': coords.x === null || coords.y === null,
    });
    const containerAttrs = {
        ref: containerRef,
        className: dialogClasses,
        style: {
            top: coords.y,
            left: coords.x,
        },
    };
    const getMousePosition = (event) => ({ x: event.x, y: event.y });
    const setContainerCoords = (event) => {
        const mouseCoords = getMousePosition(event);
        let x = mouseCoords.x - dragOffsetPosition.current.x;
        let y = mouseCoords.y - dragOffsetPosition.current.y;
        let newDragOffsetX;
        let newDragOffsetY;

        if (x < 0) {
            x = 0;
            newDragOffsetX = mouseCoords.x;
        } else if (x + containerSize.current.width > window.innerWidth) {
            x = window.innerWidth - containerSize.current.width;
            newDragOffsetX = mouseCoords.x - x;
        }

        if (y < 0) {
            y = 0;
            newDragOffsetY = mouseCoords.y;
        } else if (y + containerSize.current.height > window.innerHeight) {
            y = window.innerHeight - containerSize.current.height;
            newDragOffsetY = mouseCoords.y - y;
        }

        if (newDragOffsetX) {
            dragOffsetPosition.current.x = newDragOffsetX;
        }

        if (newDragOffsetY) {
            dragOffsetPosition.current.y = newDragOffsetY;
        }

        setCoords({
            x,
            y,
        });
    };
    const startDragging = (event) => {
        const { x: containerX, y: containerY, width, height } = containerRef.current.getBoundingClientRect();
        const mouseCoords = getMousePosition(event.nativeEvent);

        dragOffsetPosition.current = {
            x: mouseCoords.x - containerX,
            y: mouseCoords.y - containerY,
        };

        containerSize.current = {
            width,
            height,
        };

        setContainerCoords(event.nativeEvent);

        setIsDragging(true);
    };
    const stopDragging = () => {
        setIsDragging(false);
    };
    const handleDragging = (event) => {
        setContainerCoords(event);
    };

    useEffect(() => {
        if (isDragging) {
            rootDOMElement.addEventListener('mousemove', handleDragging, false);
            rootDOMElement.addEventListener('mouseup', stopDragging, false);
        }

        return () => {
            rootDOMElement.removeEventListener('mousemove', handleDragging);
            rootDOMElement.removeEventListener('mouseup', stopDragging);
        };
    }, [isDragging]);

    useEffect(() => {
        const { top: referenceTop, left: referenceLeft } = referenceElement.getBoundingClientRect();
        const { width: containerWidth, height: containerHeight } = containerRef.current.getBoundingClientRect();
        const { x: offsetX, y: offsetY } = positionOffset(referenceElement);
        let x = referenceLeft + offsetX;
        let y = referenceTop + offsetY;

        if (x < 0) {
            x = 0;
        } else if (x + containerWidth > window.innerWidth) {
            x = window.innerWidth - containerWidth;
        }

        if (y < 0) {
            y = 0;
        } else if (y + containerHeight > window.innerHeight) {
            y = window.innerHeight - containerHeight;
        }

        setCoords({
            x,
            y,
        });
    }, [referenceElement]);

    return (
        <DraggableContext.Provider
            value={{
                startDragging,
            }}
        >
            <div {...containerAttrs}>{children}</div>
        </DraggableContext.Provider>
    );
};

DraggableDialog.propTypes = {
    referenceElement: PropTypes.node.isRequired,
    children: PropTypes.node,
    positionOffset: PropTypes.func,
};

DraggableDialog.defaultProps = {
    children: null,
    positionOffset: () => ({ x: 0, y: 0 }),
};

export default DraggableDialog;
