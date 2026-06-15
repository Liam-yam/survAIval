var activeIndex = null;

function highlightIncident(index, status) {

    var items = document.querySelectorAll('.incident-item');
    items.forEach(function (item) {
        item.classList.remove('highlighted');
    });

    var selected = document.getElementById('incident-' + index);
    if (selected) {
        selected.classList.add('highlighted');

        selected.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    var activeDot = document.getElementById('activeDot');
    if (activeDot) {
        activeDot.className = 'map-dot pulse-dot';
        if (status === 'pending')    activeDot.classList.add('dot-red');
        if (status === 'responding') activeDot.classList.add('dot-orange');
        if (status === 'resolved')   activeDot.classList.add('dot-green');
    }

    var location = selected.querySelector('.incident-location');
    var title    = selected.querySelector('.incident-title');
    var pinHighlight = document.getElementById('mapPinHighlight');
    var pinLabel     = document.getElementById('mapPinLabel');

    if (pinLabel && location) {
        pinLabel.textContent = location.textContent.trim();
    }
    if (pinHighlight) {
        pinHighlight.classList.add('show');
    }

    activeIndex = index;
}

document.addEventListener('click', function (e) {
    var list = document.getElementById('incidentList');
    var pin  = document.getElementById('mapPinHighlight');

    if (list && !list.contains(e.target)) {
        var items = document.querySelectorAll('.incident-item');
        items.forEach(function (item) { item.classList.remove('highlighted'); });

        if (pin) pin.classList.remove('show');
        activeIndex = null;
    }
});

document.querySelector('.sos-btn').addEventListener('click', function () {
    if (window.confirm("Are you sure you want to send an SOS alert?")) {
        alert("SOS alert sent! Help is on the way.");
    }
});
