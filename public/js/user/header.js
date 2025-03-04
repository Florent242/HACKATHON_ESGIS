// lucide initiating
lucide.createIcons();

// Start Challenge button click handler
if (document.querySelector('.profile-btn')) {
    document.querySelector('.profile-btn').addEventListener('click', () => {
        window.location.href = '/HACKATHON_ESGIS/public/user/profile';
    });
}

// Notification button click handler
const notificationBtn = document.querySelector('.notification-btn');
notificationBtn.addEventListener('click', () => {
    alert('Notifications coming soon!');
});

const headerDropdown = document.querySelector('.header-dropdown');
const dropdown = document.querySelector('.dropdown');

document.querySelectorAll('.main-nav li').forEach(link => {
    link.addEventListener('mouseenter', function() {
        const itemIndex = this.getAttribute('data-item'); // Get the index of the item to show
        const dropdownItems = document.querySelectorAll('.dropdown-item');
        dropdown.style.transform = `translateX(-${itemIndex * 100}%)`; // Scroll to the active item

        dropdownItems.forEach((item, index) => {
            item.classList.remove('active'); // Hide all items
            if (index == itemIndex) {
                item.classList.add('active'); // Show the relevant item
            }
        });

        // Show the dropdown
        headerDropdown.classList.add('visible'); // Add the visible class
    });

    link.addEventListener('mouseleave', function() {
        // Hide the dropdown
        headerDropdown.classList.remove('visible'); // Remove the visible class
    });
});

// Show the dropdown when mouse enters the dropdown area
headerDropdown.addEventListener('mouseenter', function() {
    this.classList.add('visible'); // Keep it visible
});

// Hide the dropdown when mouse leaves both the main nav and the dropdown
document.querySelector('.nav-container').addEventListener('mouseleave', function() {
    headerDropdown.classList.remove('visible'); // Hide it when mouse leaves
});

// Hide the dropdown when mouse leaves the dropdown area
headerDropdown.addEventListener('mouseleave', function() {
    this.classList.remove('visible'); // Hide it when mouse leaves
});