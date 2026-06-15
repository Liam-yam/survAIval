function togglePass(fieldId, btn) {
    var field = document.getElementById(fieldId);
    var icon  = btn.querySelector('i');

    if (field.type === 'password') {
        field.type    = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        field.type    = 'password';
        icon.className = 'bi bi-eye';
    }
}

document.querySelector('.sos-btn').addEventListener('click', function () {
    if (window.confirm("Are you sure you want to send an SOS alert?")) {
        alert("SOS alert sent! Help is on the way.");
    }
});
