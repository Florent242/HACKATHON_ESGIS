document.addEventListener("DOMContentLoaded", function () {
    const tabLogin = document.getElementById("tab-login");
    const tabRegister = document.getElementById("tab-register");
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const authCard = document.querySelector(".auth-card");

    // Initialize Lucide icons once DOM is ready, with robust fallbacks
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
    window.addEventListener('load', ensureLucideIcons, { once: true });
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

    // Disable native HTML5 validation on auth forms to avoid UA "not focusable" edge cases
    if (loginForm) loginForm.setAttribute('novalidate', 'novalidate');
    if (registerForm) registerForm.setAttribute('novalidate', 'novalidate');

    // Toggle password visibility for all buttons with .toggle-password
    function initPasswordToggles(root) {
        const scope = root || document;
        const btns = scope.querySelectorAll('.toggle-password');
        btns.forEach((btn) => {
            if (btn.__hasToggleHandler) return;
            btn.__hasToggleHandler = true;
            btn.addEventListener('click', () => {
                const wrapper = btn.closest('.display') || btn.parentElement;
                if (!wrapper) return;
                let input = wrapper.querySelector('input[type="password"], input[type="text"]');
                if (!input) return;
                const isText = input.type === 'text';
                input.type = isText ? 'password' : 'text';
                btn.setAttribute('aria-label', isText ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
                btn.title = isText ? 'Afficher le mot de passe' : 'Masquer le mot de passe';
                btn.innerHTML = isText ? '<i data-lucide="eye"></i>' : '<i data-lucide="eye-off"></i>';
                try { if (window.lucide) lucide.createIcons(); } catch (_) {}
            });
        });
    }
    initPasswordToggles(document);

    // Email sanitization + validation (prevents scripts/invalid chars)
    const emailInputs = document.querySelectorAll('input[type="email"][name="email"]');
    const EMAIL_REGEX = /^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/;
    function sanitizeEmailValue(v) {
        if (!v) return '';
        // Remove tags, spaces and any disallowed char
        return String(v)
            .replace(/<[^>]*>/g, '')         // strip tags
            .replace(/[\s]/g, '')           // strip whitespace
            .replace(/[^A-Za-z0-9.@_%+\-]/g, ''); // keep only allowed
    }
    emailInputs.forEach((el) => {
        el.addEventListener('input', () => {
            const clean = sanitizeEmailValue(el.value);
            if (clean !== el.value) el.value = clean;
            el.setCustomValidity('');
        });
        el.addEventListener('blur', () => {
            const clean = sanitizeEmailValue(el.value);
            el.value = clean;
            if (clean && !EMAIL_REGEX.test(clean)) {
                el.setCustomValidity('Veuillez saisir une adresse e-mail valide.');
            } else {
                el.setCustomValidity('');
            }
            el.reportValidity();
        });
    });

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
    const phoneInput = document.getElementById('phone');
    const phoneCountry = document.getElementById('phone_country');

    // Messages d'erreur
    const fullNameError = document.getElementById('fullNameError');
    const usernameError = document.getElementById('usernameError');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const confirmPasswordError = document.getElementById('confirmPasswordError');
    const schoolError = document.getElementById('schoolError');
    const phoneError = document.getElementById('phoneError');

    /**  Système de steps **/
    const stepDots = document.querySelectorAll('.step-dot');
    const prevBtn = document.querySelector('.prev-step-btn');
    const nextBtn = document.querySelector('.next-step-btn');
    const formSections = document.querySelectorAll('.form-section');
    // const termsSection = document.querySelector('.form-group.mt-6');
    const submitBtn = document.querySelector('#signup');

    // Fonction pour valider le nom complet
    /**
     * @description Fonction pour valider le nom complet
     * @param {string} value 
     * @param {HTMLElement} errorElement 
     * @returns {boolean}
     */
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
    /**
     * @description Fonction pour valider le nom d'utilisateur
     * @param {string} value 
     * @param {HTMLElement} errorElement 
     * @returns {boolean}
     */
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
    /**
     * @description Fonction pour valider l'email
     * @param {string} value 
     * @param {HTMLElement} errorElement 
     * @returns {boolean}
     */
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
    /**
     * @description Fonction pour valider le champ d'école
     * @param {string} value 
     * @param {HTMLElement} errorElement 
     * @returns {boolean}
     */
    function validateSchool(value, errorElement) {
        if (!value.trim()) {
            showError(school, errorElement, 'Veuillez entrer le nom de votre école');
            return false;
        }
        hideError(school, errorElement);
        return true;
    }

    // Fonction pour valider le mot de passe
    /**
     * @description Fonction pour valider le mot de passe
     * @param {string} value 
     * @param {HTMLElement} password 
     * @param {HTMLElement} passwordError 
     * @returns {boolean}
     */
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
    /**
     * @description Fonction pour valider la confirmation du mot de passe
     * @param {string} value 
     * @param {HTMLElement} confirmPassword 
     * @param {HTMLElement} confirmPasswordError 
     * @param {HTMLElement} password 
     * @returns {boolean}
     */
    function validateConfirmPassword(value, confirmPassword, confirmPasswordError, password) {
        if (value !== password.value || value.trim() === '') {
            showError(confirmPassword, confirmPasswordError, "Les mots de passe ne correspondent pas");
            return false;
        }
        hideError(confirmPassword, confirmPasswordError);
        return true;
    }

    /**
     * @description Fonction pour valider le numéro de téléphone (multi-pays)
     * - Utilise intl-tel-input si disponible, sinon regex de secours
     * @param {string} value 
     * @param {HTMLElement} phoneInput 
     * @param {HTMLElement} phoneError 
     * @returns {boolean}
     */
    function validatePhone(value, phoneInput, phoneError) {
        const v = (value || '').trim();
        // 1) Si plugin intl-tel-input initialisé
        if (phoneInput && phoneInput.__iti) {
            const iti = phoneInput.__iti;
            if (!v) {
                showError(phoneInput, phoneError, 'Veuillez saisir votre numéro');
                return false;
            }
            if (!iti.isValidNumber()) {
                showError(phoneInput, phoneError, 'Veuillez saisir un numéro valide');
                return false;
            }
            hideError(phoneInput, phoneError);
            return true;
        }
        // 2) Fallback minimal: vérifier format international simple
        const ok = /^[+0-9 ()\-]*$/.test(v);
        if (!ok) {
            showError(phoneInput, phoneError, 'Numéro invalide. Utilisez un format international (+... ...)');
            return false;
        }
        hideError(phoneInput, phoneError);
        return true;
    }

    (async function initSchoolSelect() {
        const schoolSelect = document.getElementById('school');
        const schoolError = document.getElementById('schoolError');
        if (!schoolSelect) return;

        function populateSchools(list) {
            for (let i = schoolSelect.options.length - 1; i >= 1; i--) {
                schoolSelect.remove(i);
            }
            (list || []).forEach(name => {
                if (!name || typeof name !== 'string') return;
                const opt = document.createElement('option');
                opt.value = name;
                opt.textContent = name;
                schoolSelect.appendChild(opt);
            });
        }

        try {
            async function loadSchoolsScript() {
                if (window.SCHOOLS) return;
                if (document.querySelector('script[data-schools="true"]')) return new Promise((res)=>{
                    if (window.SCHOOLS) return res();
                    document.addEventListener('schools:loaded', () => res(), { once: true });
                });

                await new Promise((resolve, reject) => {
                    const s = document.createElement('script');
                    s.src = '/assets/schools.js';
                    s.async = true;
                    s.defer = true;
                    s.dataset.schools = 'true';
                    s.onload = () => {
                        document.dispatchEvent(new CustomEvent('schools:loaded'));
                        resolve();
                    };
                    s.onerror = () => reject(new Error('Impossible de charger /assets/schools.js'));
                    document.head.appendChild(s);
                });
            }

            await loadSchoolsScript();

            const raw = window.SCHOOLS;
            const schools = Array.isArray(raw) ? raw : (Array.isArray(raw?.schools) ? raw.schools : []);
            if (!schools.length) throw new Error('Aucune école dans window.SCHOOLS');

            schools.sort((a, b) => ('' + a).localeCompare(b));
            populateSchools(schools);
        } catch (e) {
            populateSchools([
                'Autre / Non listée'
            ]);
            console.warn('Impossible de charger /assets/schools.js:', e);
        }

        function validateSchool() {
            if (!schoolSelect.value) {
                schoolError && (schoolError.textContent = 'Veuillez sélectionner votre école');
                schoolError && schoolError.classList.remove('hidden');
                return false;
            }
            schoolError && schoolError.classList.add('hidden');
            return true;
        }

        schoolSelect.addEventListener('change', validateSchool);

        const signupBtn = document.querySelector('#signup');
        if (signupBtn) {
            signupBtn.addEventListener('click', (e) => {
                if (!validateSchool()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        }
    })();

    (function initPhoneValidation() {
        if (!phoneInput) return;

        // Try to use intl-tel-input for full country coverage
        const CDN = {
            css: 'https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.6/build/css/intlTelInput.css',
            js: 'https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.6/build/js/intlTelInput.min.js',
            utils: 'https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.6/build/js/utils.js',
        };

        function loadCSSOnce(href) {
            if ([...document.styleSheets].some(s => s.href && s.href.includes('intl-tel-input'))) return Promise.resolve();
            return new Promise((resolve, reject) => {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = href;
                link.onload = () => resolve();
                link.onerror = () => resolve(); // don't block if CSS fails
                document.head.appendChild(link);
            });
        }
        function loadScriptOnce(src, attrKey) {
            if (window.intlTelInput) return Promise.resolve();
            if (document.querySelector(`script[data-${attrKey}="true"]`)) {
                return new Promise(res => {
                    if (window.intlTelInput) return res();
                    const check = setInterval(() => {
                        if (window.intlTelInput) { clearInterval(check); res(); }
                    }, 50);
                });
            }
            return new Promise((resolve, reject) => {
                const s = document.createElement('script');
                s.src = src;
                s.async = true;
                s.defer = true;
                s.dataset[attrKey] = 'true';
                s.onload = () => resolve();
                s.onerror = () => reject(new Error('Failed to load intl-tel-input'));
                document.head.appendChild(s);
            });
        }

        function initITI() {
            try {
                const iti = window.intlTelInput(phoneInput, {
                    initialCountry: 'bj',
                    preferredCountries: ['bj','ci','tg','bf','sn','gh','ng','fr'],
                    utilsScript: CDN.utils,
                    nationalMode: false, // keep international format with +
                    separateDialCode: false,
                    dropdownContainer: document.body,
                });

                // Hide custom country select (plugin provides its own dropdown)
                if (phoneCountry) {
                    phoneCountry.closest('.display')?.classList.add('has-iti');
                    phoneCountry.style.display = 'none';
                }

                function showPhoneError(msg) {
                    if (!phoneError) return;
                    phoneError.textContent = msg || 'Numéro invalide';
                    phoneError.classList.remove('hidden');
                }
                function hidePhoneError() {
                    if (!phoneError) return;
                    phoneError.classList.add('hidden');
                    phoneError.textContent = '';
                }

                // Validate on blur
                phoneInput.addEventListener('blur', () => {
                    if (phoneInput.value.trim() === '') return; // allow empty pre-submit
                    if (!iti.isValidNumber()) {
                        showPhoneError('Veuillez saisir un numéro valide');
                    } else {
                        hidePhoneError();
                    }
                });
                // Soften errors while typing
                phoneInput.addEventListener('input', () => {
                    if (/^[+\d\s()\-]*$/.test(phoneInput.value)) hidePhoneError();
                });

                // Hook submit: normalize to E.164
                if (submitBtn) {
                    submitBtn.addEventListener('click', (e) => {
                        const value = phoneInput.value.trim();
                        if (!value) {
                            showPhoneError('Veuillez saisir votre numéro');
                            e.preventDefault();
                            e.stopPropagation();
                            phoneInput.focus();
                            return;
                        }
                        if (phoneInput.__iti) {
                            const iti = phoneInput.__iti;
                            if (!iti.isValidNumber()) {
                                showPhoneError('Veuillez saisir un numéro valide');
                                e.preventDefault();
                                e.stopPropagation();
                                phoneInput.focus();
                                return;
                            }
                            const e164 = iti.getNumber();
                            if (e164) phoneInput.value = e164;
                        } else {
                            // Fallback: ensure it starts with + and looks sane
                            const ok = /^[+0-9 ()\-]{6,20}$/.test(value);
                            if (!ok) {
                                showPhoneError('Numéro invalide. Utilisez un format international (+... ...)');
                                e.preventDefault();
                                e.stopPropagation();
                                phoneInput.focus();
                                return;
                            }
                        }
                    });
                }

                // Expose instance to be used globally by validatePhone
                phoneInput.__iti = iti;

                return true;
            } catch (err) {
                console.warn('intl-tel-input init error:', err);
                return false;
            }
        }

        // Load CDN and init
        loadCSSOnce(CDN.css)
            .then(() => loadScriptOnce(CDN.js, 'iti'))
            .then(() => {
                const ok = initITI();
                if (!ok) throw new Error('Could not init iti');
            })
            .catch(() => {
                // Fallback to current minimal multi-country logic if CDN fails
                console.warn('intl-tel-input non disponible, fallback local.');
            });
    })();

    function calculateTotalHeight(form) {
        const cardStyle = window.getComputedStyle(authCard);
        const paddingTop = parseFloat(cardStyle.paddingTop);
        const paddingBottom = parseFloat(cardStyle.paddingBottom);
        const borderTop = parseFloat(cardStyle.borderTopWidth);
        const borderBottom = parseFloat(cardStyle.borderBottomWidth);

        return form.offsetHeight + paddingTop + paddingBottom + borderTop + borderBottom;
    }

    const formObserver = new MutationObserver((mutations) => {
        const activeForm = document.querySelector(".auth-form.active");
        if (activeForm) {
            const newHeight = calculateTotalHeight(activeForm);
            authCard.style.height = `${newHeight}px`;
        }
    });

    const observerConfig = {
        childList: true,
        subtree: true,
        attributes: true,
        characterData: true
    };

    formObserver.observe(loginForm, observerConfig);
    formObserver.observe(registerForm, observerConfig);

    // Helpers to enable/disable all controls in a form
    function setFormControlsEnabled(form, enabled) {
        if (!form) return;
        const fields = form.querySelectorAll('input, select, textarea, button');
        fields.forEach(el => {
            if (enabled) {
                if (el.dataset._disabledByToggle === '1') {
                    el.disabled = false;
                    delete el.dataset._disabledByToggle;
                }
            } else {
                if (!el.disabled) {
                    el.disabled = true;
                    el.dataset._disabledByToggle = '1';
                }
            }
        });
    }

    // On load: enable only the active form, disable the other
    const activeForm = document.querySelector('.auth-form.active');
    if (activeForm === loginForm) {
        setFormControlsEnabled(loginForm, true);
        setFormControlsEnabled(registerForm, false);
    } else if (activeForm === registerForm) {
        setFormControlsEnabled(registerForm, true);
        setFormControlsEnabled(loginForm, false);
    }

    function switchForms(showLogin) {
        const currentForm = showLogin ? registerForm : loginForm;
        const nextForm = showLogin ? loginForm : registerForm;

        const currentHeight = authCard.offsetHeight;
        authCard.style.height = `${currentHeight}px`;

        void authCard.offsetWidth;

        currentForm.classList.remove("active");

        setTimeout(() => {
            nextForm.classList.add("active");

            const newHeight = calculateTotalHeight(nextForm);

            authCard.style.height = `${newHeight}px`;
            // Ensure icons are rendered and toggles are wired after switch
            ensureLucideIcons();
            initPasswordToggles(nextForm);
        }, 50);

        // Enable next form controls and disable current to avoid native validation on hidden fields
        setFormControlsEnabled(nextForm, true);
        setFormControlsEnabled(currentForm, false);
    }

    // Display login form by default and set initial height
    loginForm.classList.add("active");
    authCard.style.height = `${calculateTotalHeight(loginForm)}px`;
    ensureLucideIcons();
    initPasswordToggles(loginForm);

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
            // termsSection.style.display = 'block';
            submitBtn.style.display = 'flex';
            prevBtn.style.display = 'flex';
        } else if (step === 1) {
            prevBtn.style.display = 'none';
            submitBtn.style.display = 'none';
            // termsSection.style.display = 'none';
        } else {
            nextBtn.style.display = 'block';
            prevBtn.style.display = 'flex';
            nextBtn.textContent = 'Suivant';
            // termsSection.style.display = 'none';
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
        // Render icons when step sections change (password fields appear at step 2)
        ensureLucideIcons();
        initPasswordToggles(activeForm);
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
                    validateSchool(school.value, schoolError) &&
                    validatePhone(phoneInput.value, phoneInput, phoneError);
                break;

            case 2: // Étape 2: Sécurité
                canProceed = validatePassword(password.value, password, passwordError) &&
                    validateConfirmPassword(confirmPassword.value, confirmPassword, confirmPasswordError, password);
                break;

            case 3: // Étape 3: Hackathon
                // Valider la compétence principale
                if (special_comp?.value === '') {
                    showError(special_comp, document.getElementById('mainSkillError'), "Sélectionnez votre compétence principale");
                    canProceed = false;
                    break;
                }

                // Valider le niveau d'étude
                if (study_level?.value === '') {
                    showError(study_level, document.getElementById('educationLevelError'), "Sélectionnez votre niveau d'étude");
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
        lucide.createIcons();

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

            // Validation du numéro de téléphone
            if (!validatePhone(phoneInput.value, phoneInput, phoneError)) {
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
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Redirection
                window.location.href = data.redirect;
            } else {
                showNotification('Veuillez corriger les erreurs', data.error || data.message || "Erreur lors de l'inscription", 'warning');
            }
        } catch (error) {
            console.error('Validation error:', error.message);
            showNotification('Une erreur est survenue', error.message, 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i data-lucide="send"></i>S\'inscrire';
            lucide.createIcons();
        }
    });

    // Gestionnaire de formulaire de connexion
    signinForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i data-lucide="loader-circle" class="animate-spin"></i> Traitement...';
        lucide.createIcons();

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Erreur de connexion');
            }

            // Stockage des tokens
            if (data.success) {
                console.log(data);
                setFlashMessage('success', data.message, data.username);
                window.location.href = data.redirect;
            } else if (!data.success) {
                showNotification("Erreur lors de la connexion", data.message || data.error || 'Veuillez corriger les erreurs', 'warning');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i data-lucide="send"></i> Se connecter';
                lucide.createIcons();
                return;
            }
        } catch (error) {
            showNotification('Une erreur est survenue', error.message, 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i data-lucide="send"></i> Se connecter';
            lucide.createIcons();
        }
    });  

    // function pour rechercher un nom d'utilisateur
    async function checkUsername(Username) {
        fetch('/api/auth/check-username', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
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
        fetch('/api/auth/check-email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
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

    // ===== Utility: temporarily disable hidden required controls to avoid native validation focus errors =====
    function disableHiddenRequiredControls(form) {
        const toRestore = [];
        const fields = form.querySelectorAll('input, select, textarea');
        fields.forEach((el) => {
            if (!el.required) return;
            // Consider element hidden if not in layout or CSS-hidden
            const isHidden = (el.offsetParent === null) || (getComputedStyle(el).visibility === 'hidden') || (getComputedStyle(el).display === 'none');
            if (isHidden) {
                toRestore.push(el);
                el.dataset._tempDisabled = '1';
                el.disabled = true;
            }
        });
        return function restore() {
            toRestore.forEach((el) => {
                if (el.dataset._tempDisabled) {
                    el.disabled = false;
                    delete el.dataset._tempDisabled;
                }
            });
        };
    }

    // Hook: when clicking on any submit button inside auth forms, disable hidden required controls first
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('button[type="submit"]');
        if (!btn) return;
        const form = btn.form || btn.closest('form');
        if (!form || !form.classList.contains('auth-form')) return;
        // Email JS validation
        const emailEl = form.querySelector('input[type="email"][name="email"]');
        if (emailEl) {
            const clean = sanitizeEmailValue(emailEl.value);
            emailEl.value = clean;
            if (!clean || !EMAIL_REGEX.test(clean)) {
                emailEl.setCustomValidity('Veuillez saisir une adresse e-mail valide.');
                emailEl.reportValidity();
                e.preventDefault();
                e.stopPropagation();
                emailEl.focus();
                return;
            }
            emailEl.setCustomValidity('');
        }
        const restore = disableHiddenRequiredControls(form);
        // Restore after the browser finished validation (submit or invalid)
        form.addEventListener('submit', () => setTimeout(restore, 0), { once: true });
        form.addEventListener('invalid', () => restore(), { once: true, capture: true });
    }, true);
});