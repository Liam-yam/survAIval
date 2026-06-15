function openEditModal() {
    document.getElementById('modalOverlay').classList.add('open');
    document.getElementById('editModal').classList.add('open');
}

function closeEditModal() {
    document.getElementById('modalOverlay').classList.remove('open');
    document.getElementById('editModal').classList.remove('open');
}

document.getElementById('modalOverlay').addEventListener('click', closeEditModal);

function previewPhoto(input) {
    if (!input.files || !input.files[0]) return;

    var reader   = new FileReader();
    var preview  = document.getElementById('photoPreview');
    var fallback = document.getElementById('avatarFallback');

    reader.onload = function (e) {
        preview.src          = e.target.result;
        preview.style.display = 'block';
        if (fallback) fallback.style.display = 'none';
    };

    reader.readAsDataURL(input.files[0]);
}

var timeFormatSelect = document.getElementById('timeFormat');

if (timeFormatSelect) {
    var saved = localStorage.getItem('time_format') || '12hr';
    timeFormatSelect.value = saved;
}

function saveTimeFormat(value) {
    localStorage.setItem('time_format', value);
}

document.querySelector('.sos-btn').addEventListener('click', function () {
    if (window.confirm("Are you sure you want to send an SOS alert?")) {
        alert("SOS alert sent! Help is on the way.");
    }
});
