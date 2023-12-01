import { getAuthenticationHeaders } from '../../common/services/common.service.js';
import { handleRequestResponse } from '../../common/helpers/request.helper';
import { showErrorNotification } from '../../common/services/notification.service';

const ENDPOINT_LOAD_SUBITEMS = '/api/ibexa/v2/location/tree/load-subitems';
const ENDPOINT_LOAD_SUBTREE = '/api/ibexa/v2/location/tree/load-subtree';

export const loadLocationItems = ({ siteaccess, accessToken }, parentLocationId, callback, limit = 50, offset = 0) => {
    const request = new Request(`${ENDPOINT_LOAD_SUBITEMS}/${parentLocationId}/${limit}/${offset}`, {
        method: 'GET',
        mode: 'same-origin',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/vnd.ibexa.api.ContentTreeNode+json',
            ...getAuthenticationHeaders({ siteaccess, accessToken })
        },
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

export const loadSubtree = ({ token, siteaccess, accessToken, subtree, sortClause, sortOrder }, callback) => {
    let path = ENDPOINT_LOAD_SUBTREE;

    if (sortClause && sortOrder) {
        path += `?sortClause=${sortClause}&sortOrder=${sortOrder}`;
    }

    const request = new Request(path, {
        method: 'POST',
        mode: 'same-origin',
        credentials: 'same-origin',
        body: JSON.stringify({
            LoadSubtreeRequest: {
                '_media-type': 'application/vnd.ibexa.api.ContentTreeLoadSubtreeRequest',
                nodes: subtree,
            },
        }),
        headers: {
            Accept: 'application/vnd.ibexa.api.ContentTreeRoot+json',
            'Content-Type': 'application/vnd.ibexa.api.ContentTreeLoadSubtreeRequest+json',
            ...getAuthenticationHeaders({ token, siteaccess, accessToken })
        },
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
