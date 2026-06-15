var toggleIds = ['sms_alerts', 'sound_alarm', 'push_notifications', 'two_factor'];

toggleIds.forEach(function (id) {
    var saved   = localStorage.getItem(id);
    var checkbox = document.getElementById(id);
    if (!checkbox) return;

    if (saved === null) {
        checkbox.checked = true;
        localStorage.setItem(id, 'true');
    } else {
        checkbox.checked = saved === 'true';
    }
});

function saveSetting(key, value) {
    localStorage.setItem(key, value ? 'true' : 'false');
}

function openBarangayModal() {
    document.getElementById('modalOverlay').classList.add('open');
    document.getElementById('barangayModal').classList.add('open');
}

function closeBarangayModal() {
    document.getElementById('modalOverlay').classList.remove('open');
    document.getElementById('barangayModal').classList.remove('open');
}

document.querySelector('.sos-btn').addEventListener('click', function () {
    if (window.confirm("Are you sure you want to send an SOS alert?")) {
        alert("SOS alert sent! Help is on the way.");
    }
});
