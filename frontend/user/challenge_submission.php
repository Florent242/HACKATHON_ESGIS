<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soumettre un défi</title>
    <link rel="stylesheet" href="/css/styles/user/challenge_submission.css">
    <link rel="stylesheet" href="/css/styles/user/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script defer src="/js/user/challenge_submission.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

</head>

<body>
    <?php require_once "../includes/user/header.php"; ?>

    <main>
        <div class="container mx-auto px-4 py-6 max-w-6xl">
            <!-- Header avec bouton retour -->
            <div class="flex items-center mb-8">
                <button onclick="goBack('challenge_dev')" class="flex items-center text-gray-300 hover:text-white transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Retour aux défis
                </button>
            </div>

            <!-- Titre principal -->
            <div class="mb-8">
                <div class="flex items-center mb-2">
                    <i data-lucide="cloud-upload" class="w-4 h-4 mr-2"></i>
                    <h1 class="text-3xl font-bold">Soumettre votre solution</h1>
                </div>
                <p class="text-gray-400">Complétez le formulaire ci-dessous pour soumettre votre solution au défi.</p>
            </div>

            <!-- Message d'erreur/succès -->
            <div id="alert" class="hidden mb-6 p-4 rounded-lg border"></div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Informations sur le défi -->
                <div class="lg:col-span-1">
                    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                        <h2 class="text-xl font-semibold mb-4">Informations sur le défi</h2>

                        <div class="space-y-6 challenge-info">
                            <div class="mb-6">
                                <h3 class="text-lg font-medium mb-2" id="challengeTitle">
                                    [[Title]]
                                </h3>
                                <p class="text-gray-400 text-sm mb-4" id="challengeDescription">
                                    [[Description]]
                                </p>
                            </div>

                            <div class="space-y-3 mb-6">
                                <div class="flex items-center text-sm">
                                    <i data-lucide="pin" class="w-3 h-3 mr-2"></i>
                                    <span class="text-gray-400">Date limite:</span>
                                    <span class="ml-2" id="challengeDeadline"></span>
                                </div>
                                <div class="flex items-center text-sm">
                                    <i data-lucide="code-xml" class="w-4 h-4 mr-2"></i>
                                    <span class="text-gray-400">Technologies:</span>
                                    <span class="ml-2" id="challengeTechnologies"></span>
                                </div>
                            </div>

                            <div>
                                <h4 class="font-medium mb-3">Ressources</h4>
                                <div class="space-y-2"id="challengeResources">
                                    <a href="" class="flex items-center justify-between text-blue-400 hover:text-blue-300 text-sm transition-colors">
                                        <div class="flex items-center space-x-2 flex-row">
                                            <i data-lucide="file-text" class="w-3 h-3 mr-2"></i>
                                            [[Resources]]
                                        </div>
                                        <i data-lucide="external-link" class="w-3 h-3 ml-2"></i>
                                    </a>
                                    <a href="" class="flex items-center justify-between text-blue-400 hover:text-blue-300 text-sm transition-colors">
                                        <div class="flex items-center space-x-2 flex-row">
                                            <i data-lucide="code-xml" class="w-3 h-3 mr-2"></i>
                                            [[Resources]]
                                        </div>
                                        <i data-lucide="external-link" class="w-3 h-3 ml-2"></i>
                                    </a>
                                </div>
                            </div>
                            
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
                                            <i data-lucide="github" class="w-5 h-5 mr-2"></i>
                                            URL du dépôt GitHub <span class="text-red-400">*</span>
                                        </label>
                                        <input type="url" id="githubUrl" placeholder="https://github.com/username/repo" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400">
                                        <p class="text-sm text-gray-400 mt-1">Lien vers le dépôt GitHub contenant votre code source.</p>
                                    </div>
                                </div>

                                <!-- URL de démonstration -->
                                <div>
                                    <label class="flex items-center text-sm font-medium mb-2">
                                        <i data-lucide="link" class="w-5 h-5 mr-2"></i>
                                        URL de démonstration
                                    </label>
                                    <input type="url" id="githubDemoUrl" placeholder="https://votre-demo.vercel.app" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400">
                                    <p class="text-sm text-gray-400 mt-1">Lien vers une démonstration en ligne de votre solution, si disponible.</p>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label class="block text-sm font-medium mb-2">
                                        <i data-lucide="file-text" class="inline w-5 h-5 mr-2"></i>
                                        Description de votre solution <span class="text-red-400">*</span>
                                    </label>
                                    <textarea id="githubDescription" rows="4" placeholder="Décrivez comment vous avez abordé et implémenté votre solution..." class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400 resize-vertical"></textarea>
                                </div>

                                <!-- Notes additionnelles -->
                                <div>
                                    <label class="block text-sm font-medium mb-2">
                                        <i data-lucide="file-text" class="inline w-5 h-5 mr-2"></i>
                                        Notes additionnelles
                                    </label>
                                    <textarea id="githubNotes" rows="3" placeholder="Instructions pour exécuter le projet, identifiants de test, défis rencontrés, etc." class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400 resize-vertical"></textarea>
                                </div>

                                <!-- Avant de soumettre -->
                                <div class="bg-blue-900/20 border border-blue-500/30 rounded-lg p-4">
                                    <div class="flex items-center mb-3">
                                        <i data-lucide="info" class="w-5 h-5 text-blue-400 mr-2"></i>
                                        <h4 class="font-medium text-blue-300">Avant de soumettre</h4>
                                    </div>
                                    <ul class="text-sm text-gray-300 space-y-2">
                                        <li class="flex items-start">
                                            <span class="text-blue-400 mr-2">•</span>
                                            <span id="checklistGithub">Assurez-vous que votre dépôt GitHub est public ou accessible à notre équipe.</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="text-blue-400 mr-2">•</span>
                                            <span>Vérifiez que vous avez inclus un README avec les instructions pour installer et exécuter votre projet.</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="text-blue-400 mr-2">•</span>
                                            <span id="checklistContent">Évitez d'inclure les dossiers node_modules ou autres dépendances volumineuses.</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="text-blue-400 mr-2">•</span>
                                            <span>Assurez-vous que votre code est propre, documenté et suit les bonnes pratiques.</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="text-blue-400 mr-2">•</span>
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
                                            <i data-lucide="folder-archive" class="w-5 h-5 mr-2"></i>
                                            Fichier ZIP de votre solution <span class="text-red-400">*</span>
                                        </label>
                                        <div class="border-2 border-dashed border-gray-600 rounded-lg p-8 text-center hover:border-gray-500 transition-colors">
                                            <div id="dropZone" class="flex items-center justify-center flex-col space-x-4">
                                                <input type="file" id="zipFile" accept=".zip" class="hidden">
                                                <i data-lucide="cloud-upload" class="w-12 h-12 mx-auto text-gray-400 mb-4"></i>
                                                <p class="text-gray-400 mb-2">Glissez-déposez votre fichier ZIP ici ou cliquez pour sélectionner</p>
                                                <button type="button" onclick="document.getElementById('zipFile').click()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                                    Sélectionner un fichier
                                                </button>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-400 mt-1">Fichier ZIP contenant votre code source (max 50MB). Incluez un README avec les instructions d'installation et d'exécution.</p>
                                    </div>
                                </div>

                                <!-- URL de démonstration -->
                                <div>
                                    <label class="flex items-center text-sm font-medium mb-2">
                                        <i data-lucide="link" class="w-5 h-5 mr-2"></i>
                                        URL de démonstration
                                    </label>
                                    <input type="url" id="zipDemoUrl" placeholder="https://votre-demo.vercel.app" class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400">
                                    <p class="text-sm text-gray-400 mt-1">Lien vers une démonstration en ligne de votre solution, si disponible.</p>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label class="block text-sm font-medium mb-2">
                                        <i data-lucide="file-text" class="inline w-5 h-5 mr-2"></i>
                                        Description de votre solution <span class="text-red-400">*</span>
                                    </label>
                                    <textarea id="zipDescription" rows="4" placeholder="Décrivez comment vous avez abordé et implémenté votre solution..." class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400 resize-vertical"></textarea>
                                </div>

                                <!-- Notes additionnelles -->
                                <div>
                                    <label class="block text-sm font-medium mb-2">
                                        <i data-lucide="file-text" class="inline w-5 h-5 mr-2"></i>
                                        Notes additionnelles
                                    </label>
                                    <textarea id="zipNotes" rows="3" placeholder="Instructions pour exécuter le projet, identifiants de test, défis rencontrés, etc." class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400 resize-vertical"></textarea>
                                </div>

                                <!-- Avant de soumettre -->
                                <div class="bg-blue-900/20 border border-blue-500/30 rounded-lg p-4">
                                    <div class="flex items-center mb-3">
                                        <i data-lucide="info" class="w-5 h-5 text-blue-400 mr-2"></i>
                                        <h4 class="font-medium text-blue-300">Avant de soumettre</h4>
                                    </div>
                                    <ul class="text-sm text-gray-300 space-y-2">
                                        <li class="flex items-start">
                                            <span class="text-blue-400 mr-2">•</span>
                                            <span id="checklistGithub">Assurez-vous que votre fichier ZIP contient tous les fichiers nécessaires.</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="text-blue-400 mr-2">•</span>
                                            <span>Incluez un fichier README.md avec les instructions d'installation et d'exécution.</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="text-blue-400 mr-2">•</span>
                                            <span id="checklistContent">Évitez d'inclure les dossiers node_modules ou autres dépendances volumineuses.</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="text-blue-400 mr-2">•</span>
                                            <span>Assurez-vous que votre code est propre, documenté et suit les bonnes pratiques.</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="text-blue-400 mr-2">•</span>
                                            <span>Testez votre application pour vous assurer qu'elle fonctionne correctement.</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="text-blue-400 mr-2">•</span>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>