import React, { useEffect, useCallback, useState, createContext, useRef, useContext } from 'react';
import PropTypes from 'prop-types';
import Icon from '../common/icon/icon';
import Snackbar from './components/snackbar/snackbar';
import SidebarHeader from './components/sidebar-header/sidebar.header';
import ItemsView from './components/items-view/items.view';
import Filters from './components/filters/filters';
import { CancelContext } from '../universal-discovery/universal.discovery.module';
import TreeBrowser from './components/tree-browser/tree.browser';

const { Translator, ibexa, document } = window;

export const SelectedItemsContext = createContext();

const ImagePickerTabModule = (props) => {
    const cancelUDW = useContext(CancelContext);
    const filtersLabel = Translator.trans(/*@Desc("Filters")*/ 'filters.title', {}, 'ibexa_universal_discovery_widget');
    const languageLabel = Translator.trans(/*@Desc("Language")*/ 'filters.language', {}, 'ibexa_universal_discovery_widget');
    const sectionLabel = Translator.trans(/*@Desc("Section")*/ 'filters.section', {}, 'ibexa_universal_discovery_widget');
    const subtreeLabel = Translator.trans(/*@Desc("Subtree")*/ 'filters.subtree', {}, 'ibexa_universal_discovery_widget');
    const clearLabel = Translator.trans(/*@Desc("Clear")*/ 'filters.clear', {}, 'ibexa_universal_discovery_widget');
    const applyLabel = Translator.trans(/*@Desc("Apply")*/ 'filters.apply', {}, 'ibexa_universal_discovery_widget');

    return (
        <div className="m-dwb">
            <div className="c-dwb-main-container">
                <div className="c-dwb-main-container__top-bar">
                    <div className="c-dwb-top-bar">
                        <div className="c-dwb-top-bar__title-wrapper" data-tooltip-container-selector=".c-udw-tab" title="Image library">
                            Image library
                        </div>
                        <div className="c-dwb-top-bar__actions-wrapper">
                            <div className="ibexa-input-text-wrapper ibexa-input-text-wrapper--search ibexa-input-text-wrapper--type-text">
                                <input type="text" className="ibexa-input ibexa-input--text form-control" placeholder="Search..." />
                                <div className="ibexa-input-text-wrapper__actions">
                                    <button
                                        type="button"
                                        className="btn ibexa-btn ibexa-btn--ghost ibexa-btn--no-text ibexa-input-text-wrapper__action-btn ibexa-input-text-wrapper__action-btn--clear"
                                        tabIndex="-1"
                                    >
                                        <Icon name="discard" extraClasses="ibexa-icon--tiny" />
                                    </button>
                                    <button
                                        type="button"
                                        className="btn ibexa-btn ibexa-btn--ghost ibexa-btn--no-text ibexa-input-text-wrapper__action-btn ibexa-input-text-wrapper__action-btn--search"
                                        tabIndex="-1"
                                    >
                                        <Icon name="search" extraClasses="ibexa-icon--small" />
                                    </button>
                                </div>
                            </div>
                        </div>
                        <span className="c-dwb-top-bar__cancel-btn-wrapper">
                            <button
                                className="c-dwb-top-bar__cancel-btn btn ibexa-btn ibexa-btn--ghost ibexa-btn--no-text"
                                type="button"
                                onClick={cancelUDW}
                                data-tooltip-container-selector=".c-top-menu__cancel-btn-wrapper"
                            >
                                <Icon name="discard" extraClasses="ibexa-icon--medium" />
                            </button>
                        </span>
                    </div>
                </div>
                <div className="c-dwb-main-container__content">
                    <div className="c-dwb-main-container__left-sidebar">
                        <SidebarHeader>
                            <Icon name="content-tree" extraClasses="ibexa-icon--small" />
                            Folders
                        </SidebarHeader>
                        <TreeBrowser />
                    </div>
                    <div className="c-dwb-main-container__main">
                        <ItemsView />
                    </div>
                    <div className="c-dwb-main-container__right-sidebar">
                        {/* <SidebarHeader>
                            <div className="c-filters__header-content">{filtersLabel}</div>
                            <div className="c-filters__header-actions">
                                <button className="btn ibexa-btn ibexa-btn--ghost ibexa-btn--small" type="button" >
                                    {clearLabel}
                                </button>
                                <button
                                    type="submit"
                                    className="btn ibexa-btn ibexa-btn--secondary ibexa-btn--small ibexa-btn--apply"
                                >
                                    {applyLabel}
                                </button>
                            </div>
                        </SidebarHeader> */}
                        <Filters />
                    </div>
                </div>
                <div className="c-dwb-select-snackbar" />
            </div>
            {/* {isAnyItemSelected && <Snackbar selectedItems={selectedItems} />} */}
            <Snackbar />
        </div>
    );
};

ibexa.addConfig(
    'adminUiConfig.universalDiscoveryWidget.tabs',
    [
        {
            id: 'image_picker',
            component: ImagePickerTabModule,
            label: Translator.trans(/*@Desc("Image Picker")*/ 'image_picker.label', {}, 'ibexa_universal_discovery_widget'),
            icon: ibexa.helpers.icon.getIconPath('bookmark'),
            isHiddenOnList: true,
        },
    ],
    true,
);

export default ImagePickerTabModule;

// DiscoveryWidget.propTypes = {};

// DiscoveryWidget.defaultProps = {};

// ibexa.addConfig('modules.DAM', DiscoveryWidget);

// export default DiscoveryWidget;
