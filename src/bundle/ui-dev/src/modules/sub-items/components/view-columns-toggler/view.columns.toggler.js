import React, { Component, createRef } from 'react';
import { createPortal } from 'react-dom';
import PropTypes from 'prop-types';

import Icon from '../../../common/icon/icon';
import ViewColumnsTogglerListElement from './view.columns.toggler.list.element';
import { columnsLabels } from '../../sub.items.module';
import { createCssClassNames } from '../../../common/helpers/css.class.names';

const { document, Translator, Popper } = window;

export default class ViewColumnsTogglerComponent extends Component {
    constructor(props) {
        super(props);

        this.togglePanel = this.togglePanel.bind(this);
        this.hidePanel = this.hidePanel.bind(this);

        this._refTogglerButton = createRef();
        this._refPanel = createRef();

        this.state = {
            isOpen: false,
        };
    }

    componentDidMount() {
        document.addEventListener('click', this.hidePanel, false);

        this.popperInstance = new Popper.createPopper(this._refTogglerButton.current, this._refPanel.current, {
            placement: 'bottom-end',
            modifiers: [
                {
                    name: 'flip',
                    enabled: true,
                    options: {
                        fallbackPlacements: ['top-end'],
                        boundary: document.body,
                    },
                },
                {
                    name: 'offset',
                    options: {
                        offset: [0, 12],
                    },
                },
            ],
        });
    }

    componentWillUnmount() {
        document.removeEventListener('click', this.hidePanel);

        this.popperInstance.destroy();
    }

    hidePanel({ target }) {
        if (!this.state.isOpen) {
            return;
        }

        const isClickInsideToggler = this._refTogglerButton.current.contains(target);
        const isClickInsidePopup = this._refPanel.current.contains(target);

        if (!isClickInsideToggler && !isClickInsidePopup) {
            this.setState(() => ({
                isOpen: false,
            }));
        }
    }

    togglePanel() {
        this.setState(
            (state) => ({
                isOpen: !state.isOpen,
            }),
            () => {
                this.popperInstance.update();
            },
        );
    }

    renderPanel() {
        const { columnsVisibility, toggleColumnVisibility } = this.props;
        const { isOpen } = this.state;
        const className = createCssClassNames({
            'ibexa-popup-menu': true,
            'c-view-columns-toggler__panel': true,
            'c-view-columns-toggler__panel--hidden': !isOpen,
        });

        return createPortal(
            <ul className={className} ref={this._refPanel}>
                {Object.entries(columnsVisibility).map(([columnKey, isColumnVisible]) => {
                    const label = columnsLabels[columnKey];

                    return (
                        <ViewColumnsTogglerListElement
                            key={columnKey}
                            label={label}
                            columnKey={columnKey}
                            isColumnVisible={isColumnVisible}
                            toggleColumnVisibility={toggleColumnVisibility}
                        />
                    );
                })}
            </ul>,
            document.body,
        );
    }

    renderCaretIcon() {
        const iconName = this.state.isOpen ? 'caret-up' : 'caret-down';

        return <Icon name={iconName} extraClasses="ibexa-icon--tiny-small c-simple-dropdown__expand-icon" />;
    }

    renderToggler() {
        const label = Translator.trans(/*@Desc("Columns")*/ 'view_columns_toggler.label', {}, 'ibexa_sub_items');

        return (
            <button ref={this._refTogglerButton} type="button" className="c-simple-dropdown__selected" onClick={this.togglePanel}>
                <Icon name="column-settings" extraClasses="ibexa-icon--small c-simple-dropdown__selected-item-type-icon" />
                <span className="c-simple-dropdown__selected-item-label">{label}</span>
                {this.renderCaretIcon()}
            </button>
        );
    }

    render() {
        return (
            <div className="c-view-columns-toggler">
                <div className="c-simple-dropdown c-simple-dropdown--switcher">
                    {this.renderToggler()}
                    {this.renderPanel()}
                </div>
            </div>
        );
    }
}

ViewColumnsTogglerComponent.propTypes = {
    columnsVisibility: PropTypes.object.isRequired,
    toggleColumnVisibility: PropTypes.func.isRequired,
};
