// =========================================================
// Announcement page script
//  - Search + filter chips for the card grid
//  - Open-Meteo weather API for the Weather Outlook sidebar
//  - Modal open/close with full announcement details
// =========================================================

(function () {
    'use strict';

    /* ====================================================
       SEARCH + FILTER
    ==================================================== */
    const searchInput = document.getElementById('announceSearch');
    const filterBar   = document.getElementById('announceFilters');
    const grid        = document.getElementById('announceGrid');
    const noResults   = document.getElementById('noResults');

    let activeFilter = 'all';

    function applyFilters() {
        if (!grid) return;

        const query = (searchInput && searchInput.value || '').trim().toLowerCase();
        const cards = grid.querySelectorAll('.announce-card-item');
        let visibleCount = 0;

        cards.forEach(card => {
            const matchesFilter = activeFilter === 'all' || card.dataset.filter === activeFilter;
            const matchesSearch = !query || (card.dataset.search || '').includes(query);
            const show = matchesFilter && matchesSearch;
            card.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });

        if (noResults) {
            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    if (filterBar) {
        filterBar.addEventListener('click', function (e) {
            const btn = e.target.closest('.filter-chip');
            if (!btn) return;
            filterBar.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            activeFilter = btn.dataset.filter || 'all';
            applyFilters();
        });
    }

    /* ====================================================
       MODAL
    ==================================================== */
    const overlay   = document.getElementById('announceOverlay');
    const modal     = document.getElementById('announceModal');
    const iconWrap  = document.getElementById('modalIconWrap');
    const icon      = document.getElementById('modalIcon');
    const chip      = document.getElementById('modalChip');
    const titleEl   = document.getElementById('modalTitle');
    const dateEl    = document.getElementById('modalDate');
    const locationEl = document.getElementById('modalLocation');
    const bodyEl    = document.getElementById('modalBody');

    const CHIP_COLORS = [
        'chip-red', 'chip-blue', 'chip-orange',
        'chip-green', 'chip-purple', 'chip-gray'
    ];

    function setChipClass(el, color) {
        CHIP_COLORS.forEach(c => el.classList.remove(c));
        el.classList.add(color || 'chip-green');
    }

    function formatDate(iso) {
        const d = new Date(iso);
        if (isNaN(d.getTime())) return iso || '—';
        return d.toLocaleString('en-US', {
            month: 'short', day: 'numeric', year: 'numeric',
            hour: 'numeric', minute: '2-digit'
        });
    }

    window.openAnnouncement = function (data) {
        if (!data) return;

        icon.className = 'bi ' + (data.icon || 'bi-megaphone-fill');
        setChipClass(iconWrap, data.color);
        setChipClass(chip, data.color);
        chip.textContent = data.label || 'Notice';

        titleEl.textContent = data.title || 'Untitled';
        dateEl.textContent  = formatDate(data.created_at);
        locationEl.textContent = data.barangay ? data.barangay : 'All Barangays';
        bodyEl.textContent  = data.body || '';

        overlay.classList.add('open');
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    };

    window.closeAnnouncement = function () {
        overlay.classList.remove('open');
        modal.classList.remove('open');
        document.body.style.overflow = '';
    };

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('open')) {
            window.closeAnnouncement();
        }
    });

    /* ====================================================
       WEATHER API (Open-Meteo)
       https://api.open-meteo.com/v1/forecast
       - No API key required
       - Returns current weather + 3-day daily forecast
       - weather_code follows WMO codes; we map them to
         Bootstrap Icons + temperature + risk level
    ==================================================== */

    // WMO weather codes -> { icon, label, risk }
    // Source: https://open-meteo.com/en/docs (WMO Weather interpretation codes)
    const WMO_CODES = {
        0:  { icon: 'bi-sun-fill',           label: 'Clear',          risk: 'low' },
        1:  { icon: 'bi-cloud-sun-fill',     label: 'Mainly clear',   risk: 'low' },
        2:  { icon: 'bi-cloud-sun-fill',     label: 'Partly cloudy',  risk: 'low' },
        3:  { icon: 'bi-cloud-fill',         label: 'Overcast',       risk: 'low' },
        45: { icon: 'bi-cloud-fog-fill',     label: 'Fog',            risk: 'low' },
        48: { icon: 'bi-cloud-fog-fill',     label: 'Rime fog',       risk: 'low' },
        51: { icon: 'bi-cloud-drizzle-fill', label: 'Light drizzle',  risk: 'low' },
        53: { icon: 'bi-cloud-drizzle-fill', label: 'Drizzle',        risk: 'low' },
        55: { icon: 'bi-cloud-drizzle-fill', label: 'Heavy drizzle',  risk: 'low' },
        61: { icon: 'bi-cloud-rain-fill',    label: 'Light rain',     risk: 'moderate' },
        63: { icon: 'bi-cloud-rain-fill',    label: 'Rain',           risk: 'moderate' },
        65: { icon: 'bi-cloud-rain-heavy-fill', label: 'Heavy rain',  risk: 'high' },
        71: { icon: 'bi-cloud-snow-fill',    label: 'Light snow',     risk: 'moderate' },
        73: { icon: 'bi-cloud-snow-fill',    label: 'Snow',           risk: 'moderate' },
        75: { icon: 'bi-snow',               label: 'Heavy snow',     risk: 'high' },
        77: { icon: 'bi-cloud-snow-fill',    label: 'Snow grains',    risk: 'moderate' },
        80: { icon: 'bi-cloud-rain-fill',    label: 'Rain showers',   risk: 'moderate' },
        81: { icon: 'bi-cloud-rain-fill',    label: 'Rain showers',   risk: 'moderate' },
        82: { icon: 'bi-cloud-rain-heavy-fill', label: 'Violent showers', risk: 'high' },
        85: { icon: 'bi-cloud-snow-fill',    label: 'Snow showers',   risk: 'moderate' },
        86: { icon: 'bi-cloud-snow-fill',    label: 'Snow showers',   risk: 'high' },
        95: { icon: 'bi-cloud-lightning-rain-fill', label: 'Thunderstorm', risk: 'high' },
        96: { icon: 'bi-cloud-lightning-rain-fill', label: 'Thunderstorm + hail', risk: 'high' },
        99: { icon: 'bi-cloud-lightning-rain-fill', label: 'Severe thunderstorm', risk: 'high' }
    };

    function wmoInfo(code) {
        return WMO_CODES[code] || { icon: 'bi-cloud-fill', label: 'Unknown', risk: 'low' };
    }

    // Map weekday index (0=Sun..6=Sat) to a 3-letter label
    const DAY_LABELS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    function renderWeather(data, cityLabel) {
        const list = document.getElementById('weatherOutlookList');
        if (!list || !data || !data.daily) return;

        const codes  = data.daily.weather_code   || [];
        const maxs   = data.daily.temperature_2m_max || [];
        const mins   = data.daily.temperature_2m_min || [];
        const times  = data.daily.time           || [];

        if (!times.length) {
            list.innerHTML = '<div class="weather-row"><p class="weather-risk">No forecast data available.</p></div>';
            return;
        }

        // Worst risk across all forecast days (high > moderate > low)
        const riskRank = { low: 0, moderate: 1, high: 2 };
        const riskText = { low: 'Low', moderate: 'Moderate', high: 'High' };
        let worst = 'low';
        codes.forEach(c => {
            const r = wmoInfo(c).risk;
            if (riskRank[r] > riskRank[worst]) worst = r;
        });

        // Update the city label if geolocation succeeded
        const loc = document.getElementById('weatherLocation');
        if (loc && cityLabel) {
            loc.innerHTML = '<i class="bi bi-geo-alt-fill"></i> ' + escapeHtml(cityLabel);
        }

        // Build rows: first row = today (1 day), then pair subsequent days
        // We just render one big row showing all days for simplicity.
        const daysHtml = times.map((iso, i) => {
            const d = new Date(iso);
            const label = DAY_LABELS[d.getDay()];
            const info = wmoInfo(codes[i]);
            const temp = Math.round((maxs[i] + mins[i]) / 2);
            return `
                <div class="weather-day" title="${escapeHtml(info.label)}">
                    <span class="day-label">${label}</span>
                    <i class="bi ${info.icon} weather-icon"></i>
                    <span class="day-temp">${temp}°</span>
                </div>
            `;
        }).join('');

        list.innerHTML = `
            <div class="weather-row">
                <div class="weather-day-group">
                    ${daysHtml}
                </div>
                <p class="weather-risk" data-risk="${worst}">
                    Localized Severe Weather Risk level: <strong>${riskText[worst]}</strong>
                </p>
            </div>
        `;
    }

    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function renderWeatherError(message) {
        const list = document.getElementById('weatherOutlookList');
        if (!list) return;
        list.innerHTML = `<div class="weather-row"><p class="weather-risk" data-risk="moderate">${escapeHtml(message)}</p></div>`;
    }

    function buildWeatherUrl(lat, lng) {
        const params = new URLSearchParams({
            latitude: lat,
            longitude: lng,
            current: 'temperature_2m,weather_code',
            daily: 'weather_code,temperature_2m_max,temperature_2m_min',
            timezone: 'auto',
            forecast_days: '3'
        });
        return 'https://api.open-meteo.com/v1/forecast?' + params.toString();
    }

    async function loadWeather() {
        const cfg = window.SURVAIVAL_WEATHER;
        if (!cfg) return;

        let lat = cfg.lat;
        let lng = cfg.lng;
        let label = cfg.cityLabel;

        // Try geolocation for a more accurate forecast. If the user
        // denies or it's unavailable, fall back to the city default.
        if (navigator.geolocation) {
            try {
                const pos = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        timeout: 5000,
                        maximumAge: 10 * 60 * 1000  // cache 10 minutes
                    });
                });
                lat = pos.coords.latitude;
                lng = pos.coords.longitude;
                label = 'Your location';
            } catch (e) {
                // Silent fallback to defaults
            }
        }

        const url = buildWeatherUrl(lat, lng);

        try {
            const res = await fetch(url, { cache: 'no-store' });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            renderWeather(data, label);
        } catch (err) {
            console.warn('[survAIval] weather fetch failed:', err);
            renderWeatherError('Forecast unavailable. Please try again later.');
        }
    }

    // Kick off weather load once the DOM is ready (the script tag is
    // at the end of <body>, so this fires almost immediately).
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadWeather);
    } else {
        loadWeather();
    }

    // Refresh the forecast every 15 minutes while the page is open.
    setInterval(loadWeather, 15 * 60 * 1000);
})();