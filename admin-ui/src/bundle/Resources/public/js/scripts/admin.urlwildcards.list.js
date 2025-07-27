(function (doc) {
    const typeField = doc.getElementById('url_wildcard_list_type');

    typeField.addEventListener('change', function () {
        this.form.submit();
    });
})(document);
