(function (global, doc, ibexa, Routing) {
    const copyButtons = doc.querySelectorAll('.ibexa-btn--copy-content-type');
    const copyContentType = ({ currentTarget }) => {
        const contentTypeCopyForm = doc.querySelector('form[name="content_type_copy"]');
        const contentTypeIdentifierInput = contentTypeCopyForm.querySelector('#content_type_copy_content_type');
        const { contentTypeId, contentTypeIdentifier, contentTypeGroupId } = currentTarget.dataset;
        const formAction = Routing.generate('ibexa.content_type.copy', { contentTypeId, contentTypeGroupId });

        contentTypeIdentifierInput.value = contentTypeIdentifier;
        contentTypeCopyForm.action = formAction;

        contentTypeCopyForm.submit();
    };

    copyButtons.forEach((copyButton) => copyButton.addEventListener('click', copyContentType, false));
})(window, window.document, window.ibexa, window.Routing);
