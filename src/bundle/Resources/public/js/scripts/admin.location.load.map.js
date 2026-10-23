(function (global, doc, Leaflet) {
    const initMap = (mapLocation) => {
        const latitude = parseFloat(mapLocation.dataset.latitude);
        const longitude = parseFloat(mapLocation.dataset.longitude);
        const map = Leaflet.map(mapLocation, {
            zoom: 15,
            zoomControl: false,
            scrollWheelZoom: false,
            dragging: false,
            tap: false,
            center: [latitude, longitude],
        });

        Leaflet.marker([latitude, longitude], {
            icon: new Leaflet.Icon.Default({
                imagePath: '/bundles/ibexaadminuiassets/vendors/leaflet/dist/images/',
            }),
        }).addTo(map);

        Leaflet.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(map);
    };

    doc.querySelectorAll('.ibexa-gmaplocation__map').forEach(initMap);
    doc.body.addEventListener(
        'ibexa-content-field-updated',
        (event) => {
            event.target.querySelectorAll('.ibexa-gmaplocation__map').forEach(initMap);
        },
        false,
    );
})(window, window.document, window.L);
