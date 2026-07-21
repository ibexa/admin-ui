declare global {
    interface Ibexa {
        helpers: IbexaHelpers;
    }

    interface IbexaHelpers {
        browser: typeof import('@ibexa-admin-ui-helpers/browser.helper');
        contentType: typeof import('@ibexa-admin-ui-helpers/content.type.helper');
        cookies: typeof import('@ibexa-admin-ui-helpers/cookies.helper');
        dom: typeof import('@ibexa-admin-ui-helpers/dom.helper');
        formValidation: typeof import('@ibexa-admin-ui-helpers/form.validation.helper');
        highlight: typeof import('@ibexa-admin-ui-helpers/highlight.helper');
        icon: typeof import('@ibexa-admin-ui-helpers/icon.helper');
        content: typeof import('@ibexa-admin-ui-helpers/content.helper');
        location: typeof import('@ibexa-admin-ui-helpers/location.helper');
        ellipsis: {
            middle: typeof import('@ibexa-admin-ui-helpers/middle.ellipsis');
        };
        modal: typeof import('@ibexa-admin-ui-helpers/modal.helper');
        notification: typeof import('@ibexa-admin-ui-helpers/notification.helper');
        objectInstances: typeof import('@ibexa-admin-ui-helpers/object.instances');
        pagination: typeof import('@ibexa-admin-ui-helpers/pagination.helper');
        react: typeof import('@ibexa-admin-ui-helpers/react.helper');
        request: typeof import('@ibexa-admin-ui-helpers/request.helper');
        system: typeof import('@ibexa-admin-ui-helpers/system.helper');
        table: typeof import('@ibexa-admin-ui-helpers/table.helper');
        tagViewSelect: typeof import('@ibexa-admin-ui-helpers/tag.view.select.helper');
        text: typeof import('@ibexa-admin-ui-helpers/text.helper');
        timezone: typeof import('@ibexa-admin-ui-helpers/timezone.helper');
        tooltips: typeof import('@ibexa-admin-ui-helpers/tooltips.helper');
        user: typeof import('@ibexa-admin-ui-helpers/user.helper');
    }
}

export {};
