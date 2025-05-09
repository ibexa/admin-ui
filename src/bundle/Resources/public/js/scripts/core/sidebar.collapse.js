(function (global, doc) {
    const sidebar = doc.querySelector('.ibexa-list-filters__sidebar');
    const toggleBtn = sidebar.querySelector('.ibexa-list-filters__expand-btn');
    const toggleCollapseIcon = toggleBtn.querySelector('.ibexa-list-filters__collapse-icon');
    const toggleExpandIcon = toggleBtn.querySelector('.ibexa-list-filters__expand-icon');

    const toggleSidebar = () => {
        const isExpanded = toggleBtn.getAttribute('aria-expanded') === 'true';

        sidebar.classList.toggle('ibexa-list-filters__sidebar--collapsed', isExpanded);
        toggleBtn.setAttribute('aria-expanded', (!isExpanded).toString());
        toggleExpandIcon.toggleAttribute('hidden', !isExpanded);
        toggleCollapseIcon.toggleAttribute('hidden', isExpanded);
    };

    toggleBtn.addEventListener('click', toggleSidebar, false);
})(window, window.document, window.bootstrap, window.Translator);
