document.addEventListener('DOMContentLoaded', () => {
    // Pour tester la notification qui correspond à l'image

    const notificationElement = document.getElementById('notification-data');
    if (notificationElement) {
        try {
            const notificationData = JSON.parse(notificationElement.getAttribute('data-notification'));
            console.log(notificationData);
            if (notificationData) {
                showNotification(
                    notificationData.message,
                    notificationData.details || null,
                    notificationData.type || 'info'
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
            const flashMessage = getFlashMessage();
            console.log(flashMessage);
            if (flashMessage) {
                showNotification(
                    flashMessage.message,
                    flashMessage.details || null,
                    flashMessage.type || 'info'
                );
                // Supprimer le message après l'avoir affiché
                localStorage.removeItem('flashMessage');
            }
        } catch (e) {
            console.error('Erreur lors du parsing des données de notification:', e);
        }
    }
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
    notification.className = `fixed top-4 ${type === 'success' ? 'left-1/2' : 'right-4'} transform ${type === 'success' ? '-translate-x-1/2' : 'translate-x-0'} max-w-md w-auto bg-gray-900/90 backdrop-blur-sm border ${type === 'success' ? 'border-green-500/30' : type === 'error' ? 'border-red-500/30' : type === 'warning' ? 'border-yellow-500/30' : 'border-blue-500/30'} rounded-lg shadow-lg shadow-black/30 p-3 flex items-start justify-between gap-3 animate-fade-in z-50`;

    // Conteneur d'icône
    const iconContainer = document.createElement('div');
    iconContainer.className = 'flex-shrink-0 pt-0.5';

    // Icône Lucide
    const icon = document.createElement('i');
    icon.setAttribute('data-lucide',
        type === 'success' ? 'check-circle' :
            type === 'error' ? 'x-circle' :
                type === 'warning' ? 'alert-triangle' :
                    'info'
    );
    icon.className = `w-5 h-5 ${type === 'success' ? 'text-green-400' :
        type === 'error' ? 'text-red-400' :
            type === 'warning' ? 'text-yellow-400' :
                'text-blue-400'
        }`;

    iconContainer.appendChild(icon);
    notification.appendChild(iconContainer);

    // Contenu du texte
    const textContainer = document.createElement('div');
    textContainer.className = 'flex-1';

    // Message principal
    const messageElement = document.createElement('p');
    messageElement.className = 'text-white font-medium text-sm';
    messageElement.innerText = message;
    textContainer.appendChild(messageElement);

    // Message de détails (en option)
    if (details) {
        const detailsElement = document.createElement('p');
        detailsElement.className = 'text-gray-300/90 font-normal text-xs mt-1';
        detailsElement.innerText = details;
        textContainer.appendChild(detailsElement);
    }

    notification.appendChild(textContainer);

    // Bouton de fermeture
    const closeContainer = document.createElement('div');
    closeContainer.className = 'flex-shrink-0 pt-0.5';

    const closeButton = document.createElement('button');
    closeButton.className = 'text-gray-400 hover:text-white transition-colors focus:outline-none';

    const closeIcon = document.createElement('i');
    closeIcon.setAttribute('data-lucide', 'x');
    closeIcon.className = 'w-4 h-4';

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

// Dans un fichier utils.js ou directement dans auth.js
function setFlashMessage(type, message, details = null) {
    // Stocker le message dans localStorage
    localStorage.setItem('flashMessage', JSON.stringify({
        type: type,
        message: message,
        details: details,
        timestamp: Date.now()
    }));
}

function getFlashMessage() {
    const message = localStorage.getItem('flashMessage');
    if (message) {
        const flash = JSON.parse(message);
        return flash;
    }
    return null;
}

