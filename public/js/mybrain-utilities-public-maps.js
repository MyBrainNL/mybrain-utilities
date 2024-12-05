(function( $ ) {
	'use strict';

	jQuery(document).ready(function($){
		if ($('#mbu-map').length > 0) {
			setTimeout(function(){ 
				setTimeout(function(){ 
					var latlong, latitude, longitude, latlng;
					var mycenter = '52.145634,5.04855';
					var center = $('#mbu-map').attr('center');
					var coords = $('#mbu-map').attr('coords');
					var zoom = $('#mbu-map').attr('zoom');
					if (zoom == '') {
						zoom = 17;
					}
					if ((typeof center != 'undefined') && (center != '')) {
						mycenter = center;
					} else if ((typeof coords != 'undefined') && (coords != '')) {
						mycenter = coords;
					}
					if ((typeof coords != 'undefined') && (coords != '')) {
						latlong =  coords.split(',');
						latitude = parseFloat(latlong[0]);
						longitude = parseFloat(latlong[1]);
						latlng = L.latLng(latitude, longitude);
					} else if ((typeof center != 'undefined') && (center != '')) {
						latlong =  center.split(',');
						latitude = parseFloat(latlong[0]);
						longitude = parseFloat(latlong[1]);
						latlng = L.latLng(latitude, longitude);
					}
					latlong =  mycenter.split(',');
					latitude = parseFloat(latlong[0]);
					longitude = parseFloat(latlong[1]);
					var latlngc = L.latLng(latitude, longitude);
					var mbumap = L.map('mbu-map').setView(latlngc, zoom);
					L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
						attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>'
					}).addTo(mbumap);
					var maphasmarker = false;
					var marker;
					var latlngs = [];
					if ((typeof coords != 'undefined') && (coords != '')) {
						if ($('.mbumap-popup').length > 0) {
							var content = $('.mbumap-popup').html();
							marker = new L.marker(latlng).addTo(mbumap).bindPopup(content).openPopup();
						} else {
							marker = new L.marker(latlng).addTo(mbumap);
						}
						latlngs.push(latlng);
						maphasmarker = true;
						// mbumap.fitBounds(latlngs);
					}
				}, 100);
			}, 0);
		}
	});

})( jQuery );
