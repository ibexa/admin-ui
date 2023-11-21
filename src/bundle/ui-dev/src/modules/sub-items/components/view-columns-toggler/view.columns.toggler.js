import React, { Component, createRef } from 'react';
import PropTypes from 'prop-types';

import Icon from '../../../common/icon/icon';
import ViewColumnsTogglerListElement from './view.columns.toggler.list.element';
import { columnsLabels } from '../../sub.items.module';
import { createCssClassNames } from '../../../common/helpers/css.class.names';

const { Translator } = window;

const DEFAULT_PANEL_HEIGHT = 450;

export default class ViewColumnsTogglerComponent extends Component {
    constructor(props) {
        super(props);

        this.togglePanel = this.togglePanel.bind(this);
        this.hidePanel = this.hidePanel.bind(this);

        this._refTogglerButton = createRef();
        this._refPanel = createRef();

        this.state = {
            isOpen: false,
            buttonBottomDocumentOffset: null,
            panelHeight: null,
        };
    }

    componentDidMount() {
        document.addEventListener('click', this.hidePanel, false);

        this.setState(() => ({
            buttonBottomDocumentOffset: this.getBtnBottomDocumentOffset(),
        }));
    }

    componentDidUpdate() {
        const { isOpen, panelHeight } = this.state;

        if (isOpen && panelHeight === null) {
            this.setState({
                panelHeight: this._refPanel.current.offsetHeight,
            });
        }
    }

    componentWillUnmount() {
        document.removeEventListener('click', this.hidePanel);
    }

    getBtnBottomDocumentOffset() {
        const buttonTopOffset = this._refTogglerButton.current.getBoundingClientRect().top;

        return window.innerHeight - buttonTopOffset;
    }

    hidePanel({ target }) {
        if (!this.state.isOpen) {
            return;
        }

        const isClickInsideToggler = target.closest('.c-view-columns-toggler');

        if (!isClickInsideToggler) {
            this.setState(() => ({
                isOpen: false,
            }));
        }
    }

    togglePanel() {
        this.setState((state) => ({
            buttonBottomDocumentOffset: this.getBtnBottomDocumentOffset(),
            isOpen: !state.isOpen,
        }));
    }

    renderPanel() {
        if (!this.state.isOpen) {
            return null;
        }

        const { columnsVisibility, toggleColumnVisibility } = this.props;
        const { buttonBottomDocumentOffset, panelHeight: measuredPanelHeight } = this.state;
        const panelHeight = measuredPanelHeight ?? DEFAULT_PANEL_HEIGHT;
        const showAboveBtn = buttonBottomDocumentOffset < panelHeight;
        const className = createCssClassNames({
            'ibexa-popup-menu': true,
            'c-view-columns-toggler__panel': true,
            'c-view-columns-toggler__panel--above-btn': showAboveBtn,
        });

        return (
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
            </ul>
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
