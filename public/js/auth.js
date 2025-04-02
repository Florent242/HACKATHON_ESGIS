document.addEventListener("DOMContentLoaded", function () {
    const tabLogin = document.getElementById("tab-login");
    const tabRegister = document.getElementById("tab-register");
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");
    const authCard = document.querySelector(".auth-card");

    // constante pour les message d'erreur pour la validation a l'inscription

    const form = document.getElementById('registrationForm');
    const signinForm = document.getElementById('signinForm');

    // Éléments du formulaire
    const fullName = document.getElementById('fullname');
    const username = document.getElementById('username');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');
    const school = document.getElementById('school');

    // Messages d'erreur
    const fullNameError = document.getElementById('fullNameError');
    const usernameError = document.getElementById('usernameError');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const confirmPasswordError = document.getElementById('confirmPasswordError');


    /**  Système de steps **/
    const stepDots = document.querySelectorAll('.step-dot');
    const prevBtn = document.querySelector('.prev-step-btn');
    const nextBtn = document.querySelector('.next-step-btn');
    const formSections = document.querySelectorAll('.form-section');
    const termsSection = document.querySelector('.form-group.mt-6');
    const submitBtn = document.querySelector('#signup');

    const schoolError = document.getElementById('schoolError');

    // Fonction pour valider le nom complet
    function validateFullName(value, errorElement) {
        if (value.length < 3 || value.trim() === '') {
            showError(fullName, errorElement, "Le nom complet doit contenir au moins 3 caractères");
            return false;
        } else if (!/^[a-zA-ZÀ-ÿ\s'-]+$/.test(value)) {
            showError(fullName, errorElement, "Le nom ne peut contenir que des lettres");
            return false;
        }
        hideError(fullName, errorElement);
        return true;
    }

    // Fonction pour valider le nom d'utilisateur
    function validateUsername(value, errorElement) {
        if (value.length < 3 || value.trim() === '') {
            showError(username, errorElement, "Le nom d'utilisateur doit contenir au moins 3 caractères");
            return false;
        } else if (!/^[a-zA-Z0-9_]+$/.test(value)) {
            showError(username, errorElement, "Le nom d'utilisateur ne peut contenir que des lettres, chiffres et underscores");
            return false;
        }
        hideError(username, errorElement);
        return true;
    }

    // Fonction pour valider l'email
    function validateEmail(value, errorElement) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value) || value.trim() === '') {
            showError(email, errorElement, "Veuillez entrer une adresse email valide");
            return false;
        }
        hideError(email, errorElement);
        return true;
    }

    // Fonction pour valider le champ d'école
    function validateSchool(value, errorElement) {
        if (!value.trim()) {
            showError(school, errorElement, 'Veuillez entrer le nom de votre école');
            return false;
        }
        hideError(school, errorElement);
        return true;
    }

    // Fonction pour valider le mot de passe
    function validatePassword(value, password, passwordError) {
        if (value.length < 8) {
            showError(password, passwordError, "Le mot de passe doit contenir au moins 8 caractères");
            return false;
        } else if (!/[A-Z]/.test(value)) {
            showError(password, passwordError, "Le mot de passe doit contenir au moins une majuscule");
            return false;
        } else if (!/[a-z]/.test(value)) {
            showError(password, passwordError, "Le mot de passe doit contenir au moins une minuscule");
            return false;
        } else if (!/[0-9]/.test(value)) {
            showError(password, passwordError, "Le mot de passe doit contenir au moins un chiffre");
            return false;
        } else if (!/[^A-Za-z0-9]/.test(value)) {
            showError(password, passwordError, "Le mot de passe doit contenir au moins un caractère spécial");
            return false;
        } else {
            hideError(password, passwordError);
            return true;
        }
    }

    // Fonction pour valider la confirmation du mot de passe
    function validateConfirmPassword(value, confirmPassword, confirmPasswordError, password) {
        if (value !== password.value || value.trim() === '') {
            showError(confirmPassword, confirmPasswordError, "Les mots de passe ne correspondent pas");
            return false;
        }
        hideError(confirmPassword, confirmPasswordError);
        return true;
    }



    // Helper function to calculate the total height needed
    function calculateTotalHeight(form) {
        // Get the computed styles of the auth card to account for padding, borders, etc.
        const cardStyle = window.getComputedStyle(authCard);
        const paddingTop = parseFloat(cardStyle.paddingTop);
        const paddingBottom = parseFloat(cardStyle.paddingBottom);
        const borderTop = parseFloat(cardStyle.borderTopWidth);
        const borderBottom = parseFloat(cardStyle.borderBottomWidth);

        // Calculate total height needed (form height + padding + borders)
        return form.offsetHeight + paddingTop + paddingBottom + borderTop + borderBottom;
    }

    // Setup a MutationObserver to watch for DOM changes inside forms
    const formObserver = new MutationObserver((mutations) => {
        // Check if the mutation affects the active form
        const activeForm = document.querySelector(".auth-form.active");
        if (activeForm) {
            // Calculate the new height and apply it
            const newHeight = calculateTotalHeight(activeForm);
            authCard.style.height = `${newHeight}px`;
        }
    });

    // Configuration for the observer
    const observerConfig = {
        childList: true,      // Watch for changes to the child elements
        subtree: true,        // Watch all descendants, not just direct children
        attributes: true,     // Watch for changes to attributes
        characterData: true   // Watch for changes to text content
    };

    // Start observing both forms
    formObserver.observe(loginForm, observerConfig);
    formObserver.observe(registerForm, observerConfig);

    function switchForms(showLogin) {
        // Obtain references to forms
        const currentForm = showLogin ? registerForm : loginForm;
        const nextForm = showLogin ? loginForm : registerForm;

        // Get current height for smooth transition
        const currentHeight = authCard.offsetHeight;
        authCard.style.height = `${currentHeight}px`;

        // Force reflow
        void authCard.offsetWidth;

        // Hide current form
        currentForm.classList.remove("active");

        // Show new form with a slight delay
        setTimeout(() => {
            nextForm.classList.add("active");

            // Calculate new height including all elements
            const newHeight = calculateTotalHeight(nextForm);

            // Apply the new height
            authCard.style.height = `${newHeight}px`;
        }, 50);
    }

    // Display login form by default and set initial height
    loginForm.classList.add("active");
    authCard.style.height = `${calculateTotalHeight(loginForm)}px`;

    // Initialize tabs
    tabLogin.addEventListener("click", function () {
        if (!tabLogin.classList.contains("active")) {
            tabLogin.classList.add("active");
            tabRegister.classList.remove("active");
            switchForms(true);
        }
    });

    tabRegister.addEventListener("click", function () {
        if (!tabRegister.classList.contains("active")) {
            tabRegister.classList.add("active");
            tabLogin.classList.remove("active");
            switchForms(false);
        }
    });

    // Also add a resize observer to handle window resize events
    const resizeObserver = new ResizeObserver((entries) => {
        const activeForm = document.querySelector(".auth-form.active");
        if (activeForm) {
            const newHeight = calculateTotalHeight(activeForm);
            authCard.style.height = `${newHeight}px`;
        }
    });

    // Observe the auth card for resize events
    resizeObserver.observe(authCard);

    let currentStep = 1;

    // Fonction pour mettre à jour les étapes
    function updateStep(step) {
        const bubbles = document.querySelectorAll('.bubble');
        const progressFill = document.getElementById('progressFill');

        // Mettre à jour les bulles
        bubbles.forEach((bubble, index) => {
            if (index + 1 === step) {
                bubble.classList.add('active');
                bubble.classList.remove('completed');
            } else if (index + 1 < step) {
                bubble.classList.remove('active');
                bubble.classList.add('completed');
            } else {
                bubble.classList.remove('active', 'completed');
            }
        });

        // Mettre à jour la barre de progression
        const progressPercent = ((step - 1) / (bubbles.length - 1)) * 100;
        progressFill.style.width = `${progressPercent}%`;

        // Reste du code inchangé...
        formSections.forEach((section, index) => {
            if (index + 1 === step) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });

        if (step === formSections.length) {
            nextBtn.style.display = 'none';
            termsSection.style.display = 'block';
            submitBtn.style.display = 'flex';
            prevBtn.style.display = 'flex';
        } else if (step === 1) {
            prevBtn.style.display = 'none';
            submitBtn.style.display = 'none';
            termsSection.style.display = 'none';
        } else {
            nextBtn.style.display = 'block';
            prevBtn.style.display = 'flex';
            nextBtn.textContent = 'Suivant';
            termsSection.style.display = 'none';
            submitBtn.style.display = 'none';
        }
        // Gérer le bouton précédent
        if (step === 1) {
            prevBtn.disabled = true;
        } else {
            prevBtn.disabled = false;
        }
        currentStep = step;

        const activeForm = document.querySelector('.auth-form.active');
        if (activeForm) {
            const newHeight = calculateTotalHeight(activeForm);
            authCard.style.height = `${newHeight}px`;
        }
    }

    // Écouteurs d'événements pour les boutons
    nextBtn.addEventListener('click', async function () {
        // Valider selon l'étape actuelle
        let canProceed = false;

        switch (currentStep) {
            case 1: // Étape 1: Informations personnelles
                canProceed = validateFullName(fullName.value, fullNameError) &&
                    validateUsername(username.value, usernameError) &&
                    validateEmail(email.value, emailError) &&
                    validateSchool(school.value, schoolError);
                break;

            case 2: // Étape 2: Sécurité
                canProceed = validatePassword(password.value, password, passwordError) &&
                    validateConfirmPassword(confirmPassword.value, confirmPassword, confirmPasswordError, password);
                break;

            case 3: // Étape 3: Hackathon
                // Valider la compétence principale
                if (main_skill.value === '') {
                    showError(main_skill, document.getElementById('mainSkillError'), "Sélectionnez votre compétence principale");
                    canProceed = false;
                    break;
                }

                // Valider le niveau d'étude
                if (education_level.value === '') {
                    showError(education_level, document.getElementById('educationLevelError'), "Sélectionnez votre niveau d'étude");
                    canProceed = false;
                    break;
                }

                canProceed = true;
                break;
        }

        // Passer à l'étape suivante si tout est valide
        if (canProceed && currentStep < formSections.length) {
            updateStep(currentStep + 1);
        }
    });

    prevBtn.addEventListener('click', () => {
        if (currentStep > 1) {
            updateStep(currentStep - 1);
        }
    });

    // Initialiser les étapes
    updateStep(1);

    // message d'erreur pour la validation a l'inscription

    // Validation du nom complet
    fullName.addEventListener('input', function () {
        if (this.value.length < 3 && this.value.trim() !== '') {
            showError(this, fullNameError, "Le nom complet doit contenir au moins 3 caractères");
        } else if (!/^[a-zA-ZÀ-ÿ\s'-]+$/.test(this.value) && this.value.trim() !== '') {
            showError(this, fullNameError, "Le nom ne peut contenir que des lettres");
        } else if (this.value.trim() === '') {
            hideError(this, fullNameError);
        } else {
            hideError(this, fullNameError);
        }
    });

    // Validation du nom d'utilisateur
    username.addEventListener('input', function () {
        if (username.value.length < 3 && username.value !== '') {
            showError(username, usernameError, "Le nom d'utilisateur doit contenir au moins 3 caractères");
        } else if (!/^[a-zA-Z0-9_]+$/.test(username.value)) {
            showError(username, usernameError, "Le nom d'utilisateur ne peut contenir que des lettres, chiffres et underscores");
        } else if (username.value.length >= 3) {
            checkUsername(username.value);
        } else {
            hideError(username, usernameError);
        }
    });

    // Validation de l'email
    email.addEventListener('input', function () {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(this.value) && this.value !== '') {
            showError(this, emailError, "Veuillez entrer une adresse email valide");
        } else if (emailRegex.test(this.value) && this.value !== '') {
            checkEmail(this.value);
        } else {
            hideError(this, emailError);
        }
    });

    // Validation du mot de passe
    password.addEventListener('input', function () {
        if (this.value.trim() === '') {
            hideError(this, passwordError);
            return;
        }
        validatePassword(this.value, this, passwordError);
    });

    // Validation de la confirmation du mot de passe
    confirmPassword.addEventListener('input', function () {
        if (this.value !== password.value && this.value.trim() !== '') {
            showError(this, confirmPasswordError, "Les mots de passe ne correspondent pas");
        } else {
            hideError(this, confirmPasswordError);
        }
    });

    // Validation du champ d'école
    school.addEventListener('input', function () {
        if (this.value.trim() === '') {
            showError(this, schoolError, 'Veuillez entrer le nom de votre école');
        } else {
            hideError(this, schoolError);
        }
    });

    // Validation du formulaire à la soumission
    form.addEventListener('submit', async function (event) {
        event.preventDefault(); // Toujours preventDefault au début

        let isValid = true;

        // Désactiver le bouton de soumission et afficher l'indicateur de traitement
        const submitBtn = event.target.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i data-lucide="loader-circle" class="animate-spin"></i> Traitement...';

        try {
            // Validation du nom complet
            if (fullName.value.length < 3 || fullName.value.trim() === '') {
                showError(fullName, fullNameError, "Le nom complet doit contenir au moins 3 caractères");
                isValid = false;
            }

            // Validation du nom d'utilisateur
            if (username.value.length < 3 || username.value.trim() === '') {
                showError(username, usernameError, "Le nom d'utilisateur doit contenir au moins 3 caractères");
                isValid = false;
            }

            // Validation de l'email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value) || email.value.trim() === '') {
                showError(email, emailError, "Veuillez entrer une adresse email valide");
                isValid = false;
            }

            // Validation du champ d'école
            if (school.value.trim() === '') {
                showError(school, schoolError, 'Veuillez entrer le nom de votre école');
                isValid = false;
            }

            // Validation du mot de passe
            if (!validatePassword(password.value, password, passwordError) || password.value.trim() === '') {
                showError(password, passwordError, "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial");
                isValid = false;
            }

            // Validation de la confirmation du mot de passe
            if (confirmPassword.value !== password.value || confirmPassword.value.trim() === '') {
                showError(confirmPassword, confirmPasswordError, "Les mots de passe ne correspondent pas");
                isValid = false;
            }

            // Validation synchrone
            if (!isValid) { return; }

            // Vérifications asynchrones
            const [usernameAvailable, emailAvailable] = await Promise.all([
                checkUsername(username.value),
                checkEmail(email.value)
            ]);

            if (usernameAvailable || emailAvailable) {
                showNotification(usernameAvailable ? "Email déjà utilisé" : "Nom d'utilisateur déjà pris", 'Veuillez corriger les erreurs', 'warning');
                return;
            }

            // Envoi des données via Fetch
            const formData = new FormData(event.target);
            const response = await fetch(event.target.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Stockage du token côté client
                // if (data.token) {
                //     localStorage.setItem('auth_token', data.token);
                // }

                // Redirection
                window.location.href = data.redirect || '/user';
            } else {
                showNotification(data.message || "Erreur lors de l'inscription", 'Veuillez corriger les erreurs', 'warning');
            }
        } catch (error) {
            console.error('Validation error:', error.message);
            showNotification('Une erreur est survenue', error.message, 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'S\'inscrire';
        }
    });

    // Gestionnaire de formulaire de connexion
    signinForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i data-lucide="circle-loader" class="animate-spin"></i> Traitement...';

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Erreur de connexion');
            }

            // Stockage des tokens
            if (data.success) {
                setFlashMessage('success', data.message, data.details);
                window.location.href = data.redirect || '/user';
            } else if (!data.success) {
                showNotification(data.message || "Erreur lors de la connexion", 'Veuillez corriger les erreurs', 'warning');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Se connecter';
                return;
            }
        } catch (error) {
            showNotification(error.message, 'Veuillez corriger les erreurs', 'warning');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Se connecter';
        }
    });

    // function pour rechercher un nom d'utilisateur
    async function checkUsername(Username) {
        fetch('/HACKATHON_ESGIS/public/api/auth/check-username', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username: Username })
        })
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    showError(username, usernameError, "Cet nom d'utilisateur est déjà utilisé");
                    return false;
                } else {
                    hideError(username, usernameError);
                    return true;
                }
            })
            .catch(error => {
                console.error('Error checking username:', error);
            });
    }

    // function pour rechercher un email
    async function checkEmail(Email) {
        fetch('/HACKATHON_ESGIS/public/api/auth/check-email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email: Email })
        })
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    showError(email, emailError, "Cet email est déjà utilisé");
                    return false;
                } else {
                    hideError(email, emailError);
                    return true;
                }
            })
            .catch(error => {
                console.error('Error checking email:', error);
            });
    }

    // Fonctions d'affichage/masquage des erreurs
    function showError(inputElement, errorElement, message) {
        // Ajouter la classe d'erreur à l'input
        inputElement.parentElement.classList.add('input-error');

        // Afficher et animer le message d'erreur
        errorElement.textContent = message;
        errorElement.classList.remove('hidden', 'fade-out');
    }

    function hideError(inputElement, errorElement) {
        // Retirer la classe d'erreur de l'input
        inputElement.parentElement.classList.remove('input-error');

        // Vérifier si l'erreur est déjà masquée
        if (errorElement.classList.contains('hidden')) return;

        // Supprimer l'ancienne animation si elle est encore en cours
        errorElement.classList.remove('fade-in');

        // Ajouter la classe de disparition
        errorElement.classList.add('fade-out');

        // Attendre la fin de l'animation avant de cacher complètement
        errorElement.addEventListener('animationend', function () {
            errorElement.classList.add('hidden');
            errorElement.classList.remove('fade-out'); // Nettoyage après animation
        }, { once: true });
    }
});