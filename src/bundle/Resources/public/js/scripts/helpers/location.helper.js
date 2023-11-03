import { escapeHTML } from './text.helper';
import { getJsonFromResponse } from './request.helper';
import { showErrorNotification } from './notification.helper';
import { getContext as getHelpersContext } from './helpers.service';

const { Translator, document: doc } = window;

const removeRootFromPathString = (pathString) => {
    const pathArray = pathString.split('/').filter((id) => id);

    return pathArray.splice(1, pathArray.length - 1);
};
const buildLocationsBreadcrumbs = (locations) =>
    locations.map((Location) => escapeHTML(Location.ContentInfo.Content.TranslatedName)).join(' / ');
const findLocationsByIds = (idList, callback) => {
    const token = getHelpersContext().token ?? doc.querySelector('meta[name="CSRF-Token"]')?.content;
    const siteaccess = getHelpersContext().siteaccess ?? doc.querySelector('meta[name="SiteAccess"]')?.content;
    const body = JSON.stringify({
        ViewInput: {
            identifier: `locations-by-path-string-${idList.join('-')}`,
            public: false,
            LocationQuery: {
                FacetBuilders: {},
                SortClauses: { SectionIdentifier: 'ascending' },
                Filter: { LocationIdCriterion: idList.join(',') },
                limit: 50,
                offset: 0,
            },
        },
    });
    const request = new Request('/api/ibexa/v2/views', {
        method: 'POST',
        headers: {
            Accept: 'application/vnd.ibexa.api.View+json; version=1.1',
            'Content-Type': 'application/vnd.ibexa.api.ViewInput+json; version=1.1',
            'X-Requested-With': 'XMLHttpRequest',
            'X-Siteaccess': siteaccess,
            'X-CSRF-Token': token,
        },
        body,
        mode: 'same-origin',
        credentials: 'same-origin',
    });
    const errorMessage = Translator.trans(
        /*@Desc("Cannot find children Locations with ID %idList%")*/ 'select_location.error',
        { idList: idList.join(',') },
        'ibexa_universal_discovery_widget',
    );

    fetch(request)
        .then(getJsonFromResponse)
        .then((viewData) => viewData.View.Result.searchHits.searchHit)
        .then((searchHits) => searchHits.map((searchHit) => searchHit.value.Location))
        .then(callback)
        .catch(() => showErrorNotification(errorMessage));
};

export { removeRootFromPathString, findLocationsByIds, buildLocationsBreadcrumbs };
