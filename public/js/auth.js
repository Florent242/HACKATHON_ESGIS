document.addEventListener("DOMContentLoaded", function () {
    const tabLogin = document.getElementById("tab-login");
    const tabRegister = document.getElementById("tab-register");
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");

    // Afficher le formulaire de connexion par défaut
    loginForm.style.display = "block";

    tabLogin.addEventListener("click", function () {
        tabLogin.classList.add("active");
        tabRegister.classList.remove("active");
        loginForm.style.display = "block";
        registerForm.style.display = "none";
    });

    tabRegister.addEventListener("click", function () {
        tabRegister.classList.add("active");
        tabLogin.classList.remove("active");
        registerForm.style.display = "block";
        loginForm.style.display = "none";
    });

    // message d'erreur pour la validation a l'inscription
    const form = document.getElementById('registrationForm');

    // Éléments du formulaire
    const fullName = document.getElementById('fullName');
    const username = document.getElementById('username');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');

    // Messages d'erreur
    const fullNameError = document.getElementById('fullNameError');
    const usernameError = document.getElementById('usernameError');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const confirmPasswordError = document.getElementById('confirmPasswordError');
    
    // Validation du nom complet
    fullName.addEventListener('input', function () {
        if (this.value.length < 3 && this.value.trim() !== '') {
            showError(this, fullNameError, "Le nom complet doit contenir au moins 3 caractères");
        } else if (!/^[a-zA-ZÀ-ÿ\s'-]+$/.test(this.value) && this.value.trim() !== '') {
            showError(this, fullNameError, "Le nom ne peut contenir que des lettres");
        } else if (this.value.trim() === '') {
            hideError(this, fullNameError);
        }else{
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

    // Validation du formulaire à la soumission
    form.addEventListener('submit', async function (event) {
        event.preventDefault(); // Toujours preventDefault au début

        let isValid = true;

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

        // Validation asynchrone
        if (isValid) {
            try {
                const [usernameAvailable, emailAvailable] = await Promise.all([
                    checkUsername(username.value),
                    checkEmail(email.value)
                ]);

                if (!usernameAvailable && !emailAvailable) {
                    form.submit(); // Soumettre seulement si tout est valide
                }
            } catch (error) {
                console.error('Validation error:', error);
            }
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

// Validation du mot de passe
function validatePassword(value,password, passwordError) {
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
});