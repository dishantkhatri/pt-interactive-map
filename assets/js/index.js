window.addEventListener('DOMContentLoaded', () => {
    let mapContainer = document.getElementById('leaflet-map');
    if (mapContainer !== null) {
    	fetch(frontend_ajax_object.siteURL+'/wp-json/wp/v2/posts-with-lat-lng')
        .then(response => response.json())
        .then(locations => {
            const map = L.map('leaflet-map').setView([3.10044, 37.40782], 2);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 12,
                minZoom: 2,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Add markers to the map
            for (const location of locations) {
	                L.marker([location.latitude, location.longitude])
                    .bindPopup("<a href='" + location.post_link + "' target='_self'>"+location.title+"</a>")
                    .addTo(map);
            }
        })
        .catch(error => {
            console.error('Error fetching locations:', error);
        });
    }
});