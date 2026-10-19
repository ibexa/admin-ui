import * as middleEllipsisHelper from '@ibexa-admin-ui/src/bundle/Resources/public/js/scripts/helpers/middle.ellipsis';

(function (doc) {
    middleEllipsisHelper.parse();

    doc.body.addEventListener(
        'ibexa-content-field-updated',
        (event) => {
            middleEllipsisHelper.parse(event.target);
        },
        false,
    );
})(window.document);
