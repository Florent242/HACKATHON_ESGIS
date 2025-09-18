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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Authentification</title>
    <link rel="stylesheet" href="/css/styles/auth.css">
    <link rel="stylesheet" href="/css/styles/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
    <script defer src="/js/auth.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>


</head>

<body>
    <?php require_once "../includes/header.php"; ?>
    <div id="notification-data" data-notification='<?= htmlspecialchars(json_encode($_SESSION['notification'] ?? null)) ?>'></div>
    <div class="auth-container">
        <!-- Onglets pour basculer entre connexion et inscription -->
        <div class="auth-tabs">
            <button class="auth-tab active" id="tab-login">Utilisateur</button>
            <button class="auth-tab" id="tab-register">Inscription</button>
        </div>

        <!-- Formulaires -->
        <div class="auth-card">
            <div class="auth-form" id="loginForm">
                <h1>Espace Utilisateur</h1>
                <p>Connectez-vous à votre compte étudiant</p> <br>
                <form action="/api/auth/login" method="POST" id="signinForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="form-group">
                        <label for="email_user">Email ou nom d'utilisateur</label>
                        <div class="display p-2 shadow-lg shadow-indigo-300/10">
                            <i data-lucide="mail"></i>
                            <input type="text" id="email_user" name="identifier" placeholder="etudiant@esgis.bj" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password_user">Mot de passe</label>
                        <div class="display relative p-2 shadow-lg shadow-indigo-300/10">
                            <i data-lucide="key"></i>
                            <input
                                type="password"
                                id="password_user"
                                name="password"
                                placeholder="............"
                                required
                                autocomplete="current-password"
                                aria-describedby="password_help">
                            <button
                                type="button"
                                class="toggle-password absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-blue-500 focus:outline-none transition-colors duration-200"
                                aria-label="Afficher le mot de passe"
                                title="Afficher/Masquer le mot de passe">
                                <i data-lucide="eye" class=""></i>
                            </button>
                        </div>
                        <p id="password_help" class="error-message mt-1.5 text-xs sm:text-sm"></p>
                    </div>

                    <div class="flex flex-row justify-center items-center mx-auto max-w-[60%] max-md:text-sm">
                        <label for="remember_me" class="animated-checkbox">
                            <input type="checkbox" id="remember_me" name="remember_me" class="animated-checkbox-input">
                            <span class="animated-checkbox-check"></span>
                            <span class="animated-checkbox-label text-sm max-md:text-sm">Rester connecté</span>
                        </label>
                    </div>
                    <button type="submit" class="submit-btn"> <i data-lucide="send"></i>Se connecter</button>
                </form>
            </div>

            <div class="auth-form" id="registerForm">
                <h1>Inscription</h1>
                <p>Créez votre compte EsgisHub</p>
                <div class="steps-compact">
                    <div class="progress-track">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                    <div class="step-bubbles">
                        <div class="bubble active" data-step="1" data-tooltip="Informations personnelles">
                            <span>1</span>
                        </div>
                        <div class="bubble" data-step="2" data-tooltip="Sécurité">
                            <span>2</span>
                        </div>
                        <div class="bubble" data-step="3" data-tooltip="Hackathon">
                            <span>3</span>
                        </div>
                    </div>
                </div>
                <form action="/api/auth/register" method="POST" id="registrationForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <!-- Section Informations personnelles -->
                    <div class="form-section">
                        <h3 class="section-title">Informations personnelles</h3>

                        <div class="form-group">
                            <label for="fullname" class="label after:ml-1 after:text-red-500 after:content-['*']">Nom complet</label>
                            <div class="display p-2 shadow-lg shadow-indigo-300/10">
                                <i data-lucide="user"></i>
                                <input type="text" id="fullname" name="fullname" placeholder="Votre nom complet" required>
                            </div>
                            <span class="error-message absolute top-full text-red-500 text-sm mt-1 hidden" id="fullNameError"></span>
                        </div>
                        <div class="flex gap-2 flex-row justify-between">

                            <div class="form-group">
                                <label for="username" class="label after:ml-1 after:text-red-500 after:content-['*']">Nom d'utilisateur</label>
                                <div class="display p-2 shadow-lg shadow-indigo-300/10">
                                    <i data-lucide="user"></i>
                                    <input type="text" id="username" name="username" placeholder="Votre pseudo" required>
                                </div>
                                <span class="error-message absolute top-full text-red-500 text-sm mt-1 hidden" id="usernameError"></span>
                            </div>

                            <div class="form-group">
                                <label for="email" class="label after:ml-1 after:text-red-500 after:content-['*']">Email</label>
                                <div class="display p-2 shadow-lg shadow-indigo-300/10">
                                    <i data-lucide="mail"></i>
                                    <input type="email" id="email" name="email" placeholder="etudiant@esgis.bj" required>
                                </div>
                                <span class="error-message absolute top-full text-red-500 text-sm mt-1 hidden" id="emailError"></span>
                            </div>
                        </div>

                        <div class="flex gap-2 flex-row justify-between">
                            <div class="form-group">
                                <label for="phone" class="label after:ml-1 after:text-red-500 after:content-['*']">Téléphone</label>
                                <div class="display p-2 shadow-lg shadow-indigo-300/10 flex items-center gap-2">
                                    <i data-lucide="phone"></i>
                                    <select id="phone_country" aria-label="Indicatif pays" class="w-[110px] bg-transparent outline-none px-2 py-2 border border-gray-700 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                        <option value="BJ" selected>+229 BJ</option>
                                        <option value="TG">+228 TG</option>
                                        <option value="CI">+225 CI</option>
                                        <option value="BF">+226 BF</option>
                                        <option value="SN">+221 SN</option>
                                        <option value="GH">+233 GH</option>
                                        <option value="NG">+234 NG</option>
                                        <option value="FR">+33 FR</option>
                                    </select>
                                    <input
                                        type="tel"
                                        id="phone"
                                        name="phone"
                                        placeholder="+229 XX XX XX XX"
                                        autocomplete="tel"
                                        inputmode="tel"
                                        required>
                                </div>
                                <span class="error-message hidden text-[11px] text-red-400 mt-1" id="phoneError"></span>
                            </div>
                            <div class="form-group">
                                <label for="school" class="label after:ml-1 after:text-red-500 after:content-['*']">École</label>
                                <div class="custom-select-container">
                                    <div class="display p-2 shadow-lg shadow-indigo-300/10">
                                        <i data-lucide="school"></i>
                                        <select id="school" name="school" required aria-label="Sélectionnez votre école" class="school-select w-full text-white bg-transparent outline-none px-3 py-2 border border-gray-700 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                            <option value="" disabled selected>— Sélectionnez votre école —</option>
                                        </select>
                                        <i data-lucide="chevron-down" class="select-arrow"></i>
                                    </div>
                                </div>
                                <span class="error-message hidden" id="schoolError"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Section Sécurité -->
                    <div class="form-section mt-6">
                        <h3 class="section-title">Sécurité</h3>

                        <div class="form-group">
                            <label for="password" class="label after:ml-1 after:text-red-500 after:content-['*']">Mot de passe</label>
                            <div class="display p-2 shadow-lg shadow-indigo-300/10">
                                <i data-lucide="key"></i>
                                <input type="password" id="password" name="password" placeholder="Minimum 8 caractères" required autocomplete="new-password">
                                <button type="button" class="toggle-password" aria-label="Afficher le mot de passe" title="Afficher/Masquer le mot de passe"><i data-lucide="eye"></i></button>
                            </div>
                            <span class="error-message absolute top-full text-red-500 text-sm mt-1 hidden" id="passwordError"></span>
                        </div>

                        <div class="form-group">
                            <label for="confirmPassword" class="label after:ml-1 after:text-red-500 after:content-['*']">Confirmer le mot de passe</label>
                            <div class="display p-2 shadow-lg shadow-indigo-300/10">
                                <i data-lucide="key"></i>
                                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Retapez votre mot de passe" required autocomplete="new-password">
                                <button type="button" class="toggle-password" aria-label="Afficher le mot de passe" title="Afficher/Masquer le mot de passe"><i data-lucide="eye"></i></button>
                            </div>
                            <span class="error-message absolute top-full text-red-500 text-sm mt-1 hidden" id="confirmPasswordError"></span>
                        </div>
                    </div>

                    <!-- Section Hackathon -->
                    <div class="form-section mt-6">
                        <h3 class="section-title">Participation au Hackathon</h3>

                        <div class="form-group mb-4">
                            <label for="main_skill" class="label after:ml-1 after:text-red-500 after:content-['*'] mb-1.5 block text-sm font-medium text-gray-300">Compétence principale</label>
                            <div class="relative w-full">
                                <div class="flex items-center bg-gray-800/80 border border-gray-600/50 rounded-md px-2.5 py-1.5 text-sm focus-within:ring-1 focus-within:ring-indigo-500 focus-within:border-indigo-500 transition-all w-full duration-150">
                                    <i data-lucide="code" class="w-3.5 h-3.5 text-indigo-400 mr-2 flex-shrink-0"></i>
                                    <select
                                        id="main_skill"
                                        name="main_skill"
                                        required
                                        class="w-full bg-transparent text-sm text-gray-100 placeholder-gray-400 border-none focus:ring-0 focus:outline-none appearance-none cursor-pointer pr-5 py-0.5">
                                        <option value="" class="bg-gray-800 text-gray-300">Sélectionnez votre compétence</option>
                                        <optgroup label="Développement" class="bg-gray-800 text-gray-200">
                                            <option value="fullstack" class="hover:bg-indigo-600">Développement Full-Stack</option>
                                            <option value="frontend" class="hover:bg-indigo-600">Développement Frontend</option>
                                            <option value="backend" class="hover:bg-indigo-600">Développement Backend</option>
                                            <option value="mobile" class="hover:bg-indigo-600">Développement Mobile</option>
                                            <option value="game" class="hover:bg-indigo-600">Développement de Jeux</option>
                                        </optgroup>
                                        <optgroup label="Sécurité & Réseau" class="bg-gray-800 text-gray-200">
                                            <option value="cybersec" class="hover:bg-indigo-600">Cybersécurité / Hacking Éthique</option>
                                            <option value="network" class="hover:bg-indigo-600">Réseaux & Infrastructure</option>
                                            <option value="blockchain" class="hover:bg-indigo-600">Blockchain & Web3</option>
                                        </optgroup>
                                        <optgroup label="Data & IA" class="bg-gray-800 text-gray-200">
                                            <option value="ai" class="hover:bg-indigo-600">Intelligence Artificielle</option>
                                            <option value="data" class="hover:bg-indigo-600">Science des Données</option>
                                            <option value="ml" class="hover:bg-indigo-600">Machine Learning</option>
                                        </optgroup>
                                        <optgroup label="Design & Création" class="bg-gray-800 text-gray-200">
                                            <option value="design" class="hover:bg-indigo-600">Design UI/UX</option>
                                            <option value="graphism" class="hover:bg-indigo-600">Graphisme & Motion Design</option>
                                            <option value="3d" class="hover:bg-indigo-600">Modélisation 3D</option>
                                        </optgroup>
                                        <optgroup label="Autres" class="bg-gray-800 text-gray-200">
                                            <option value="iot" class="hover:bg-indigo-600">IoT & Hardware</option>
                                            <option value="marketing" class="hover:bg-indigo-600">Marketing Digital</option>
                                            <option value="product" class="hover:bg-indigo-600">Product Management</option>
                                            <option value="devops" class="hover:bg-indigo-600">DevOps & Cloud</option>
                                        </optgroup>
                                    </select>
                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-400 absolute right-2 pointer-events-none"></i>
                                </div>
                            </div>
                            <span class="error-message hidden text-[11px] text-red-400 mt-1" id="mainSkillError"></span>
                        </div>

                        <div class="form-group">
                            <label for="education_level" class="label after:ml-1 after:text-red-500 after:content-['*']">Niveau d'étude</label>
                            <div class="custom-select-container">
                                <div class="display p-2 shadow-lg shadow-indigo-300/10">
                                    <i data-lucide="book"></i>
                                    <select id="education_level" name="education_level" required>
                                        <option value="">Sélectionnez votre niveau</option>
                                        <option value="licence1">Licence 1</option>
                                        <option value="licence2">Licence 2</option>
                                        <option value="licence3">Licence 3</option>
                                        <option value="master1">Master 1</option>
                                        <option value="master2">Master 2</option>
                                        <option value="doctorat">Doctorat</option>
                                        <option value="aucun">Aucun</option>
                                    </select>
                                    <i data-lucide="chevron-down" class="select-arrow"></i>
                                </div>
                            </div>
                            <span class="error-message hidden" id="educationLevelError"></span>
                        </div>
                    </div>

                    <!-- Section Conditions -->
                    <div class="form-group mt-6">
                        <div class="flex items-center w-full">
                            <input class="checked:bg-blue-500 w-sm" type="checkbox" id="terms" name="terms" required class="mr-2">
                            <label for="terms" class="text-sm w-full">J'accepte les <a href="/conditions" target="_blank" class="text-indigo-500 hover:underline">conditions d'utilisation</a> et la <a href="/privacy" target="_blank" class="text-indigo-500 hover:underline">politique de confidentialité</a></label>
                        </div>
                    </div>

                    <div class="steps-navigation">
                        <button type="button" class="prev-step-btn" disabled>
                            <i data-lucide="chevron-left"></i> Précédent
                        </button>
                        <button type="button" class="next-step-btn">
                            Suivant <i data-lucide="chevron-right"></i>
                        </button>
                    </div>

                    <button type="submit" id="signup" class="submit-btn mt-6"><i data-lucide="send"></i>S'inscrire</button>
                </form>
            </div>
        </div>
        <br>
    </div>

</body>

</html>