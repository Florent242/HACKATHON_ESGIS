// lucide initiating
lucide.createIcons();

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

/**
 * Affiche une notification.
 * @param {string} message - Le message à afficher.
 * @param {string} details - Les détails de la notification (optionnel).
 * @param {string} type - Le type de notification ('success', 'error', 'info', 'warning').
 * @param {number} duration - Durée en millisecondes avant disparition (optionnel).
 */
function showNotification(message, details = null, type = 'success', duration = 5000) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 ${type === 'success' ? 'left-1/2' : 'right-[1%]'} transform ${type === 'success' ? '-translate-x-1/2' : '-translate-x-[1%]'} max-w-md w-auto ${type === 'success' ? 'bg-green-700' : type === 'error' ? 'bg-red-700' : type === 'warning' ? 'bg-yellow-700' : 'bg-blue-700'} rounded-lg shadow-lg p-2 flex items-start animate-fade-in z-200`;

    // Conteneur d'icône
    const iconContainer = document.createElement('div');
    iconContainer.className = 'flex-shrink-0 mr-3';

    // Icône Lucide
    const icon = document.createElement('i');
    icon.setAttribute('data-lucide',
        type === 'success' ? 'check-circle' :
            type === 'error' ? 'circle-x' :
                type === 'warning' ? 'alert-triangle' :
                    'info'
    );
    icon.className = `w-4 h-4 ${type === 'success' ? 'text-green-500' :
            type === 'error' ? 'text-red-500' :
                type === 'warning' ? 'text-yellow-500' :
                    'text-blue-500'
        }`;

    iconContainer.appendChild(icon);
    notification.appendChild(iconContainer);

    // Contenu du texte
    const textContainer = document.createElement('div');
    textContainer.className = 'flex-1';

    // Message principal
    const messageElement = document.createElement('h3');
    messageElement.className = 'text-white font-semibold';
    messageElement.innerText = message;
    textContainer.appendChild(messageElement);

    // Message de détails (en option)
    if (details) {
        const detailsElement = document.createElement('p');
        detailsElement.className = 'text-white font-light text-sm mt-0.5';
        detailsElement.innerText = details;
        textContainer.appendChild(detailsElement);
    }

    notification.appendChild(textContainer);

    // Bouton de fermeture
    const closeContainer = document.createElement('div');
    closeContainer.className = 'flex-shrink-0 ml-3';

    const closeButton = document.createElement('button');
    closeButton.className = 'text-gray-400 hover:text-gray-500 focus:outline-none';

    const closeIcon = document.createElement('i');
    closeIcon.setAttribute('data-lucide', 'x');
    closeIcon.className = 'w-5 h-5';

    closeButton.appendChild(closeIcon);
    closeButton.addEventListener('click', () => {
        hideNotification(notification);
    });

    closeContainer.appendChild(closeButton);
    notification.appendChild(closeContainer);

    // Ajouter la notification au DOM
    document.body.appendChild(notification);

    // Initialiser Lucide pour les nouvelles icônes
    if (window.lucide) {
        window.lucide.createIcons();
    }

    // Masquer la notification après la durée spécifiée
    if (duration) {
        setTimeout(() => hideNotification(notification), duration);
    }

    return notification;
}

function hideNotification(notification) {
    notification.classList.add('animate-fade-out');
    // notification.addEventListener('animationend', () => notification.remove(), { once: true });
}

document.addEventListener('DOMContentLoaded', () => {
    // Pour tester la notification qui correspond à l'image

    const notificationElement = document.getElementById('notification-data');
    if (notificationElement) {
        try {
            const notificationData = JSON.parse(notificationElement.getAttribute('data-notification'));
            if (notificationData) {
                showNotification(
                    notificationData.message,
                    notificationData.details || null,
                    notificationData.type || 'success'
                );
                // Supprimer la notification de la session après affichage
                fetch('clearNotification.php', { method: 'POST' })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur lors de la suppression de la notification');
                        }
                    })
                    .catch(error => {
                        console.error(error);
                    });
            }
        } catch (e) {
            console.error('Erreur lors du parsing des données de notification:', e);
        }
    }
});



// showNotification('Successfully saved!', 'Anyone with a link can now view this file.','success');