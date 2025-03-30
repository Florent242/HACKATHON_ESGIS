<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$errorMessage = isset($_GET['error']) ? urldecode($_GET['error']) : null;
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - S'inscrire</title>
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/auth.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/header.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/dist/output.css">
    <script defer src="/HACKATHON_ESGIS/public/js/auth.js"></script>
    <!-- Lucide Icons -->
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.280.0/dist/umd/lucide.min.js"></script>
</head>

<body>
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
                <form action="/HACKATHON_ESGIS/public/api/auth/login" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="form-group">
                        <label for="email_user">Email</label>
                        <div class="display p-2 shadow-lg shadow-indigo-300/10">
                            <i data-lucide="mail"></i>
                            <input type="email" id="email_user" name="email" placeholder="etudiant@esgis.bj" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password_user">Mot de passe</label>
                        <div class="display p-2 shadow-lg shadow-indigo-300/10">
                            <i data-lucide="key"></i>
                            <input type="password" id="password_user" name="password" placeholder="............" required>
                        </div>
                    </div>

                    <div class="mb-1">
                        <label for="remember_me">Rester connecté</label>
                        <input type="checkbox" class="text-white checked:bg-blue-500" name="remember_me" id="remember_me">
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
                        <div class="bubble active" data-step="1" title="Informations personnelles">
                            <span>1</span>
                        </div>
                        <div class="bubble" data-step="2" title="Sécurité">
                            <span>2</span>
                        </div>
                        <div class="bubble" data-step="3" title="Hackathon">
                            <span>3</span>
                        </div>
                    </div>
                </div>
                <form action="/HACKATHON_ESGIS/public/api/auth/register" method="POST" id="registrationForm">
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
                            <span class="error-message absolute top-full text-red-500 text-xs mt-1 hidden" id="fullNameError"></span>
                        </div>
                        <div class="flex gap-2 flex-row justify-between">

                            <div class="form-group">
                                <label for="username" class="label after:ml-1 after:text-red-500 after:content-['*']">Nom d'utilisateur</label>
                                <div class="display p-2 shadow-lg shadow-indigo-300/10">
                                    <i data-lucide="user"></i>
                                    <input type="text" id="username" name="username" placeholder="Votre pseudo" required>
                                </div>
                                <span class="error-message absolute top-full text-red-500 text-xs mt-1 hidden" id="usernameError"></span>
                            </div>

                            <div class="form-group">
                                <label for="email" class="label after:ml-1 after:text-red-500 after:content-['*']">Email</label>
                                <div class="display p-2 shadow-lg shadow-indigo-300/10">
                                    <i data-lucide="mail"></i>
                                    <input type="email" id="email" name="email" placeholder="etudiant@esgis.bj" required>
                                </div>
                                <span class="error-message absolute top-full text-red-500 text-xs mt-1 hidden" id="emailError"></span>
                            </div>
                        </div>
                        
                        <div class="flex gap-2 flex-row justify-between">
                            <div class="form-group">
                                <label for="phone" class="label after:ml-1 after:text-red-500 after:content-['*']">Téléphone</label>
                                <div class="display p-2 shadow-lg shadow-indigo-300/10">
                                    <i data-lucide="phone"></i>
                                    <input type="tel" id="phone" name="phone" placeholder="+229 XX XX XX XX" required>
                                </div>
                                <span class="error-message absolute top-full text-red-500 text-xs mt-1 hidden" id="phoneError"></span>
                            </div>
                            <div class="form-group">
                                <label for="school" class="label after:ml-1 after:text-red-500 after:content-['*']">École</label>
                                <div class="display p-2 shadow-lg shadow-indigo-300/10">
                                    <i data-lucide="school"></i>
                                    <input type="text" id="school" name="school" placeholder="Votre école" required>
                                </div>
                                <span class="error-message absolute top-full text-red-500 text-xs mt-1 hidden" id="schoolError"></span>
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
                                <input type="password" id="password" name="password" placeholder="Minimum 8 caractères" required>
                            </div>
                            <span class="error-message absolute top-full text-red-500 text-xs mt-1 hidden" id="passwordError"></span>
                        </div>

                        <div class="form-group">
                            <label for="confirmPassword" class="label after:ml-1 after:text-red-500 after:content-['*']">Confirmer le mot de passe</label>
                            <div class="display p-2 shadow-lg shadow-indigo-300/10">
                                <i data-lucide="key"></i>
                                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Retapez votre mot de passe" required>
                            </div>
                            <span class="error-message absolute top-full text-red-500 text-xs mt-1 hidden" id="confirmPasswordError"></span>
                        </div>
                    </div>

                    <!-- Section Hackathon -->
                    <div class="form-section mt-6">
                        <h3 class="section-title">Participation au Hackathon</h3>

                        <div class="form-group">
                            <label for="main_skill" class="label after:ml-1 after:text-red-500 after:content-['*']">Compétence principale</label>
                            <div class="custom-select-container">
                                <div class="display p-2 shadow-lg shadow-indigo-300/10">
                                    <i data-lucide="code"></i>
                                    <select id="main_skill" name="main_skill" required>
                                        <option value="">Sélectionnez votre compétence principale</option>
                                        <option value="frontend">Développement Frontend</option>
                                        <option value="backend">Développement Backend</option>
                                        <option value="design">Design UI/UX</option>
                                        <option value="ai">Intelligence Artificielle</option>
                                        <option value="data">Science des Données</option>
                                        <option value="marketing">Marketing Digital</option>
                                        <option value="iot">IoT & Hardware</option>
                                        <option value="blockchain">Blockchain</option>
                                    </select>
                                    <i data-lucide="chevron-down" class="select-arrow"></i>
                                </div>
                            </div>
                            <span class="error-message hidden" id="mainSkillError"></span>
                        </div>

                        <div class="form-group">
                            <label for="project_idea" class="label">Idée de projet (optionnel)</label>
                            <div class="display p-2 shadow-lg shadow-indigo-300/10">
                                <i data-lucide="lightbulb"></i>
                                <textarea id="project_idea" name="project_idea" rows="3" placeholder="Décrivez brièvement votre idée..."></textarea>
                            </div>
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
                            <input class="checked:bg-blue-500 w-xs" type="checkbox" id="terms" name="terms" required class="mr-2">
                            <label for="terms" class="text-sm w-full">J'accepte les <a href="/conditions" class="text-indigo-500 hover:underline">conditions d'utilisation</a> et la <a href="/privacy" class="text-indigo-500 hover:underline">politique de confidentialité</a></label>
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

    <script>
        window.addEventListener("load", function() {
            lucide.createIcons();
        });
    </script>

</body>

</html>