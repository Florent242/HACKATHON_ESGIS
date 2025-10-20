/**
 * Amélioration de l'accessibilité pour la navigation au clavier
 * Ce script ajoute des classes pour gérer les états de focus visibles
 * uniquement lors de la navigation au clavier
 */

// Initialisation de la gestion du focus visible
function initFocusVisible() {
    // Vérifier si le navigateur supporte :focus-visible
    try {
        document.body.classList.add('js-focus-visible');
        
        // Détecter la navigation au clavier et ajouter/supprimer la classe en conséquence
        let hadKeyboardEvent = false;
        const keyboardModalityWhitelist = [
            'text',
            'search',
            'url',
            'tel',
            'email',
            'password',
            'number',
            'date',
            'month',
            'week',
            'time',
            'datetime',
            'datetime-local'
        ];

        const isKeyboardModality = (event) => {
            const isTextInput = event.target.tagName.toLowerCase() === 'input' && 
                keyboardModalityWhitelist.includes(event.target.type);
            
            return event.key === 'Tab' || event.key === 'Tab' && isTextInput;
        };

        document.addEventListener('keydown', (e) => {
            if (isKeyboardModality(e)) {
                hadKeyboardEvent = true;
            }
        }, true);

        document.addEventListener('mousedown', () => {
            hadKeyboardEvent = false;
        }, true);

        document.addEventListener('focusin', (e) => {
            if (hadKeyboardEvent) {
                document.documentElement.classList.add('keyboard-navigation');
                e.target.classList.add('focus-visible');
            }
        }, true);

        document.addEventListener('focusout', (e) => {
            e.target.classList.remove('focus-visible');
        }, true);

        // Gestion des clics pour réinitialiser l'état
        document.addEventListener('click', () => {
            hadKeyboardEvent = false;
        }, true);

        console.log('Focus visible initialisé');
    } catch (error) {
        console.error('Erreur lors de l\'initialisation du focus visible:', error);
    }
}

// Initialiser au chargement du DOM
document.addEventListener('DOMContentLoaded', () => {
    initFocusVisible();
    
    // Ajouter un attribut aria-hidden aux icônes décoratives
    document.querySelectorAll('i[data-lucide]').forEach(icon => {
        if (!icon.hasAttribute('aria-hidden') && !icon.closest('button, a, [role="button"]')) {
            icon.setAttribute('aria-hidden', 'true');
        }
    });
    
    // Amélioration de l'accessibilité des messages d'erreur
    const errorMessages = document.querySelectorAll('.error-message');
    errorMessages.forEach(message => {
        if (message.textContent.trim() !== '') {
            message.setAttribute('role', 'alert');
            message.setAttribute('aria-live', 'assertive');
        }
    });
});

// Exporter pour une utilisation dans d'autres fichiers si nécessaire
export { initFocusVisible };
