// ============================================
// survAIval — Hotlines Script
// ============================================

// ---- Search Filter ----

document.getElementById('searchInput').addEventListener('input', function () {
    var query    = this.value.toLowerCase().trim();
    var rows     = document.querySelectorAll('.hotline-row');
    var noResult = document.getElementById('noResults');
    var visible  = 0;

    rows.forEach(function (row) {
        var text = row.getAttribute('data-search') || '';
        if (text.includes(query)) {
            row.classList.remove('hidden');
            visible++;
        } else {
            row.classList.add('hidden');
        }
    });

    noResult.style.display = visible === 0 ? 'block' : 'none';
});

// ---- Copy Number ----

function copyNumber(number, btn) {
    navigator.clipboard.writeText(number).then(function () {
        // Change icon temporarily
        var icon = btn.querySelector('i');
        icon.className = 'bi bi-check-lg';
        btn.classList.add('copied');

        // Show toast
        showToast();

        // Reset after 2s
        setTimeout(function () {
            icon.className = 'bi bi-clipboard';
            btn.classList.remove('copied');
        }, 2000);
    });
}

function showToast() {
    var toast = document.getElementById('copyToast');
    toast.classList.add('show');
    setTimeout(function () {
        toast.classList.remove('show');
    }, 2000);
}

// ---- Call Confirmation Modal ----

function confirmCall(name, number, tel) {
    document.getElementById('modalTitle').textContent  = name;
    document.getElementById('modalNumber').textContent = number;
    document.getElementById('callLink').href           = 'tel:' + tel;

    document.getElementById('modalOverlay').classList.add('open');
    document.getElementById('callModal').classList.add('open');
}

function closeModal() {
    document.getElementById('modalOverlay').classList.remove('open');
    document.getElementById('callModal').classList.remove('open');
}

document.getElementById('modalOverlay').addEventListener('click', closeModal);

// ---- SOS Confirmation ----

document.querySelector('.sos-btn').addEventListener('click', function () {
    if (window.confirm("Are you sure you want to send an SOS alert?")) {
        alert("SOS alert sent! Help is on the way.");
    }
});