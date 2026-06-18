/* ============================================================
   survAIval - Incident Map
   - Leaflet map (OpenStreetMap tiles) plotting real report coords
   - List-row <-> marker sync
   - Google Maps address search via embed iframe
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
        attribution: '© OpenStreetMap contributors'
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

    // ---------- Google Maps address search (embed) ----------
    var addressInput = document.getElementById('addressSearch');
    var searchBtn    = document.getElementById('searchBtn');
    var gmapCard     = document.getElementById('gmapCard');
    var gmapFrame    = document.getElementById('gmapFrame');
    var gmapTitle    = document.getElementById('gmapTitle');

    function searchAddress() {
        var q = (addressInput.value || '').trim();
        if (!q) return;

        // Bias results to the Philippines + current barangay
        var fullQuery = q + ', ' + (config.label || 'Sto. Tomas, Batangas, Philippines');
        var url = 'https://www.google.com/maps?q=' + encodeURIComponent(fullQuery) + '&output=embed';

        gmapTitle.textContent = q;
        gmapFrame.src = url;
        gmapCard.style.display = 'block';
        gmapCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    if (searchBtn)    searchBtn.addEventListener('click', searchAddress);
    if (addressInput) addressInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); searchAddress(); }
    });

    window.closeGmap = function () {
        gmapCard.style.display = 'none';
        gmapFrame.src = '';
    };

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
})();
