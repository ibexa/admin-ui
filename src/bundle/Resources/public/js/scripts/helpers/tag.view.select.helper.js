import { removeRootFromPathString, findLocationsByIds, buildLocationsBreadcrumbs } from './location.helper';

const buildItemsFromUDWResponse = (udwItems, getId, callback) => {
    Promise.all(
        udwItems.map(
            (item) =>
                new Promise((resolve) => {
                    findLocationsByIds(removeRootFromPathString(item.pathString), (locations) => {
                        resolve({
                            id: getId(item),
                            name: buildLocationsBreadcrumbs(locations),
                        });
                    });
                }),
        ),
    ).then(callback);
};

export { buildItemsFromUDWResponse };
