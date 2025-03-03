import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import PropTypes from 'prop-types';

import { createCssClassNames } from '../helpers/css.class.names';

const Table = ({ extraClasses, children, isLastColumnSticky }) => {
    const scrollableWrapperRef = useRef(null);
    const [hasLastColumnShadow, setHasLastColumnShadow] = useState(false);
    const className = createCssClassNames({
        'ibexa-table table': true,
        'ibexa-table--last-column-sticky': isLastColumnSticky,
        'ibexa-table--last-column-shadow': isLastColumnSticky && hasLastColumnShadow,
        [extraClasses]: true,
    });
    const updateLastColumnShadowState = useCallback(() => {
        const offsetRoundingCompensator = 0.5;
        const shouldShowRightColumnShadow =
            scrollableWrapperRef.current.scrollLeft <
            scrollableWrapperRef.current.scrollWidth - scrollableWrapperRef.current.offsetWidth - 2 * offsetRoundingCompensator;

        setHasLastColumnShadow(shouldShowRightColumnShadow);
    }, [scrollableWrapperRef, setHasLastColumnShadow]);
    const scrollableWrapperResizeObserver = useMemo(
        () =>
            new ResizeObserver(() => {
                updateLastColumnShadowState();
            }),
        [updateLastColumnShadowState],
    );

    useEffect(() => {
        if (isLastColumnSticky) {
            updateLastColumnShadowState();
            scrollableWrapperRef.current?.addEventListener('scroll', updateLastColumnShadowState, false);

            return () => {
                scrollableWrapperRef.current?.removeEventListener('scroll', updateLastColumnShadowState, false);
            };
        }
    }, [isLastColumnSticky, updateLastColumnShadowState]);

    return (
        <div
            className="ibexa-scrollable-wrapper"
            ref={(ref) => {
                if (!ref || (scrollableWrapperRef.current !== null && scrollableWrapperRef.current !== ref)) {
                    scrollableWrapperResizeObserver.unobserve(scrollableWrapperRef.current);
                }

                scrollableWrapperRef.current = ref;

                if (ref) {
                    scrollableWrapperResizeObserver.observe(ref);

                    if (isLastColumnSticky) {
                        updateLastColumnShadowState();
                        ref.addEventListener('scroll', updateLastColumnShadowState, false);
                    }
                }
            }}
        >
            <table className={className}>{children}</table>
        </div>
    );
};

Table.propTypes = {
    extraClasses: PropTypes.string,
    children: PropTypes.element,
    isLastColumnSticky: PropTypes.bool,
};

Table.defaultProps = {
    extraClasses: '',
    children: null,
    isLastColumnSticky: false,
};

export default Table;
