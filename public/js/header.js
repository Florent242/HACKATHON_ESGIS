// lucide initiating
lucide.createIcons();

// Start Challenge button click handler
if (document.querySelector('.start-challenge')) {
    document.querySelector('.start-challenge').addEventListener('click', () => {
        window.location.href = '/HACKATHON_ESGIS/public/auth';
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
    threshold: 0.25 // Déclenche l'événement lorsque 10% de l'élément est visible
});

if (fadeElements) {
    fadeElements.forEach(element => {
        heroObserver.observe(element);
    });
}

// Add smooth scroll behavior. La partie a[href^="#"] est un sélecteur CSS qui cible tous les éléments <a> (liens) dont l'attribut href commence par le caractère #
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
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