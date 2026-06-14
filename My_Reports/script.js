// ============================================
// survAIval — My Reports Script
// ============================================

// ---- Modal Open/Close ----

function openModal(report) {
    // Populate details
    document.getElementById('modalTitle').textContent       = report.incident_title || '—';
    document.getElementById('modalType').textContent        = report.incident_type  || '—';
    document.getElementById('modalLocation').textContent    = report.location       || '—';
    document.getElementById('modalReporter').textContent    = report.reporter_name  || '—';
    document.getElementById('modalContact').textContent     = report.contact_number || '—';
    document.getElementById('modalDescription').textContent = report.description    || '—';

    // Photo
    var photoWrap = document.getElementById('modalPhotoWrap');
    var photoImg  = document.getElementById('modalPhoto');
    if (report.photo && report.photo !== '') {
        photoImg.src        = '../' + report.photo;
        photoWrap.style.display = 'block';
    } else {
        photoWrap.style.display = 'none';
    }

    // Status tracker
    updateTracker(report.status);

    // Open
    document.getElementById('modalOverlay').classList.add('open');
    document.getElementById('reportModal').classList.add('open');
}

function closeModal() {
    document.getElementById('modalOverlay').classList.remove('open');
    document.getElementById('reportModal').classList.remove('open');
}

// ---- Status Tracker Logic ----

function updateTracker(status) {
    var stepReported   = document.getElementById('step-reported');
    var stepResponding = document.getElementById('step-responding');
    var stepResolved   = document.getElementById('step-resolved');

    // Reset all
    stepReported.className   = 'tracker-step';
    stepResponding.className = 'tracker-step';
    stepResolved.className   = 'tracker-step';

    if (status === 'pending') {
        stepReported.classList.add('current');
    }

    if (status === 'responding') {
        stepReported.classList.add('done');
        stepResponding.classList.add('current');
    }

    if (status === 'resolved') {
        stepReported.classList.add('done');
        stepResponding.classList.add('done');
        stepResolved.classList.add('done');
    }
}

// ---- SOS Confirmation ----

document.querySelector('.sos-btn').addEventListener('click', function () {
    if (window.confirm("Are you sure you want to send an SOS alert?")) {
        alert("SOS alert sent! Help is on the way.");
    }
});