<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
    <title>Soumettre un défi</title>
    <link rel="stylesheet" href="/css/styles/user/challenge_submission.css">
    <?php require_once '../includes/user/head.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script defer src="/js/user/challenge_submission.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

</head>

<body>
    <?php require_once "../includes/user/header.php"; ?>

    <main class="min-h-screen py-8">
        <div class="container mx-auto px-4 max-w-6xl">
            <!-- Header avec bouton retour -->
            <div class="flex items-center mb-8 animate-fadeInUp">
                <button onclick="goBack('challenge_dev')" class="group flex items-center text-gray-300 hover:text-white transition-all duration-300 bg-gray-800/50 hover:bg-gray-700/50 px-4 py-2.5 rounded-xl border border-gray-700/50">
                    <i data-lucide="arrow-left" class="w-5 h-5 mr-2 transition-transform group-hover:-translate-x-0.5"></i>
                    <span>Retour aux défis</span>
                </button>
            </div>

            <!-- Titre principal -->
            <div class="mb-12 text-center animate-fadeInUp" style="animation-delay: 0.1s">
                <div class="inline-flex items-center justify-center px-5 py-1.5 bg-blue-900/20 text-blue-300 rounded-full text-sm font-medium mb-4">
                    <i data-lucide="cloud-upload" class="w-4 h-4 mr-2"></i>
                    <span>Soumission de solution</span>
                </div>
                <h1 class="text-4xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-cyan-400 mb-3">
                    Soumettre votre solution
                </h1>
                <p class="text-gray-400 max-w-2xl mx-auto text-lg">
                    Participez au défi en partageant votre travail. Remplissez le formulaire ci-dessous pour nous envoyer votre solution.
                </p>
            </div>

            <!-- Message d'erreur/succès -->
            <div id="alert" class="hidden mb-8 rounded-xl p-4 transition-all duration-300 ease-in-out">
                <div class="flex items-start">
                    <i data-lucide="alert-circle" class="w-5 h-5 mt-0.5 mr-3 flex-shrink-0"></i>
                    <div>
                        <p id="alert-message" class="text-sm"></p>
                    </div>
                    <button onclick="this.parentElement.classList.add('hidden')" class="ml-auto text-gray-400 hover:text-white">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Informations sur le défi -->
                <div class="lg:col-span-1 animate-fadeInUp" style="animation-delay: 0.2s">
                    <div class="bg-gradient-to-br from-gray-800/80 to-gray-900/80 backdrop-blur-sm rounded-2xl p-6 border border-gray-700/50 hover:border-blue-500/30 transition-all duration-300 card-hover-effect">
                        <div class="flex items-center mb-5">
                            <div class="p-2 bg-blue-900/30 rounded-lg mr-3">
                                <i data-lucide="award" class="w-5 h-5 text-blue-400"></i>
                            </div>
                            <h2 class="text-xl font-semibold bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">
                                Informations sur le défi
                            </h2>
                        </div>

                        <div class="space-y-6 challenge-info">
                            <div>
                                <h3 class="text-lg font-semibold text-white mb-3" id="challengeTitle">
                                    [[Titre]]
                                </h3>
                                <div class="p-4 bg-gray-800/50 rounded-xl border border-gray-700/50 mb-4">
                                    <p class="text-gray-300 text-sm leading-relaxed" id="challengeDescription">
                                        [[Description]]
                                    </p>
                                </div>
                                <div class="space-y-3 text-sm">
                                    <div class="flex items-center text-gray-400">
                                        <i data-lucide="clock" class="w-4 h-4 mr-2 text-blue-400"></i>
                                        Hackathon : <span id="hackathonTitle">[[TitreHackathon]]</span>
                                    </div>
                                    <div class="flex items-center text-gray-400">
                                        <i data-lucide="tag" class="w-4 h-4 mr-2 text-purple-400"></i>
                                        Catégorie : <span id="challengeCategory">[[Categorie]]</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Carte d'aide -->
                    <div class="mt-6 bg-gradient-to-br from-gray-800/60 to-gray-900/60 backdrop-blur-sm rounded-2xl p-5 border border-gray-700/30 transition-all duration-300 hover:border-blue-500/20">
                        <h3 class="font-medium text-gray-200 mb-3 flex items-center">
                            <i data-lucide="help-circle" class="w-5 h-5 mr-2 text-blue-400"></i>
                            Besoin d'aide ?
                        </h3>
                        <p class="text-sm text-gray-400 mb-4">
                            Notre équipe est là pour vous aider. Consultez la documentation ou contactez notre support en cas de difficulté.
                        </p>
                        <div class="space-y-2">
                            <a href="/user/ressources" class="flex items-center text-sm text-blue-400 hover:text-blue-300 transition-colors group">
                                <i data-lucide="book-open" class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform"></i>
                                Voir des ressources
                            </a>
                            <a href="https://discord.gg/FbztK5Uagd" target="_blank" class="flex items-center text-sm text-blue-400 hover:text-blue-300 transition-colors group">
                                <i data-lucide="message-circle" class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform"></i>
                                Contacter le support
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Formulaire de soumission -->
                <div class="lg:col-span-2">
                    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                        <h2 class="text-xl font-semibold mb-6">Formulaire de soumission</h2>
                        <!-- Type de soumission -->
                        <div>
                            <label class="block text-sm font-medium mb-3">Type de soumission</label>
                            <div class="flex space-x-4">
                                <button type="button" id="githubBtn" class="flex-1 flex items-center justify-center px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <i data-lucide="github" class="w-5 h-5 mr-2"></i>
                                    Dépôt GitHub
                                </button>
                                <button type="button" id="zipBtn" class="flex-1 flex items-center justify-center px-4 py-3 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition-colors">
                                    <i data-lucide="folder-archive" class="w-5 h-5 mr-2"></i>
                                    Fichier ZIP
                                </button>
                            </div>
                        </div>

                        <hr>

                        <div id="formContainer">

                            <!-- github submissionForm -->

                            <form id="githubSubmissionForm" class="space-y-6">

                                <!-- Contenu GitHub -->
                                <div id="githubContent" class="space-y-4">
                                    <div>
                                        <label class="flex items-center text-sm font-medium mb-2">
                                            <i data-lucide="github" class="w-4 h-4 mr-2 text-blue-400"></i>
                                            URL du dépôt GitHub <span class="text-red-400">*</span>
                                        </label>
                                        <input type="url" id="githubUrl" placeholder="https://github.com/username/repo" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400">
                                        <p class="text-xs text-gray-400 flex items-start">
                                            <i data-lucide="info" class="w-3.5 h-3.5 mr-1.5 text-blue-400 mt-0.5 flex-shrink-0"></i>
                                            Lien vers le dépôt GitHub contenant votre code source.
                                        </p>
                                    </div>
                                </div>

                                <!-- URL de démonstration -->
                                <div>
                                    <label class="flex items-center text-sm font-medium mb-2">
                                        <i data-lucide="link" class="w-4 h-4 mr-2 text-blue-400"></i>
                                        URL de démonstration
                                    </label>
                                    <input type="url" id="githubDemoUrl" placeholder="https://votre-demo.vercel.app" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400">
                                    <p class="text-xs text-gray-400 flex items-start">
                                        <i data-lucide="info" class="w-3.5 h-3.5 mr-1.5 text-blue-400 mt-0.5 flex-shrink-0"></i>
                                        Lien vers une démonstration en ligne de votre solution, si disponible.
                                    </p>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label class="flex items-center text-sm font-medium mb-2">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2 text-blue-400"></i>
                                        Description de votre solution <span class="text-red-400">*</span>
                                    </label>
                                    <textarea id="githubDescription" rows="4" placeholder="Décrivez comment vous avez abordé et implémenté votre solution..." class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400 resize-vertical"></textarea>
                                    <p class="text-xs text-gray-400 flex items-start">
                                        <i data-lucide="info" class="w-3.5 h-3.5 mr-1.5 text-blue-400 mt-0.5 flex-shrink-0"></i>
                                        Décrivez comment vous avez abordé et implémenté votre solution.
                                    </p>
                                </div>

                                <!-- Notes additionnelles -->
                                <div>
                                    <label class="flex items-center text-sm font-medium mb-2">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2 text-blue-400"></i>
                                        Notes additionnelles
                                    </label>
                                    <textarea id="githubNotes" rows="3" placeholder="Instructions pour exécuter le projet, identifiants de test, défis rencontrés, etc." class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400 resize-vertical"></textarea>
                                    <p class="text-xs text-gray-400 flex items-start">
                                        <i data-lucide="info" class="w-3.5 h-3.5 mr-1.5 text-blue-400 mt-0.5 flex-shrink-0"></i>
                                        Instructions pour exécuter le projet, identifiants de test, défis rencontrés, etc.
                                    </p>
                                </div>

                                <!-- Avant de soumettre -->
                                <div class="bg-gradient-to-br from-blue-900/20 to-blue-900/10 border border-blue-500/20 rounded-xl p-5 backdrop-blur-sm">
                                    <div class="flex items-start mb-4">
                                        <div class="p-2 bg-blue-900/30 rounded-lg mr-3">
                                            <i data-lucide="shield-check" class="w-5 h-5 text-blue-400"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-blue-100 mb-1">Avant de soumettre</h4>
                                            <p class="text-xs text-blue-300/80">Veuillez vérifier ces points importants</p>
                                        </div>
                                    </div>
                                    <ul class="text-sm text-gray-300 space-y-2">
                                        <li class="flex items-start group">
                                            <div class="p-1 bg-blue-900/30 rounded-full mr-3 group-hover:bg-blue-800/50 transition-colors">
                                                <i data-lucide="check" class="w-3 h-3 text-blue-400"></i>
                                            </div>
                                            <span id="checklistGithub">Assurez-vous que votre dépôt GitHub est public ou accessible à notre équipe.</span>
                                        </li>
                                        <li class="flex items-start group">
                                            <div class="p-1 bg-blue-900/30 rounded-full mr-3 group-hover:bg-blue-800/50 transition-colors">
                                                <i data-lucide="check" class="w-3 h-3 text-blue-400"></i>
                                            </div>
                                            <span>Vérifiez que vous avez inclus un README avec les instructions pour installer et exécuter votre projet.</span>
                                        </li>
                                        <li class="flex items-start group">
                                            <div class="p-1 bg-blue-900/30 rounded-full mr-3 group-hover:bg-blue-800/50 transition-colors">
                                                <i data-lucide="check" class="w-3 h-3 text-blue-400"></i>
                                            </div>
                                            <span id="checklistContent">Évitez d'inclure les dossiers node_modules ou autres dépendances volumineuses.</span>
                                        </li>
                                        <li class="flex items-start group">
                                            <div class="p-1 bg-blue-900/30 rounded-full mr-3 group-hover:bg-blue-800/50 transition-colors">
                                                <i data-lucide="check" class="w-3 h-3 text-blue-400"></i>
                                            </div>
                                            <span>Assurez-vous que votre code est propre, documenté et suit les bonnes pratiques.</span>
                                        </li>
                                        <li class="flex items-start group">
                                            <div class="p-1 bg-blue-900/30 rounded-full mr-3 group-hover:bg-blue-800/50 transition-colors">
                                                <i data-lucide="check" class="w-3 h-3 text-blue-400"></i>
                                            </div>
                                            <span>Si votre code contient des instructions malveillantes, il sera refusé et vous serez sanctionné.</span>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Boutons d'action -->
                                <div class="flex justify-end space-x-4 pt-6">
                                    <button type="button" onclick="goBack()" class="px-6 py-3 text-gray-300 hover:text-white transition-colors">
                                        Annuler
                                    </button>
                                    <button type="submit" class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                                        <i data-lucide="send" class="w-5 h-5 mr-2"></i>
                                        Soumettre ma solution
                                    </button>
                                </div>
                            </form>

                            <!-- zip submissionForm -->
                            <form id="zipSubmissionForm" class="hidden space-y-6 zip" enctype="multipart/form-data">

                                <!-- Contenu ZIP -->
                                <div id="zipContent" class="space-y-4">
                                    <div>
                                        <label class="flex items-center text-sm font-medium mb-2">
                                            <i data-lucide="folder-archive" class="w-4 h-4 mr-2 text-blue-400"></i>
                                            <span class="text-gray-200">Fichier ZIP de votre solution</span> <span class="text-red-400">*</span>
                                        </label>
                                        <div id="zipDropZone" class="mt-1 border-2 border-dashed border-gray-700/50 rounded-xl p-6 text-center bg-gray-800/30 hover:border-blue-500/50 transition-all duration-300 group/drag">
                                            <div id="dropZone" class="flex flex-col items-center justify-center space-y-3">
                                                <input type="file" id="zipFile" accept=".zip" class="hidden">
                                                <div class="p-3 mb-2 rounded-xl bg-blue-900/20 group-hover/drag:bg-blue-900/30 transition-colors">
                                                    <i data-lucide="upload-cloud" class="w-8 h-8 text-blue-400 group-hover/drag:scale-110 transition-transform"></i>
                                                </div>
                                                <p class="text-gray-300 text-sm">Glissez-déposez votre fichier ici</p>
                                                <p class="text-gray-400 text-xs mb-2">ou</p>
                                                <button type="button" onclick="document.getElementById('zipFile').click()" class="px-5 py-2.5 bg-gray-700/50 hover:bg-gray-700 text-white text-sm font-medium rounded-lg border border-gray-600/50 hover:border-blue-500/50 transition-all duration-300 flex items-center">
                                                    <i data-lucide="folder-open" class="w-4 h-4 mr-2"></i>
                                                    Sélectionner un fichier
                                                </button>
                                            </div>
                                        </div>
                                        <p class="mt-2 text-xs text-gray-400 flex items-start">
                                            <i data-lucide="info" class="w-3.5 h-3.5 mr-1.5 text-blue-400 mt-0.5 flex-shrink-0"></i>
                                            Fichier ZIP contenant votre code source (max 50MB). Incluez un README avec les instructions d'installation et d'exécution.
                                        </p>
                                    </div>
                                </div>

                                <!-- URL de démonstration -->
                                <div>
                                    <label class="flex items-center text-sm font-medium mb-2">
                                        <i data-lucide="link" class="w-4 h-4 mr-2 text-blue-400"></i>
                                        URL de démonstration
                                    </label>
                                    <input type="url" id="zipDemoUrl" placeholder="https://votre-demo.vercel.app" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400">
                                    <p class="text-xs text-gray-400 mt-1 flex items-start">
                                        <i data-lucide="info" class="w-3.5 h-3.5 mr-1.5 text-blue-400 mt-0.5 flex-shrink-0"></i>
                                        Lien vers une démonstration en ligne de votre solution, si disponible.
                                    </p>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label class="flex items-center text-sm font-medium mb-2">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2 text-blue-400"></i>
                                        Description de votre solution <span class="text-red-400">*</span>
                                    </label>
                                    <textarea id="zipDescription" rows="4" placeholder="Décrivez comment vous avez abordé et implémenté votre solution..." class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400 resize-vertical"></textarea>
                                    <p class="text-xs text-gray-400 mt-1 flex items-start">
                                        <i data-lucide="info" class="w-3.5 h-3.5 mr-1.5 text-blue-400 mt-0.5 flex-shrink-0"></i>
                                        Décrivez comment vous avez abordé et implémenté votre solution.
                                    </p>
                                </div>

                                <!-- Notes additionnelles -->
                                <div>
                                    <label class="flex items-center text-sm font-medium mb-2">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2 text-blue-400"></i>
                                        Notes additionnelles
                                    </label>
                                    <textarea id="zipNotes" rows="3" placeholder="Instructions pour exécuter le projet, identifiants de test, défis rencontrés, etc." class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400 resize-vertical"></textarea>
                                    <p class="text-xs text-gray-400 mt-1 flex items-start">
                                        <i data-lucide="info" class="w-3.5 h-3.5 mr-1.5 text-blue-400 mt-0.5 flex-shrink-0"></i>
                                        Instructions pour exécuter le projet, identifiants de test, défis rencontrés, etc.
                                    </p>
                                </div>

                                <!-- Avant de soumettre -->
                                <div class="bg-gradient-to-br from-blue-900/20 to-blue-900/10 border border-blue-500/20 rounded-xl p-5 backdrop-blur-sm">
                                    <div class="flex items-start mb-4">
                                        <div class="p-2 bg-blue-900/30 rounded-lg mr-3">
                                            <i data-lucide="shield-check" class="w-5 h-5 text-blue-400"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-blue-100 mb-1">Avant de soumettre</h4>
                                            <p class="text-xs text-blue-300/80">Veuillez vérifier ces points importants</p>
                                        </div>
                                    </div>
                                    <ul class="space-y-3">
                                        <li class="flex items-start group">
                                            <div class="p-1 bg-blue-900/30 rounded-full mr-3 group-hover:bg-blue-800/50 transition-colors">
                                                <i data-lucide="check" class="w-3 h-3 text-blue-400"></i>
                                            </div>
                                            <span class="text-sm text-blue-50/90 leading-relaxed">Vérifiez que votre code est complet et fonctionnel</span>
                                        </li>
                                        <li class="flex items-start group">
                                            <div class="p-1 bg-blue-900/30 rounded-full mr-3 group-hover:bg-blue-800/50 transition-colors">
                                                <i data-lucide="check" class="w-3 h-3 text-blue-400"></i>
                                            </div>
                                            <span class="text-sm text-blue-50/90 leading-relaxed">Assurez-vous d'avoir inclus un fichier README avec les instructions d'installation et d'exécution</span>
                                        </li>
                                        <li class="flex items-start group">
                                            <div class="p-1 bg-blue-900/30 rounded-full mr-3 group-hover:bg-blue-800/50 transition-colors">
                                                <i data-lucide="check" class="w-3 h-3 text-blue-400"></i>
                                            </div>
                                            <span class="text-sm text-blue-50/90 leading-relaxed">Vérifiez que votre solution respecte les contraintes techniques du défi</span>
                                        </li>
                                        <li class="flex items-start group">
                                            <div class="p-1 bg-blue-900/30 rounded-full mr-3 group-hover:bg-blue-800/50 transition-colors">
                                                <i data-lucide="check" class="w-3 h-3 text-blue-400"></i>
                                            </div>
                                            <span class="text-sm text-blue-50/90 leading-relaxed">Si votre code contient des instructions malveillantes, il sera refusé et vous serez sanctionné.</span>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Boutons d'action -->
                                <div class="flex justify-end space-x-4 pt-6">
                                    <button type="button" onclick="goBack()" class="px-6 py-3 text-gray-300 hover:text-white transition-colors">
                                        Annuler
                                    </button>
                                    <button type="submit" class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                                        <i data-lucide="send" class="w-5 h-5 mr-2"></i>
                                        Soumettre ma solution
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>