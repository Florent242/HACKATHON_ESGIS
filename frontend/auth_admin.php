<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="theme-color" content="#3b82f6">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Esgis Admin</title>
    <link rel="stylesheet" href="/css/styles/auth_admin.css">
    <link rel="stylesheet" href="/css/styles/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script defer type="module" src="/js/accessibility.js"></script>
    <script defer type="module" src="/js/auth_admin.js"></script>

</head>

<body class="js-focus-visible">
    <div id="notification-data" data-notification='<?= htmlspecialchars(json_encode($_SESSION['notification'] ?? null)) ?>'></div>

    <div class="ad-card w-full max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl sm:shadow-lg" role="region" aria-labelledby="admin-login-title">
        <div class="p-6 sm:p-8 md:p-10" id="loginForm">
            <div class="text-center mb-6 sm:mb-8">
                <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-3 sm:mb-4 rounded-full bg-blue-50 flex items-center justify-center transition-transform duration-300 hover:scale-105">
                    <i data-lucide="shield" class="w-8 h-8 sm:w-10 sm:h-10 text-blue-600"></i>
                </div>
                <h1 id="admin-login-title" class="text-xl sm:text-2xl font-bold text-gray-900">Espace Administrateur</h1>
                <p class="text-gray-600 mt-1 sm:mt-2 text-sm sm:text-base">Connectez-vous pour accéder au tableau de bord d'administration</p>
            </div>
            <form action="/api/auth/login" method="POST" id="signinForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? null; ?>">

                <!-- Champ Email/Identifiant -->
                <div class="form-group mb-4 sm:mb-6">
                    <label for="email_user" class="block text-sm sm:text-base font-medium text-gray-700 mb-1.5 sm:mb-2 transition-colors duration-200">
                        Email ou nom d'utilisateur
                    </label>
                    <div class="relative rounded-lg shadow-sm group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="mail" class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400 transition-colors duration-200" aria-hidden="true"></i>
                        </div>
                        <input
                            type="text"
                            id="email_user"
                            name="identifier"
                            class="block w-full pl-9 sm:pl-10 pr-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 ease-in-out text-sm sm:text-base appearance-none"
                            placeholder="username ou etudiant@esgis.bj"
                            required
                            autocomplete="username"
                            inputmode="email"
                            aria-describedby="email_help">
                    </div>
                    <p id="email_help" class="error-message mt-1.5 text-xs sm:text-sm"></p>
                </div>

                <!-- Champ Mot de passe -->
                <div class="form-group mb-4 sm:mb-6">
                    <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                        <label for="password_user" class="block text-sm sm:text-base font-medium text-gray-700 transition-colors duration-200">
                            Mot de passe
                        </label>
                        <a href="#" class="text-xs sm:text-sm text-blue-600 hover:text-blue-700 transition-colors duration-200 font-medium">
                            Mot de passe oublié ?
                        </a>
                    </div>
                    <div class="relative rounded-lg shadow-sm group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="key" class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400 transition-colors duration-200" aria-hidden="true"></i>
                        </div>
                        <input
                            type="password"
                            id="password_user"
                            name="password"
                            class="block w-full pl-9 sm:pl-10 pr-10 py-2.5 sm:py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 ease-in-out text-sm sm:text-base appearance-none"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                            aria-describedby="password_help">
                        <button
                            type="button"
                            id="togglePasswordBtn"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-blue-500 focus:outline-none transition-colors duration-200"
                            aria-label="Afficher le mot de passe"
                            title="Afficher le mot de passe"
                            style="-webkit-tap-highlight-color: transparent; touch-action: manipulation;">
                            <i data-lucide="eye" class="h-4 w-4 sm:h-5 sm:w-5"></i>
                        </button>
                    </div>
                    <p id="password_help" class="error-message mt-1.5 text-xs sm:text-sm"></p>
                </div>

                <!-- Case à cocher Rester connecté -->
                <div class="flex items-center justify-between mt-6 mb-2">
                    <div class="flex items-center">
                        <input
                            type="checkbox"
                            id="remember_me"
                            name="remember_me"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition duration-200 ease-in-out">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-700 cursor-pointer hover:text-gray-800 transition-colors duration-200">
                            Rester connecté
                        </label>
                    </div>
                </div>

                <!-- Lien d'inscription -->
                <div class="text-center mt-6 sm:mt-8">
                    <p class="text-xs sm:text-sm text-gray-600">
                        Vous n'avez pas de compte ?
                        <a href="#" class="font-medium text-blue-600 hover:text-blue-700 transition-colors duration-200 relative group inline-flex items-center">
                            <span>Demander un accès</span>
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-blue-600 group-hover:w-full transition-all duration-200 ease-in-out"></span>
                        </a>
                    </p>
                </div>

                <!-- Bouton de soumission -->
                <div class="mt-6 sm:mt-8">
                    <button
                        type="submit"
                        class="submit-btn w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm sm:text-base font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out active:scale-95 active:opacity-90"
                        style="-webkit-tap-highlight-color: transparent; touch-action: manipulation; min-height: 48px;">
                        <i data-lucide="log-in" class="w-4 h-4 sm:w-5 sm:h-5 mr-2 flex-shrink-0"></i>
                        <span>Se connecter</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialisation des icônes Lucide
            let __lucideInitDone = false;

            function ensureLucideIcons() {
                try {
                    if (window.lucide && typeof lucide.createIcons === 'function') {
                        lucide.createIcons();
                        __lucideInitDone = true;
                    }
                } catch (_) {}
            }
            // Try on DOM ready
            ensureLucideIcons();
            // Try again on window load (in case lucide is loaded after auth.js)
            window.addEventListener('load', ensureLucideIcons, {
                once: true
            });
            // Poll briefly if still not initialized
            if (!__lucideInitDone) {
                let tries = 0;
                const maxTries = 40; // ~2s at 50ms
                const iv = setInterval(() => {
                    tries++;
                    ensureLucideIcons();
                    if (__lucideInitDone || tries >= maxTries) clearInterval(iv);
                }, 50);
            }

            // Gestion de l'affichage du mot de passe avec animation

            // Disable native HTML5 validation on auth forms to avoid UA "not focusable" edge cases
            if (loginForm) loginForm.setAttribute('novalidate', 'novalidate');
            if (registerForm) registerForm.setAttribute('novalidate', 'novalidate');

            const togglePasswordBtn = document.getElementById('togglePasswordBtn');
            const passwordInput = document.getElementById('password_user');

            if (togglePasswordBtn && passwordInput) {
                let eyeIcon = togglePasswordBtn.querySelector('[data-lucide="eye"]');
                let eyeOffIcon = togglePasswordBtn.querySelector('[data-lucide="eye-off"]');
                let isPasswordVisible = false;

                // Fonction pour basculer la visibilité du mot de passe
                const togglePasswordVisibility = () => {
                    isPasswordVisible = !isPasswordVisible;

                    // Bascule entre le type de champ avec une transition fluide
                    passwordInput.type = isPasswordVisible ? 'text' : 'password';

                    // Mise à jour des attributs d'accessibilité
                    const action = isPasswordVisible ? 'Masquer' : 'Afficher';
                    togglePasswordBtn.setAttribute('aria-label', `${action} le mot de passe`);
                    togglePasswordBtn.setAttribute('title', `${action} le mot de passe`);
                    togglePasswordBtn.setAttribute('aria-pressed', isPasswordVisible);

                    // Animation de l'icône
                    if (eyeIcon) {
                        eyeIcon.style.transform = 'scale(0.8)';

                        // Changement d'icône avec animation
                        setTimeout(() => {
                            togglePasswordBtn.classList.innerHTML = `<i data-lucide="${isPasswordVisible ? 'eye-off' : 'eye'}" class="h-4 w-4 sm:h-5 sm:w-5"></i>`;
                            if (window.lucide) {
                                lucide.createIcons();
                                // Mettre à jour la référence de l'icône après la recréation
                                eyeIcon = togglePasswordBtn.querySelector('[data-lucide="eye"]');
                            }

                            // Animation de retour
                            setTimeout(() => {
                                eyeIcon.style.transform = 'scale(1.1)';
                                setTimeout(() => {
                                    eyeIcon.style.transform = 'scale(1)';
                                }, 100);
                            }, 50);
                        }, 100);
                    }
                };

                // Gestionnaire d'événements pour le clic
                togglePasswordBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    togglePasswordVisibility();
                });

                // Gestionnaire pour la touche Entrée/Espace pour l'accessibilité
                togglePasswordBtn.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        togglePasswordVisibility();
                    }
                });

                // Effet de focus personnalisé
                const inputContainer = passwordInput.closest('.relative');

                passwordInput.addEventListener('focus', function() {
                    inputContainer.classList.add('ring-2', 'ring-blue-500', 'ring-offset-1');
                });

                passwordInput.addEventListener('blur', function() {
                    inputContainer.classList.remove('ring-2', 'ring-blue-500', 'ring-offset-1');
                });
            }

            // Gestion de la soumission du formulaire avec animation
            const loginForm = document.getElementById('signinForm');
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const submitIcon = submitBtn?.querySelector('i');

                    // Vérification de la validité du formulaire
                    if (!this.checkValidity()) {
                        e.preventDefault();

                        // Animation de secousse pour les champs invalides
                        const invalidFields = this.querySelectorAll(':invalid');
                        invalidFields.forEach(field => {
                            field.style.animation = 'none';
                            void field.offsetWidth; // Déclenche un reflow
                            field.style.animation = 'shake 0.4s cubic-bezier(.36,.07,.19,.97) both';

                            // Afficher le message d'erreur
                            const errorId = field.getAttribute('aria-describedby');
                            if (errorId) {
                                const errorElement = document.getElementById(errorId);
                                if (errorElement) {
                                    errorElement.style.display = 'block';
                                    errorElement.textContent = field.validationMessage || 'Ce champ est requis';
                                }
                            }
                        });

                        return;
                    }

                    // Animation de chargement
                    if (submitBtn && submitIcon) {
                        e.preventDefault();

                        // Sauvegarder le contenu original
                        const originalContent = submitBtn.innerHTML;
                        const originalWidth = submitBtn.offsetWidth;

                        // Appliquer le style de chargement
                        submitBtn.style.width = `${originalWidth}px`;
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-90', 'cursor-not-allowed');
                        submitIcon.setAttribute('data-lucide', 'loader-2');
                        submitIcon.classList.add('animate-spin');

                        if (window.lucide) lucide.createIcons();

                        // Simuler une requête asynchrone (à remplacer par votre logique de connexion réelle)
                        setTimeout(() => {
                            // Animation de succès
                            submitIcon.setAttribute('data-lucide', 'check');
                            submitIcon.classList.remove('animate-spin');
                            if (window.lucide) lucide.createIcons();

                            // Soumettre le formulaire après l'animation
                            setTimeout(() => {
                                loginForm.submit();
                            }, 500);

                        }, 1500); // Durée de la simulation de chargement
                    }
                });

                // Gestion de la validation en temps réel
                const inputs = loginForm.querySelectorAll('input[required]');
                inputs.forEach(input => {
                    input.addEventListener('input', function() {
                        const errorId = this.getAttribute('aria-describedby');
                        if (!errorId) return;

                        const errorElement = document.getElementById(errorId);
                        if (!errorElement) return;

                        if (this.validity.valid) {
                            errorElement.style.display = 'none';
                            this.classList.remove('border-red-500');
                        } else {
                            this.classList.add('border-red-500');
                        }
                    });
                });
            }

            // Animation d'entrée progressive des éléments
            const animateOnScroll = () => {
                const elements = document.querySelectorAll('.animate-on-scroll');
                elements.forEach(element => {
                    const elementTop = element.getBoundingClientRect().top;
                    const windowHeight = window.innerHeight;

                    if (elementTop < windowHeight - 50) {
                        element.classList.add('opacity-100', 'translate-y-0');
                        element.classList.remove('opacity-0', 'translate-y-4');
                    }
                });
            };

            // Déclencher l'animation au chargement
            window.addEventListener('load', () => {
                animateOnScroll();

                // Animation du logo
                const logo = document.querySelector('.w-20');
                if (logo) {
                    setTimeout(() => {
                        logo.style.transform = 'scale(1.1)';
                        setTimeout(() => {
                            logo.style.transform = 'scale(1)';
                        }, 200);
                    }, 300);
                }
            });

            // Déclencher l'animation au défilement
            window.addEventListener('scroll', animateOnScroll);
        });
    </script>

</body>

</html>