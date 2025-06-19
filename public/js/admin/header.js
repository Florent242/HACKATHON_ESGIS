document.addEventListener('DOMContentLoaded', function() {
    // Initialize all elements that need event listeners
    initializeHeaderElements();
});

function initializeHeaderElements() {
    // Example: Toggle sidebar
    const sidebarToggle = document.querySelector('#sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
        });
    }

    // Example: Toggle user dropdown
    const userDropdown = document.querySelector('#user-dropdown-toggle');
    if (userDropdown) {
        userDropdown.addEventListener('click', function() {
            document.querySelector('#user-dropdown').classList.toggle('show');
        });
    }

    // Example: Toggle notifications
    const notificationToggle = document.querySelector('#notification-toggle');
    if (notificationToggle) {
        notificationToggle.addEventListener('click', function() {
            document.querySelector('#notification-dropdown').classList.toggle('show');
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const dropdowns = document.querySelectorAll('.dropdown-menu.show');
        dropdowns.forEach(function(dropdown) {
            if (!dropdown.contains(event.target) && !event.target.matches('.dropdown-toggle')) {
                dropdown.classList.remove('show');
            }
        });
    });
}