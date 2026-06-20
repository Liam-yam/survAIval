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

    function setPin(latlng, label) {
        if (!marker) {
            marker = L.marker(latlng, { draggable: true }).addTo(map);
            marker.on('dragend', function (e) {
                var p = e.target.getLatLng();
                latInput.value = p.lat.toFixed(6);
                lngInput.value = p.lng.toFixed(6);
                setStatus('success', 'Pin moved to ' + p.lat.toFixed(5) + ', ' + p.lng.toFixed(5));
            });
        } else {
            marker.setLatLng(latlng);
        }
        latInput.value = latlng.lat.toFixed(6);
        lngInput.value = latlng.lng.toFixed(6);
        map.setView(latlng, Math.max(map.getZoom(), 16));
        if (label) setStatus('success', label);
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
                    setPin(latlng, 'Your current location: ' + latlng.lat.toFixed(5) + ', ' + latlng.lng.toFixed(5));
                    if (locInput && !locInput.value) {
                        locInput.value = 'Brgy. San Pablo, Sto. Tomas, Batangas';
                    }
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
    setStatus('', 'Click LOCATE to use GPS, or click anywhere on the map to drop a pin.');
})();
