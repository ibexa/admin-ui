(function (global, doc) {
    class selectionFilter extends global.ibexa.BaseSidebarFilter {
        constructor(config) {
            super(config);

            this.searchToggleBtn = this.wrapper.querySelector('.ibexa-sidebar-filter-selection__search-toggle-btn');
            this.searchWrapper = this.wrapper.querySelector('.ibexa-sidebar-filter-selection__search-wrapper');
            this.searchInput = this.wrapper.querySelector('.ibexa-sidebar-filter-selection__search-input');
            this.toggleItemsCheckStateBtn = this.wrapper.querySelector('.ibexa-sidebar-filter-selection__toggle-checks-state-btn');
            this.list = this.wrapper.querySelector('.ibexa-sidebar-filter-selection__list');
            this.listItems = this.wrapper.querySelectorAll('.ibexa-sidebar-filter-selection__list-item');
            this.toggleListBtn = this.wrapper.querySelector('.ibexa-sidebar-filter-selection__list-toggle-btn');

            this.isSearchExpanded = config.isSearchExpanded || false;
            this.isListExpanded = config.isListExpanded || false;
            this.itemsCheckedCount = config.itemsCheckedCount || 0;
            this.itemsShortListLimit = config.wrapper.dataset.itemsShortListLimit;

            this.toggleSearchBar = this.toggleSearchBar.bind(this);
            this.toggleItemsCheckState = this.toggleItemsCheckState.bind(this);
            this.setToggleItemsCheckStateBtnLabel = this.setToggleItemsCheckStateBtnLabel.bind(this);
            this.filterItems = this.filterItems.bind(this);
            this.toggleListItems = this.toggleListItems.bind(this);
            this.setToggleListBtnState = this.setToggleListBtnState.bind(this);
        }

        toggleSearchBar() {
            this.isSearchExpanded = !this.isSearchExpanded;

            this.searchToggleBtn.classList.toggle('ibexa-btn--selected', this.isSearchExpanded);
            this.searchWrapper.classList.toggle('ibexa-sidebar-filter-selection__search-wrapper--hidden', !this.isSearchExpanded);
            this.toggleListBtn.classList.toggle('ibexa-sidebar-filter-selection__list-toggle-btn--hidden', this.isSearchExpanded);

            if (!this.isSearchExpanded) {
                this.searchInput.value = '';
                this.toggleListItems(true);
            }
        }

        toggleItemsCheckState() {
            this.listItems.forEach((listItem) => {
                const listItemCheckbox = listItem.querySelector('.ibexa-input--checkbox');

                listItemCheckbox.checked = !this.itemsCheckedCount;
            });

            this.setToggleItemsCheckStateBtnLabel();
        }

        toggleListItems(forcedCollapse = false) {
            const shouldCollapseList = forcedCollapse || (!forcedCollapse && this.isListExpanded);

            this.listItems.forEach((listItem, index) => {
                const isIndexOverLimit = index >= this.itemsShortListLimit;
                const shouldHideItem = shouldCollapseList && isIndexOverLimit;

                listItem.classList.toggle('ibexa-sidebar-filter-selection__list-item--hidden', shouldHideItem);
            });

            this.isListExpanded = !shouldCollapseList;
            this.setToggleListBtnState()
        }

        setToggleItemsCheckStateBtnLabel() {
            this.itemsCheckedCount = this.list
                .querySelectorAll('.ibexa-sidebar-filter-selection__list-item .ibexa-input--checkbox:checked')
                .length;

            this.toggleItemsCheckStateBtn.innerHTML = this.itemsCheckedCount
                ? `Clear (${this.itemsCheckedCount})`
                : 'Select all';
        }

        setToggleListBtnState() {
            this.toggleListBtn.innerHTML = this.isListExpanded ? 'Less' : 'More';
            this.toggleListBtn.classList.toggle('ibexa-sidebar-filter-selection__list-toggle-btn--expanded-list', this.isListExpanded);
        }

        filterItems({ currentTarget }) {
            const fieldFilterQueryLowerCase = currentTarget.value.toLowerCase();

            this.listItems.forEach((listItem) => {
                const itemNameNode = listItem.querySelector('.ibexa-sidebar-filter-selection__list-item-label');
                const itemNameLowerCase = itemNameNode.innerText.toLowerCase();
                const shouldHideItem = !itemNameLowerCase.includes(fieldFilterQueryLowerCase);

                listItem.classList.toggle('ibexa-sidebar-filter-selection__list-item--hidden', shouldHideItem);
            });
        }

        init() {
            this.toggleItemsCheckStateBtn.addEventListener('click', this.toggleItemsCheckState, false);

            this.searchInput.addEventListener('keyup', this.filterItems, false);
            this.searchInput.addEventListener('input', this.filterItems, false);

            this.listItems.forEach((listItem) => {
                const listItemCheckbox = listItem.querySelector('.ibexa-input--checkbox');

                listItemCheckbox.addEventListener('change', this.setToggleItemsCheckStateBtnLabel, false);
            });

            if (this.searchToggleBtn) {
                this.searchToggleBtn.addEventListener('click', this.toggleSearchBar, false);
            }

            if (this.toggleListBtn) {
                this.toggleListBtn.addEventListener('click', () => this.toggleListItems(), false);
            }
        }
    }

    doc.querySelectorAll('.ibexa-sidebar-filter-selection').forEach((selectionFilterWrapper) => {
        const filter = new selectionFilter({
            wrapper: selectionFilterWrapper
        });

        filter.init();
    })
})(window, window.document);
