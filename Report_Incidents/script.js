function submitReport() {
    document.getElementById('formAction').value = 'submit';
    document.getElementById('reportForm').submit();
}

function saveDraft() {
    document.getElementById('formAction').value = 'draft';
    document.getElementById('reportForm').submit();
}

function openDrafts() {
    document.getElementById('draftsOverlay').classList.add('open');
    document.getElementById('draftsPanel').classList.add('open');
}

function closeDrafts() {
    document.getElementById('draftsOverlay').classList.remove('open');
    document.getElementById('draftsPanel').classList.remove('open');
}

document.getElementById('draftsBtn').addEventListener('click', openDrafts);

function loadDraft(draft) {
    document.getElementById('report_id').value      = draft.report_id     || '';
    document.getElementById('reporter_name').value  = draft.reporter_name  || '';
    document.getElementById('contact_number').value = draft.contact_number || '';
    document.getElementById('incident_title').value = draft.incident_title || '';
    document.getElementById('incident_type').value  = draft.incident_type  || '';
    document.getElementById('location').value       = draft.location       || '';
    document.getElementById('description').value    = draft.description    || '';
    closeDrafts();

    document.querySelector('.form-card').scrollIntoView({ behavior: 'smooth' });
}

const photoInput       = document.getElementById('photoInput');
const photoPreviewGrid = document.getElementById('photoPreviewGrid');
let selectedFiles      = [];

photoInput.addEventListener('change', function () {
    const newFiles = Array.from(this.files);
    newFiles.forEach(function (file) {
        if (!file.type.startsWith('image/') && !file.type.startsWith('video/')) return;

        selectedFiles.push(file);
        const reader = new FileReader();
        reader.onload = function (e) {
            const item = document.createElement('div');
            item.classList.add('preview-item');
            item.dataset.name = file.name;

            const img = document.createElement('img');
            img.src = e.target.result;

            const removeBtn = document.createElement('button');
            removeBtn.classList.add('remove-photo');
            removeBtn.innerHTML = '<i class="bi bi-x"></i>';
            removeBtn.addEventListener('click', function () {
                selectedFiles = selectedFiles.filter(function (f) { return f.name !== file.name; });
                item.remove();
            });

            item.appendChild(img);
            item.appendChild(removeBtn);
            photoPreviewGrid.appendChild(item);
        };
        reader.readAsDataURL(file);
    });

    photoInput.value = '';
});

const uploadArea = document.getElementById('uploadArea');

uploadArea.addEventListener('dragover', function (e) {
    e.preventDefault();
    uploadArea.style.borderColor = '#2d5a27';
    uploadArea.style.backgroundColor = '#f5fbf5';
});

uploadArea.addEventListener('dragleave', function () {
    uploadArea.style.borderColor = '';
    uploadArea.style.backgroundColor = '';
});

uploadArea.addEventListener('drop', function (e) {
    e.preventDefault();
    uploadArea.style.borderColor = '';
    uploadArea.style.backgroundColor = '';

    const droppedFiles = Array.from(e.dataTransfer.files);
    droppedFiles.forEach(function (file) {
        if (!file.type.startsWith('image/') && !file.type.startsWith('video/')) return;

        selectedFiles.push(file);
        const reader = new FileReader();
        reader.onload = function (ev) {
            const item = document.createElement('div');
            item.classList.add('preview-item');

            const img = document.createElement('img');
            img.src = ev.target.result;

            const removeBtn = document.createElement('button');
            removeBtn.classList.add('remove-photo');
            removeBtn.innerHTML = '<i class="bi bi-x"></i>';
            removeBtn.addEventListener('click', function () {
                item.remove();
            });

            item.appendChild(img);
            item.appendChild(removeBtn);
            photoPreviewGrid.appendChild(item);
        };
        reader.readAsDataURL(file);
    });
});

document.querySelector('.sos-btn').addEventListener('click', function () {
    if (window.confirm("Are you sure you want to send an SOS alert?")) {
        alert("SOS alert sent! Help is on the way.");
    }
});

/* ============================================================
   GEOLOCATION + LEAFLET MAP (Report Form)
   ============================================================ */
(function () {
    'use strict';

    var config    = window.SURVAIVAL_MAP_CENTER || { lat: 14.1074, lng: 121.1416, label: '' };
    var statusEl  = document.getElementById('locationStatus');
    var latInput  = document.getElementById('latitude');
    var lngInput  = document.getElementById('longitude');
    var locInput  = document.getElementById('location');
    var mapEl     = document.getElementById('reportMap');

    if (!mapEl || typeof L === 'undefined') return;

                        function setStatus(kind, message) {
        if (!statusEl) return;
        statusEl.className = 'location-status ' + (kind || '');
        statusEl.innerHTML = '<i class="bi bi-' +
            (kind === 'success' ? 'check-circle-fill' :
             kind === 'error'   ? 'exclamation-triangle-fill' :
             kind === 'warn'    ? 'exclamation-circle-fill' :
             kind === 'loading' ? 'hourglass-split' : 'info-circle') +
            '"></i><span>' + message + '</span>';
    }

    // Initialize Leaflet map
    var map = L.map('reportMap', {
        center: [config.lat, config.lng],
        zoom: 15,
        scrollWheelZoom: true
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '� OpenStreetMap'
    }).addTo(map);

                    var marker = null;
    var lastResolved = { lat: null, lng: null };
    var geocodeToken  = 0; // used to ignore stale reverse-geocode responses

    // Reverse geocode lat/lng -> human-readable address using Nominatim
    function reverseGeocode(lat, lng) {
        var myToken = ++geocodeToken;
        // Show a "resolving" state immediately, but don't overwrite a real address
        setStatus('loading', 'Resolving address for ' + lat.toFixed(5) + ', ' + lng.toFixed(5) + '…');

        var url = 'https://nominatim.openstreetmap.org/reverse'
                + '?format=json&lat=' + encodeURIComponent(lat)
                + '&lon=' + encodeURIComponent(lng)
                + '&zoom=18&addressdetails=1';

        return fetch(url, {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            // Ignore stale responses from older pin positions
            if (myToken !== geocodeToken) return null;

            if (!data || data.error) {
                setStatus('warn', 'Pin set. Could not resolve an address — please type one.');
                return null;
            }
            var address = data.display_name || '';
            if (locInput && address) {
                locInput.value = address;
            }
            setStatus('success', 'Pin set: ' + address);
            return address;
        })
        .catch(function (err) {
            if (myToken !== geocodeToken) return null;
            console.warn('[survAIval] Reverse geocode failed:', err);
            setStatus('warn', 'Pin set, but address lookup failed. Please type the address.');
            return null;
        });
    }

    function setPin(latlng, label) {
        if (!marker) {
            marker = L.marker(latlng, { draggable: true }).addTo(map);
            marker.on('dragend', function (e) {
                var p = e.target.getLatLng();
                latInput.value = p.lat.toFixed(6);
                lngInput.value = p.lng.toFixed(6);
                reverseGeocode(p.lat, p.lng);
            });
        } else {
            marker.setLatLng(latlng);
        }
        latInput.value = latlng.lat.toFixed(6);
        lngInput.value = latlng.lng.toFixed(6);
        map.setView(latlng, Math.max(map.getZoom(), 16));

        if (label) setStatus('success', label);
        // Resolve the address and write it into the Location text box
        reverseGeocode(latlng.lat, latlng.lng);
    }

    // Click-to-pin
    map.on('click', function (e) {
        setPin(e.latlng, 'Pinned at ' + e.latlng.lat.toFixed(5) + ', ' + e.latlng.lng.toFixed(5));
    });

    // LOCATE button: browser geolocation
    var locateBtn = document.getElementById('locateBtn');
    if (locateBtn) {
        locateBtn.addEventListener('click', function () {
            if (!navigator.geolocation) {
                setStatus('error', 'Geolocation is not supported by your browser.');
                return;
            }
            locateBtn.disabled = true;
            setStatus('loading', 'Detecting your location...');
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    locateBtn.disabled = false;
                    var latlng = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                    setPin(latlng, 'Your current location detected.');
                },
                function (err) {
                    locateBtn.disabled = false;
                    var msg = 'Could not detect your location.';
                    if (err.code === 1) msg = 'Location permission denied. Please click on the map instead.';
                    else if (err.code === 2) msg = 'Location unavailable. Click on the map to set the pin.';
                    else if (err.code === 3) msg = 'Location request timed out. Try again or click on the map.';
                    setStatus('error', msg);
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
            );
        });
    }

        // Initial status
    setStatus('', 'Search an address above, click LOCATE, or click anywhere on the map to drop a pin.');

    // ============================================================
    // LOCATION SEARCH + AUTOCOMPLETE
    // ============================================================
    var suggestEl   = document.getElementById('locationSuggest');
    var spinnerEl   = document.getElementById('locationSpinner');
    var clearBtnEl  = document.getElementById('locationClear');
    var searchToken = 0;          // race-guard for autocomplete
    var debounceId  = null;
    var suggestions = [];         // current list of result objects
    var activeIdx   = -1;         // keyboard-highlighted suggestion
    var lastQuery   = '';

    function setSpinner(on) {
        if (spinnerEl) spinnerEl.classList.toggle('show', !!on);
        if (clearBtnEl) clearBtnEl.classList.toggle('show', !!on ? false : !!(locInput && locInput.value));
    }

    function hideSuggestions() {
        if (!suggestEl) return;
        suggestEl.classList.remove('show');
        suggestEl.innerHTML = '';
        suggestions = [];
        activeIdx = -1;
    }

    function escapeHtml(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function escapeRegex(s) {
        return String(s).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function highlightMatch(text, query) {
        if (!query) return escapeHtml(text);
        var safe = escapeHtml(text);
        var re   = new RegExp('(' + escapeRegex(query) + ')', 'ig');
        return safe.replace(re, '<mark>$1</mark>');
    }

    function renderSuggestions(items, query) {
        if (!suggestEl) return;
        if (!items || items.length === 0) {
            suggestEl.innerHTML = '<li class="loc-empty">No matches found</li>';
            suggestEl.classList.add('show');
            return;
        }
        suggestEl.innerHTML = items.map(function (it, i) {
            var parts = it.display_name.split(',');
            var main  = (parts[0] + (parts[1] ? ', ' + parts[1] : '')).trim() || it.display_name;
            var rest  = it.display_name;
            return '<li class="loc-item" role="option" data-idx="' + i + '" tabindex="-1">'
                 + '<i class="bi bi-geo-alt-fill"></i>'
                 + '<div class="loc-text">'
                 +   '<div class="loc-main">' + highlightMatch(main, query) + '</div>'
                 +   '<div class="loc-sub">'  + highlightMatch(rest,  query) + '</div>'
                 + '</div>'
                 + '</li>';
        }).join('');
        suggestEl.classList.add('show');
    }

    function setActiveSuggestion(idx) {
        if (!suggestEl) return;
        var items = suggestEl.querySelectorAll('.loc-item');
        items.forEach(function (el, i) {
            el.classList.toggle('active', i === idx);
            if (i === idx) el.scrollIntoView({ block: 'nearest' });
        });
        activeIdx = idx;
    }

    function pickSuggestion(idx) {
        var it = suggestions[idx];
        if (!it) return;
        var lat = parseFloat(it.lat);
        var lng = parseFloat(it.lon);
        if (!isFinite(lat) || !isFinite(lng)) return;

        if (locInput) locInput.value = it.display_name;
        setSpinner(false);
        hideSuggestions();
        setPin({ lat: lat, lng: lng }, 'Selected: ' + it.display_name);
    }

    function runSearch(query) {
        query = (query || '').trim();
        lastQuery = query;
        if (query.length < 3) {
            hideSuggestions();
            setSpinner(false);
            return;
        }

        var myToken = ++searchToken;
        setSpinner(true);

        // Build a bounded viewbox around the user's barangay so results stay local.
        var center = (config && isFinite(config.lat) && isFinite(config.lng))
                   ? { lat: config.lat, lng: config.lng }
                   : { lat: 14.1074,    lng: 121.1416 };
        var dLat = 0.05;
        var dLng = 0.05;
        var viewbox = (center.lng - dLng) + ',' +
                      (center.lat + dLat) + ',' +
                      (center.lng + dLng) + ',' +
                                            (center.lat - dLat);

        var url = 'https://nominatim.openstreetmap.org/search'
                + '?format=json&addressdetails=1&limit=6'
                + '&countrycodes=ph'
                + '&viewbox=' + encodeURIComponent(viewbox)
                + '&bounded=1'
                + '&q=' + encodeURIComponent(query);

        fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (myToken !== searchToken) return;
            suggestions = Array.isArray(data) ? data : [];
            renderSuggestions(suggestions, lastQuery);
            setSpinner(false);
        })
        .catch(function (err) {
            if (myToken !== searchToken) return;
            console.warn('[survAIval] Autocomplete search failed:', err);
            setSpinner(false);
            hideSuggestions();
            setStatus('warn', 'Address search failed. Check your network and try again.');
        });
    }

    if (locInput) {
        locInput.addEventListener('input', function () {
            if (clearBtnEl) clearBtnEl.classList.toggle('show', !!locInput.value);
            if (debounceId) clearTimeout(debounceId);
            debounceId = setTimeout(function () { runSearch(locInput.value); }, 350);
        });

        locInput.addEventListener('keydown', function (e) {
            var open = suggestEl && suggestEl.classList.contains('show');
            var itemCount = suggestEl ? suggestEl.querySelectorAll('.loc-item').length : 0;

            if (e.key === 'ArrowDown') {
                if (!open) return;
                e.preventDefault();
                setActiveSuggestion(Math.min(activeIdx + 1, itemCount - 1));
            } else if (e.key === 'ArrowUp') {
                if (!open) return;
                e.preventDefault();
                setActiveSuggestion(Math.max(activeIdx - 1, 0));
            } else if (e.key === 'Enter') {
                if (open && activeIdx >= 0 && suggestions[activeIdx]) {
                    e.preventDefault();
                    pickSuggestion(activeIdx);
                } else if (locInput.value.trim().length >= 3) {
                    e.preventDefault();
                    if (debounceId) clearTimeout(debounceId);
                    runSearch(locInput.value);
                }
            } else if (e.key === 'Escape') {
                hideSuggestions();
            }
        });

        locInput.addEventListener('focus', function () {
            if (locInput.value.trim().length >= 3 && suggestions.length > 0) {
                renderSuggestions(suggestions, lastQuery);
            }
        });

        locInput.addEventListener('blur', function () {
            // delay so a click on a suggestion still registers
            setTimeout(function () { hideSuggestions(); }, 180);
        });
    }

    if (suggestEl) {
        suggestEl.addEventListener('mousedown', function (e) {
            var li = e.target.closest('.loc-item');
            if (!li) return;
            e.preventDefault();
            var idx = parseInt(li.getAttribute('data-idx'), 10);
            if (!isNaN(idx)) pickSuggestion(idx);
        });

        suggestEl.addEventListener('mousemove', function (e) {
            var li = e.target.closest('.loc-item');
            if (!li) return;
            var idx = parseInt(li.getAttribute('data-idx'), 10);
            if (!isNaN(idx)) setActiveSuggestion(idx);
        });
    }

    if (clearBtnEl) {
        clearBtnEl.addEventListener('click', function () {
            if (!locInput) return;
            locInput.value = '';
            hideSuggestions();
            clearBtnEl.classList.remove('show');
            locInput.focus();
            setStatus('', 'Location cleared. Search, click LOCATE, or pick on the map.');
        });
    }

    // Hide suggestions when clicking outside the search wrapper
    document.addEventListener('click', function (e) {
        var wrap = document.querySelector('.location-search');
        if (wrap && !wrap.contains(e.target)) hideSuggestions();
    });
})();
