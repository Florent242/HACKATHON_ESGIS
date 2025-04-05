// Start Challenge button click handler
if (document.querySelector('.profile-btn')) {
    document.querySelector('.profile-btn').addEventListener('click', () => {
        window.location.href = '/HACKATHON_ESGIS/public/user/profile';
    });
}

/* Handle scroll annimations for elements */
// Select all element that have the .fade-in class for the animation when they are visible
const fadeElements = document.querySelectorAll('.fade-in, .fade-out, .fade-in-left, .fade-in-right');// Intersection Observer to trigger the animation when the element is visible...hehe that's cool tho
const heroObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
        else {
            entry.target.classList.remove('visible');
        }
    });
}, {
    root: null, // Utilise la fenêtre de visualisation
    threshold: 0.1 // Déclenche l'événement lorsque 10% de l'élément est visible
});

if (fadeElements) {
    fadeElements.forEach(element => {
        heroObserver.observe(element);
    });
}

// Notification button click handler
const notificationBtn = document.querySelector('.notification-btn');
notificationBtn.addEventListener('click', () => {
    alert('Notifications coming soon!');
});

// Dropdown menu handling
const headerDropdown = document.querySelector('.header-dropdown');
const dropdown = document.querySelector('.dropdown');

document.querySelectorAll('.main-nav li').forEach(link => {
    link.addEventListener('mouseenter', function () {
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

    link.addEventListener('mouseleave', function () {
        // Hide the dropdown
        headerDropdown.classList.remove('visible'); // Remove the visible class
    });
});

// Show the dropdown when mouse enters the dropdown area
headerDropdown.addEventListener('mouseenter', function () {
    this.classList.add('visible'); // Keep it visible
});

// Hide the dropdown when mouse leaves both the main nav and the dropdown
document.querySelector('.nav-container').addEventListener('mouseleave', function () {
    headerDropdown.classList.remove('visible'); // Hide it when mouse leaves
});

// Hide the dropdown when mouse leaves the dropdown area
headerDropdown.addEventListener('mouseleave', function () {
    this.classList.remove('visible'); // Hide it when mouse leaves
});
document.addEventListener('DOMContentLoaded', async () => {
    const logoutBtn = document.querySelector('.logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async () => {
            try{

                const data = await apiRequest('/auth/logout', {
                    method: 'POST'
                })
                
                if (data.success) {
                    window.location.href = '/HACKATHON_ESGIS/public';
                } else {
                    setFlashMessage('error', 'Echec de déconnexion',data.message);
                    return;
                }
            } catch (error) {
                setFlashMessage('error', 'Echec de déconnexion',error.message);
                console.error('Logout failed:', error);
            }
        });
    }
})