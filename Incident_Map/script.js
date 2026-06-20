/* ============================================================
   survAIval - Incident Map
   - Leaflet map (OpenStreetMap tiles) plotting real report coords
   - List-row <-> marker sync
   - Address search (Nominatim geocoding -> Leaflet pan + search marker)
   ============================================================ */
(function () {
    'use strict';

    if (typeof L === 'undefined') {
        console.error('[survAIval] Leaflet failed to load.');
        return;
    }

    var config = window.SURVAIVAL_INCIDENT_MAP || {};
    var center = config.center || { lat: 14.1074, lng: 121.1416 };
    var reports = Array.isArray(config.reports) ? config.reports : [];

    // ---------- Init Leaflet ----------
    var map = L.map('incidentMap', {
        center: [center.lat, center.lng],
        zoom: 15,
        scrollWheelZoom: true
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: 'OpenStreetMap contributors'
    }).addTo(map);

    // ---------- Status -> color mapping ----------
    var STATUS_COLOR = {
        pending:    '#c0392b', // red
        responding: '#e07000', // orange
        resolved:   '#27a060'  // green
    };

    // Build circle marker icons (small colored circles with white border)
    function makeMarker(status, isActive) {
        var color = STATUS_COLOR[status] || '#888';
        var size  = isActive ? 22 : 16;
        var html  = '<div style="'
                  + 'width:' + size + 'px;height:' + size + 'px;'
                  + 'background:' + color + ';'
                  + 'border:3px solid #fff;'
                  + 'border-radius:50%;'
                  + 'box-shadow:0 2px 6px rgba(0,0,0,0.4);'
                  + (isActive ? 'animation:pulse-dot 1.5s infinite;' : '')
                  + '"></div>';
        return L.divIcon({
            html: html,
            className: 'incident-marker',
            iconSize: [size, size],
            iconAnchor: [size / 2, size / 2]
        });
    }

    // ---------- Plot all reports ----------
    var markers = [];          // index-aligned with reports array
    var bounds  = [];

    reports.forEach(function (rep, idx) {
        var lat = parseFloat(rep.latitude);
        var lng = parseFloat(rep.longitude);
        if (!isFinite(lat) || !isFinite(lng)) return;

        var marker = L.marker([lat, lng], {
            icon: makeMarker(rep.status, false)
        }).addTo(map);

        var popupHtml = '<div class="incident-popup">'
                      + '<div class="popup-type">' + escapeHtml((rep.incident_type || '').toUpperCase()) + '</div>'
                      + '<div class="popup-title">' + escapeHtml(rep.incident_title || 'Untitled') + '</div>'
                      + '<div class="popup-loc"><i class="bi bi-geo-alt-fill"></i> ' + escapeHtml(rep.location || '') + '</div>'
                      + '<div class="popup-status" style="background:' + (STATUS_COLOR[rep.status] || '#888') + '">'
                      +   escapeHtml(rep.status) +
                      + '</div>'
                      + '</div>';
        marker.bindPopup(popupHtml);

        marker.on('click', function () {
            highlightIncident(idx, rep.status);
        });

        markers[idx] = marker;
        bounds.push([lat, lng]);
    });

    // If we have plotted pins, fit the map to them; otherwise stay on center
    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [40, 40], maxZoom: 16 });
    }

    // ---------- List <-> marker sync ----------
    var activeIndex = null;

    function setActiveMarker(idx) {
        markers.forEach(function (m, i) {
            if (!m) return;
            var rep = reports[i];
            m.setIcon(makeMarker(rep.status, i === idx));
            if (i === idx) {
                m.openPopup();
                var latlng = m.getLatLng();
                map.flyTo(latlng, Math.max(map.getZoom(), 16), { duration: 0.8 });
            } else {
                m.closePopup();
            }
        });
    }

    // Expose for the inline onclick handler in incident_map.php
    window.highlightIncident = function (idx, status) {
        var items = document.querySelectorAll('.incident-item');
        items.forEach(function (it) { it.classList.remove('highlighted'); });

        var row = document.getElementById('incident-' + idx);
        if (row) {
            row.classList.add('highlighted');
            row.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        activeIndex = idx;
        setActiveMarker(idx);
    };

    // Click outside the list clears highlights
    document.addEventListener('click', function (e) {
        var list = document.getElementById('incidentList');
        if (list && !list.contains(e.target)) {
            document.querySelectorAll('.incident-item').forEach(function (it) {
                it.classList.remove('highlighted');
            });
            markers.forEach(function (m, i) {
                if (!m) return;
                m.setIcon(makeMarker(reports[i].status, false));
                m.closePopup();
            });
            activeIndex = null;
        }
    });

    // ---------- Address search: geocode + pan Leaflet ----------
    var addressInput = document.getElementById('addressSearch');
    var searchBtn    = document.getElementById('searchBtn');
    var searchMarker = null; // temporary marker for searched address

    // Build a bounded viewbox around the user's barangay so results stay local.
    // (Don't concatenate the bias into the query - Nominatim treats that as a
    // single literal phrase and returns almost nothing for any real input.)
    var mapCenter = (center && isFinite(center.lat) && isFinite(center.lng))
                  ? center
                  : { lat: 14.1074, lng: 121.1416 };
    var dLat = 0.05;  // ~5.5 km N/S
    var dLng = 0.05;  // ~5.5 km E/W
    var viewbox = (mapCenter.lng - dLng) + ',' +
                  (mapCenter.lat + dLat) + ',' +
                  (mapCenter.lng + dLng) + ',' +
                  (mapCenter.lat - dLat);

    function searchAddress() {
        var q = (addressInput.value || '').trim();
        if (!q) {
            setSearchStatus('Please type an address to search.', 'warn');
            return;
        }

        // Immediate visible feedback so the user knows something is happening
        setSearchStatus('Searching for "' + q + '".', 'loading');
        showToast('Searching: ' + q, 'info');

        // Geocode via Nominatim and point the Leaflet map to it.
        // We try the bounded (local) viewbox first; if nothing's there, fall
        // back to a country-wide search so the user still gets a hit when
        // the address is outside the barangay.
        var nominatimUrl = 'https://nominatim.openstreetmap.org/search'
                         + '?format=json&limit=5'
                         + '&countrycodes=ph'
                         + '&viewbox=' + encodeURIComponent(viewbox)
                         + '&bounded=1'
                         + '&q=' + encodeURIComponent(q);

        fetch(nominatimUrl, { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data && data.length > 0) {
                placeSearchMarker(data[0], q);
                return;
            }
            var fallbackUrl = 'https://nominatim.openstreetmap.org/search'
                            + '?format=json&limit=5'
                            + '&countrycodes=ph'
                            + '&q=' + encodeURIComponent(q);
            return fetch(fallbackUrl, { headers: { 'Accept': 'application/json' } })
                .then(function (r2) { return r2.json(); })
                .then(function (data2) {
                    if (!data2 || data2.length === 0) {
                        setSearchStatus('No location found for "' + q + '".', 'warn');
                        showToast('Address not found on the map', 'warn');
                        return;
                    }
                    placeSearchMarker(data2[0], q);
                });
        })
        .catch(function (err) {
            console.error('[survAIval] Geocoding failed:', err);
            setSearchStatus('Could not reach the geocoding service. Try again later.', 'warn');
            showToast('Geocoding failed - check your network', 'warn');
        });
    }

    // Pan the Leaflet map to a Nominatim result and drop the blue search marker.
    function placeSearchMarker(hit, originalQuery) {
        var lat = parseFloat(hit.lat);
        var lng = parseFloat(hit.lon);
        if (!isFinite(lat) || !isFinite(lng)) return;

        var niceName = hit.display_name || originalQuery;
        map.flyTo([lat, lng], 17, { duration: 1.0 });
        setSearchStatus('Showing "' + originalQuery + '" on the map.', 'success');

        // Drop / move a temporary search marker
        if (searchMarker) {
            searchMarker.setLatLng([lat, lng]);
        } else {
            searchMarker = L.marker([lat, lng], {
                icon: L.divIcon({
                    html: '<div style="'
                        + 'width:18px;height:18px;'
                        + 'background:#2563eb;'
                        + 'border:3px solid #fff;'
                        + 'border-radius:50%;'
                        + 'box-shadow:0 2px 6px rgba(0,0,0,0.5);'
                        + '"></div>',
                    className: 'search-marker',
                    iconSize: [18, 18],
                    iconAnchor: [9, 9]
                })
            }).addTo(map);

            searchMarker.bindPopup(
                '<div class="incident-popup">'
                + '<div class="popup-type">SEARCH RESULT</div>'
                + '<div class="popup-title">' + escapeHtml(niceName) + '</div>'
                + '<div class="popup-loc"><i class="bi bi-geo-alt-fill"></i> '
                +   escapeHtml(niceName) + '</div>'
                + '</div>'
            );
        }
        searchMarker.openPopup();
    }

    if (searchBtn)    searchBtn.addEventListener('click', searchAddress);
    if (addressInput) addressInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); searchAddress(); }
    });

    // ---------- SOS ----------
    var sosBtn = document.querySelector('.sos-btn');
    if (sosBtn) {
        sosBtn.addEventListener('click', function () {
            if (window.confirm("Are you sure you want to send an SOS alert?")) {
                alert("SOS alert sent! Help is on the way.");
            }
        });
    }

    // ---------- Helpers ----------
    function escapeHtml(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // ---------- Inline feedback (status text + toast) ----------
    var searchStatus   = document.getElementById('searchStatus');
    var toastContainer = document.getElementById('toastContainer');

    function setSearchStatus(text, kind) {
        if (!searchStatus) return;
        searchStatus.textContent = text || '';
        searchStatus.className = 'search-status' + (kind ? ' is-' + kind : '');
    }

    function showToast(message, kind) {
        if (!toastContainer) return;
        var t = document.createElement('div');
        t.className = 'toast' + (kind ? ' toast-' + kind : '');
        t.textContent = message;
        toastContainer.appendChild(t);
        // Trigger CSS transition on next frame
        requestAnimationFrame(function () { t.classList.add('show'); });
        setTimeout(function () {
            t.classList.remove('show');
            setTimeout(function () { if (t.parentNode) t.parentNode.removeChild(t); }, 250);
        }, 2400);
    }

    // Wrap the existing highlightIncident to also surface a toast for feedback
    var origHighlight = window.highlightIncident;
    window.highlightIncident = function (idx, status) {
        if (typeof origHighlight === 'function') origHighlight(idx, status);
        var rep = reports[idx];
        if (rep) {
            showToast('Showing "' + (rep.incident_title || rep.incident_type || 'incident') + '" on the map', 'success');
        }
    };
})();
