<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="/css/dist/output.css">
    <link rel="stylesheet" href="/css/styles/footer.css">
</head>
<body class="min-h-screen bg-gray-50">
    <!-- Footer amélioré -->
    <footer class="footer-gradient border-t border-blue-100">
        <div class="max-w-7xl mx-auto px-6 py-12">
            <!-- Contenu principal du footer -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Section À propos -->
                <div class="footer-section">
                    <h3 class="footer-title">
                        <i data-lucide="info" class="footer-icon"></i>
                        À propos
                    </h3>
                    <ul class="footer-links">
                        <li><a href="#stats" class="footer-link">Notre mission</a></li>
                        <li><a href="#" class="footer-link">L'équipe</a></li>
                        <li><a href="/contact" class="footer-link">Contact</a></li>
                    </ul>
                </div>

                <!-- Section Ressources -->
                <div class="footer-section">
                    <h3 class="footer-title">
                        <i data-lucide="book-open" class="footer-icon"></i>
                        Ressources
                    </h3>
                    <ul class="footer-links">
                        <li><a href="/hackathon" class="footer-link">Hackathons</a></li>
                        <li><a href="/resources" class="footer-link">Documentation</a></li>
                    </ul>
                </div>

                <!-- Section Légal -->
                <div class="footer-section">
                    <h3 class="footer-title">
                        <i data-lucide="shield-check" class="footer-icon"></i>
                        Légal
                    </h3>
                    <ul class="footer-links">
                        <li><a href="/terms" class="footer-link">Conditions d'utilisation</a></li>
                        <li><a href="/privacy" class="footer-link">Politique de confidentialité</a></li>
                    </ul>
                </div>

                <!-- Section Réseaux sociaux -->
                <div class="footer-section">
                    <h3 class="footer-title">
                        <i data-lucide="share-2" class="footer-icon"></i>
                        Suivez-nous
                    </h3>
                    <div class="social-container">
                        <a href="#" class="social-link" aria-label="Instagram">
                            <i data-lucide="instagram"></i>
                        </a>
                        <a href="https://discord.gg/FbztK5Uagd" target="_blank" class="social-link" aria-label="Discord">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.3 12.3 0 0 1-1.873.892.077.077 0 0 0-.04.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.84 19.84 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.158-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.158 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.955 2.418-2.157 2.418z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Séparateur -->
            <div class="border-t border-blue-700/30 my-8"></div>

            <!-- Footer bottom -->
            <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-blue-400 rounded-lg flex items-center justify-center">
                        <i data-lucide="code-2" class="w-5 h-5 text-blue-900"></i>
                    </div>
                    <span class="text-blue-200 font-semibold">Hack & Stack</span>
                </div>
                
                <div class="text-center text-blue-300 text-sm">
                    © 2025 Hack & Stack. Tous droits réservés.
                </div>
                
                <div class="flex items-center space-x-4 text-blue-300 text-sm">
                    <span class="flex items-center space-x-1">
                        <i data-lucide="heart" class="w-4 h-4 text-red-400"></i>
                        <span>Fait avec passion</span>
                    </span>
                    <span class="flex items-center space-x-1">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                        <span>Cotonou, Bénin</span>
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <script src="/js/footer.js"></script>
</body>
</html>