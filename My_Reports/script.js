// ============================================
// survAIval — My Reports Script
// ============================================

// ---- Filter Dropdown ----

const filterBtn      = document.getElementById('filterBtn');
const filterDropdown = document.getElementById('filterDropdown');

filterBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    filterDropdown.classList.toggle('open');
});

document.addEventListener('click', function () {
    filterDropdown.classList.remove('open');
});

// ---- Modal ----

const modalOverlay = document.getElementById('modalOverlay');
const reportModal  = document.getElementById('reportModal');

function openModal(report) {
    // Fill in report details
    document.getElementById('modalTitle').textContent    = report.incident_title || 'Report Details';
    document.getElementById('modalType').textContent     = report.incident_type  || '—';
    document.getElementById('modalLocation').textContent = report.location       || '—';
    document.getElementById('modalReporter').textContent = report.reporter_name  || '—';
    document.getElementById('modalContact').textContent  = report.contact_number || '—';
    document.getElementById('modalDesc').textContent     = report.description    || '—';

    // Photo
    const photoWrap = document.getElementById('modalPhotoWrap');
    const photoImg  = document.getElementById('modalPhoto');
    if (report.photo) {
        photoImg.src       = '../' + report.photo;
        photoWrap.style.display = 'block';
    } else {
        photoWrap.style.display = 'none';
    }

    // Status Tracker
    updateTracker(report.status);

    modalOverlay.classList.add('open');
    reportModal.classList.add('open');
}

function closeModal() {
    modalOverlay.classList.remove('open');
    reportModal.classList.remove('open');
}

function updateTracker(status) {
    const stepReported   = document.getElementById('step-reported');
    const stepResponding = document.getElementById('step-responding');
    const stepResolved   = document.getElementById('step-resolved');
    const lineResponding = document.getElementById('line-responding');
    const lineResolved   = document.getElementById('line-resolved');

    // Reset all
    [stepReported, stepResponding, stepResolved].forEach(function (s) {
        s.classList.remove('reported', 'active', 'done');
    });
    [lineResponding, lineResolved].forEach(function (l) {
        l.classList.remove('done');
    });

    if (status === 'pending') {
        stepReported.classList.add('reported');

    } else if (status === 'responding') {
        stepReported.classList.add('done');
        lineResponding.classList.add('done');
        stepResponding.classList.add('active');

    } else if (status === 'resolved') {
        stepReported.classList.add('done');
        lineResponding.classList.add('done');
        stepResponding.classList.add('done');
        lineResolved.classList.add('done');
        stepResolved.classList.add('done');
    }
}

// ---- SOS ----

document.querySelector('.sos-btn').addEventListener('click', function () {
    if (window.confirm("Are you sure you want to send an SOS alert?")) {
        alert("SOS alert sent! Help is on the way.");
    }
});