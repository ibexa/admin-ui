import React, { useRef, createContext, useState, useEffect } from 'react';
import PropTypes from 'prop-types';

import { getRootDOMElement } from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/context.helper';
import { createCssClassNames } from '../helpers/css.class.names';

export const DraggableContext = createContext();

const DraggableDialog = ({ children, initialCoords }) => {
    const rootDOMElement = getRootDOMElement();
    const containerRef = useRef();
    const dragOffsetPosition = useRef({ x: 0, y: 0 });
    const containerSize = useRef({ width: 0, height: 0 });
    const [isDragging, setIsDragging] = useState(false);
    const [coords, setCoords] = useState(initialCoords);
    const containerAttrs = {
        ref: containerRef,
        className: 'c-draggable-dialog',
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
            rootDOMElement.addEventListener('mousemove', handleDragging);
            rootDOMElement.addEventListener('mouseup', stopDragging);
        }

        return () => {
            rootDOMElement.removeEventListener('mousemove', handleDragging);
            rootDOMElement.removeEventListener('mouseup', stopDragging);
        };
    }, [isDragging]);

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
    initialCoords: PropTypes.shape({
        x: PropTypes.number.isRequired,
        y: PropTypes.number.isRequired,
    }).isRequired,
    children: PropTypes.node,
};

DraggableDialog.defaultProps = {
    children: null,
};

export default DraggableDialog;
