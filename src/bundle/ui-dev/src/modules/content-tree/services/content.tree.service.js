import { getRequestHeaders, getRequestMode } from '../../common/services/common.service.js';
import { handleRequestResponse } from '../../common/helpers/request.helper';
import { showErrorNotification } from '../../common/services/notification.service';

const ENDPOINT_LOAD_SUBITEMS = '/api/ibexa/v2/location/tree/load-subitems';
const ENDPOINT_LOAD_SUBTREE = '/api/ibexa/v2/location/tree/load-subtree';
const DEFAULT_INSTANCE_URL = window.location.origin;

export const loadLocationItems = ({ siteaccess, accessToken, instanceUrl = DEFAULT_INSTANCE_URL }, parentLocationId, callback, limit = 50, offset = 0) => {
    const request = new Request(`${ENDPOINT_LOAD_SUBITEMS}/${parentLocationId}/${limit}/${offset}`, {
        method: 'GET',
        mode: getRequestMode({ instanceUrl }),
        credentials: 'same-origin',
        headers: getRequestHeaders({
            siteaccess,
            accessToken,
            extraHeaders: {
                Accept: 'application/vnd.ibexa.api.ContentTreeNode+json',
            }
        })
    });

    fetch(request)
        .then(handleRequestResponse)
        .then((data) => {
            const location = data.ContentTreeNode;

            location.children = location.children.map(mapChildrenToSubitems);

            return mapChildrenToSubitems(location);
        })
        .then(callback)
        .catch(showErrorNotification);
};

export const loadSubtree = ({ token, siteaccess, accessToken, subtree, sortClause, sortOrder, instanceUrl = DEFAULT_INSTANCE_URL }, callback) => {
    let path = ENDPOINT_LOAD_SUBTREE;

    if (sortClause && sortOrder) {
        path += `?sortClause=${sortClause}&sortOrder=${sortOrder}`;
    }

    const request = new Request(path, {
        method: 'POST',
        mode: getRequestMode({ instanceUrl }),
        credentials: 'same-origin',
        body: JSON.stringify({
            LoadSubtreeRequest: {
                '_media-type': 'application/vnd.ibexa.api.ContentTreeLoadSubtreeRequest',
                nodes: subtree,
            },
        }),
        headers: getRequestHeaders({
            token,
            siteaccess,
            accessToken,
            extraHeaders: {
                Accept: 'application/vnd.ibexa.api.ContentTreeRoot+json',
                'Content-Type': 'application/vnd.ibexa.api.ContentTreeLoadSubtreeRequest+json',
            }
        })
    });

    fetch(request)
        .then(handleRequestResponse)
        .then((data) => {
            const loadedSubtree = data.ContentTreeRoot.ContentTreeNodeList;

            return mapChildrenToSubitemsDeep(loadedSubtree);
        })
        .then(callback)
        .catch(showErrorNotification);
};

const mapChildrenToSubitemsDeep = (tree) =>
    tree.map((subtree) => {
        mapChildrenToSubitems(subtree);
        subtree.subitems = mapChildrenToSubitemsDeep(subtree.subitems);

        return subtree;
    });

const mapChildrenToSubitems = (location) => {
    location.totalSubitemsCount = location.totalChildrenCount;
    location.subitems = location.children;

    delete location.totalChildrenCount;
    delete location.children;
    delete location.displayLimit;

    return location;
};
