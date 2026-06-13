// ============================================
// survAIval — Dashboard Script
// ============================================

// Active nav link highlight
const navLinks = document.querySelectorAll('.sidebar-nav ul li');
navLinks.forEach(function(item) {
    item.addEventListener('click', function() {
        navLinks.forEach(function(li) { li.classList.remove('active'); });
        item.classList.add('active');
    });
});

// SOS button confirmation
const sosBtn = document.querySelector('.sos-btn');
if (sosBtn) {
    sosBtn.addEventListener('click', function() {
        const confirm = window.confirm("Are you sure you want to send an SOS alert?");
        if (confirm) {
            alert("SOS alert sent! Help is on the way.");
        }
    });
}

// Emergency card click placeholder
const emergencyCards = document.querySelectorAll('.emergency-card');
emergencyCards.forEach(function(card) {
    card.addEventListener('click', function() {
        const type = card.querySelector('p').textContent;
        alert("Reporting: " + card.querySelector('img').alt + "\n(This will open the report form)");
    });
});