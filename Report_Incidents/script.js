// ============================================
// survAIval — Report Incidents Script
// ============================================

// ---- Form Actions ----

function submitReport() {
    document.getElementById('formAction').value = 'submit';
    document.getElementById('reportForm').submit();
}

function saveDraft() {
    document.getElementById('formAction').value = 'draft';
    document.getElementById('reportForm').submit();
}

// ---- Drafts Panel ----

function openDrafts() {
    document.getElementById('draftsOverlay').classList.add('open');
    document.getElementById('draftsPanel').classList.add('open');
}

function closeDrafts() {
    document.getElementById('draftsOverlay').classList.remove('open');
    document.getElementById('draftsPanel').classList.remove('open');
}

document.getElementById('draftsBtn').addEventListener('click', openDrafts);

// ---- Load Draft into Form ----

function loadDraft(draft) {
    document.getElementById('report_id').value      = draft.report_id     || '';
    document.getElementById('reporter_name').value  = draft.reporter_name  || '';
    document.getElementById('contact_number').value = draft.contact_number || '';
    document.getElementById('incident_title').value = draft.incident_title || '';
    document.getElementById('incident_type').value  = draft.incident_type  || '';
    document.getElementById('location').value       = draft.location       || '';
    document.getElementById('description').value    = draft.description    || '';
    closeDrafts();

    // Scroll to form top
    document.querySelector('.form-card').scrollIntoView({ behavior: 'smooth' });
}

// ---- Photo Preview ----

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

    // Reset input so same file can be re-selected
    photoInput.value = '';
});

// ---- Drag and Drop ----

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

// ---- SOS Confirmation ----

document.querySelector('.sos-btn').addEventListener('click', function () {
    if (window.confirm("Are you sure you want to send an SOS alert?")) {
        alert("SOS alert sent! Help is on the way.");
    }
});