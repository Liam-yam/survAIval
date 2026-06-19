function switchTab(tabId, element) {
    document.querySelectorAll('.dashboard-tab').forEach(tab => tab.classList.remove('active-tab'));
    document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
    
    document.getElementById(tabId).classList.add('active-tab');
    element.classList.add('active');
}

function openAnnouncementModal() {
    document.getElementById('form_announcement_id').value = '';
    document.getElementById('form_category').value = 'Disaster';
    document.getElementById('form_title').value = '';
    document.getElementById('form_content').value = '';
    document.getElementById('modalTitle').innerText = 'Compose Board Announcement';
    document.getElementById('announcementModal').style.display = 'flex';
}

function closeAnnouncementModal() {
    document.getElementById('announcementModal').style.display = 'none';
}

function editAnnouncement(data) {
    document.getElementById('form_announcement_id').value = data.announcement_id || data.id;
    document.getElementById('form_category').value = data.category;
    document.getElementById('form_title').value = data.title;
    document.getElementById('form_content').value = data.content;
    document.getElementById('modalTitle').innerText = 'Update Board Announcement';
    document.getElementById('announcementModal').style.display = 'flex';
}

function togglePositionField() {
    const accountType = document.getElementById('account_type').value;
    const positionContainer = document.getElementById('position_container');
    const positionSelect = document.getElementById('position_select');

    if (accountType === 'Admin') {
        positionContainer.style.display = 'block';
        positionSelect.setAttribute('required', 'required');
    } else {
        positionContainer.style.display = 'none';
        positionSelect.removeAttribute('required');
    }
}