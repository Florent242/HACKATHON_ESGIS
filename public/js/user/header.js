import { createEle } from "../dom.js";

// Handle scroll annimations for elements 
// Select all element that have the .fade-in class for the animation when they are visible
const fadeElements = document.querySelectorAll('.fade-in, .fade-out, .fade-in-left, .fade-in-right');
// Intersection Observer to trigger the animation when the element is visible...hehe that's cool tho
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
    showNotification('Les Notifications sont en cours de développement', '', 'info', 3000);
});

// Helper function to calculate the total height needed
function calculateTotalHeight(element) {
    // Get the computed styles of the element
    const style = window.getComputedStyle(element);
    const paddingTop = parseFloat(style.paddingTop);
    const paddingBottom = parseFloat(style.paddingBottom);
    const borderTop = parseFloat(style.borderTopWidth);
    const borderBottom = parseFloat(style.borderBottomWidth);
    const marginTop = parseFloat(style.marginTop);
    const marginBottom = parseFloat(style.marginBottom);

    // Calculate total height needed (element height + padding + borders + margins)
    return element.offsetHeight +
        paddingTop +
        paddingBottom +
        borderTop +
        borderBottom +
        marginTop +
        marginBottom;
}

// Setup a MutationObserver to watch for DOM changes inside dropdown
const dropdownObserver = new MutationObserver((mutations) => {
    const activeItem = document.querySelector('.dropdown-item.active');
    if (activeItem) {
        const newHeight = calculateTotalHeight(activeItem);
        dropdown.style.height = `${newHeight}px`;
        dropdownContainer.style.height = `${newHeight}px`;
    }
});

// Start observing the dropdown
const dropdownItems = document.querySelectorAll('.dropdown-item');
if (dropdownItems) {
    dropdownItems.forEach(item => {
        dropdownObserver.observe(item, {
            childList: true,
            subtree: true,
            attributes: true,
            characterData: true
        });
    });
}

// Dropdown menu handling
const headerDropdown = document.querySelector('.header-dropdown');
const dropdown = document.querySelector('.dropdown');
const dropdownContainer = document.querySelector('.dropdown-container');

document.querySelectorAll('.main-nav li').forEach(link => {
    link.addEventListener('mouseenter', function () {
        const itemIndex = this.getAttribute('data-item'); // Get the index of the item to show
        const dropdownItems = document.querySelectorAll('.dropdown-item');

        dropdownItems.forEach((item, index) => {
            item.classList.remove('active');
            if (index == itemIndex) {
                item.classList.add('active');
            }
        });

        headerDropdown.classList.add('visible');
        const activeItem = document.querySelector('.dropdown-item.active');

        // Calculate initial height
        const initialHeight = calculateTotalHeight(dropdownItems[itemIndex]);
        if (activeItem) {
            const height = calculateTotalHeight(activeItem);
            console.log(height);
        }
        dropdown.style.height = `${initialHeight}px`;
        dropdownContainer.style.height = `${initialHeight}px`;

        dropdown.style.transform = `translateX(-${itemIndex * 100}%)`;

        setTimeout(() => {
            const activeItem = dropdownItems[itemIndex];
            if (activeItem) {
                const newHeight = calculateTotalHeight(activeItem);
                dropdownObserver.disconnect();
                dropdown.style.height = `${newHeight}px`;
                dropdownContainer.style.height = `${newHeight}px`;
                dropdownObserver.observe(activeItem, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    characterData: true
                });
            }
        }, 50);
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

// Configuration for the observer
const observerConfig = {
    childList: true,      // Watch for changes to the child elements
    subtree: true,        // Watch all descendants, not just direct children
    attributes: true,     // Watch for changes to attributes
    characterData: true   // Watch for changes to text content
};

document.addEventListener('DOMContentLoaded', async () => {

    //deco's modal window
    const decoInitModal = document.querySelector('#deco');

    const modal = document.querySelector('#fenetre_modal');
    const annuler = document.querySelector('#fermer_modal');

    //modal window for logout
    decoInitModal.addEventListener('click', (e) => {
        e.preventDefault();
        modal.classList.toggle('show');
    });

    annuler.addEventListener('click', () => {
        modal.classList.toggle('show');
    });

    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.classList.toggle('show');
        }
    });

    // Logout button click handler
    const logoutBtn = document.querySelector('#logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async () => {
            try {

                const data = await apiRequest('/auth/logout', {
                    method: 'POST'
                })

                if (data.success) {
                    window.location.href = '/HACKATHON_ESGIS/public';
                } else {
                    setFlashMessage('error', 'Echec de déconnexion', data.message);
                    return;
                }
            } catch (error) {
                setFlashMessage('error', 'Echec de déconnexion', error.message);
                console.error('Logout failed:', error);
            }
        });
    }
})