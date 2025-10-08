<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hack & Stack - Ressources</title>
    <link rel="stylesheet" href="/css/styles/ressources.css">
    <?php require_once "../includes/head.php"; ?>
    <script src="/js/ressources.js"></script>
</head>

<body class="min-h-screen text-white">
    <!-- Navigation Header -->
    <?php require_once '../includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="relative py-16 sm:py-20 lg:py-24 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-600/20 via-blue-700/20 to-blue-800/20 opacity-50"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="floating-animation inline-block mb-6">
                <div class="relative">
                    <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto">
                        <i data-lucide="book-open" class="w-10 h-10 text-white"></i>
                    </div>
                    <div class="absolute -top-1 -left-1 w-22 h-22 border-2 border-blue-400/50 rounded-2xl pulse-ring"></div>
                </div>
            </div>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold mb-6 text-gradient leading-tight">
                Centre de Ressources
            </h1>
            <p class="text-lg sm:text-xl text-slate-300 max-w-3xl mx-auto mb-8 leading-relaxed">
                Tout ce dont vous avez besoin pour exceller dans les hackathons de développement et de cybersécurité.
                Des guides aux outils avancés.
            </p>
        </div>
    </section>


    <!-- Resources Grid -->
    <main class="py-12 sm:py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Development Resources -->
            <section class="mb-16">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                        <i data-lucide="code" class="w-4 h-4 text-white"></i>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-white">Développement</h2>
                    <div class="flex-1 h-px bg-gradient-to-r from-blue-500/50 to-transparent"></div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Frontend Development -->
                    <div class="card-gradient rounded-xl p-6 hover-glow transition-all duration-300 group">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-blue-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i data-lucide="palette" class="w-5 h-5 text-white"></i>
                            </div>
                            <h3 class="text-lg sm:text-xl font-semibold text-white">Frontend</h3>
                        </div>
                        <p class="text-sm text-slate-300 mb-6 leading-relaxed">
                            Interfaces utilisateur modernes et réactives
                        </p>
                        <div class="space-y-3 mb-6">
                            <a href="https://react.dev/learn" target="_blank" class="flex items-center space-x-2 text-sm text-slate-300 hover:text-blue-400 transition-colors">
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                <span>React & Hooks</span>
                            </a>
                            <a href="https://nextjs.org/docs" target="_blank" class="flex items-center space-x-2 text-sm text-slate-300 hover:text-blue-400 transition-colors">
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                <span>Next.js Framework</span>
                            </a>
                            <a href="https://tailwindcss.com/docs" target="_blank" class="flex items-center space-x-2 text-sm text-slate-300 hover:text-blue-400 transition-colors">
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                <span>Tailwind CSS</span>
                            </a>
                        </div>
                        <button onclick="window.open('https://roadmap.sh/frontend', '_blank')" class="w-full py-2 px-4 bg-gradient-to-r from-blue-500/20 to-blue-600/20 border border-blue-500/30 rounded-lg text-sm font-medium text-white hover:from-blue-500/30 hover:to-blue-600/30 transition-all">
                            Explorer Frontend
                        </button>
                    </div>

                    <!-- Backend Development -->
                    <div class="card-gradient rounded-xl p-6 hover-glow transition-all duration-300 group">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i data-lucide="server" class="w-5 h-5 text-white"></i>
                            </div>
                            <h3 class="text-lg sm:text-xl font-semibold text-white">Backend</h3>
                        </div>
                        <p class="text-sm text-slate-300 mb-6 leading-relaxed">
                            APIs robustes et architecture scalable
                        </p>
                        <div class="space-y-3 mb-6">
                            <a href="https://nodejs.org/docs" target="_blank" class="flex items-center space-x-2 text-sm text-slate-300 hover:text-blue-400 transition-colors">
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                <span>Node.js & Express</span>
                            </a>
                            <a href="https://fastapi.tiangolo.com" target="_blank" class="flex items-center space-x-2 text-sm text-slate-300 hover:text-blue-400 transition-colors">
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                <span>Python FastAPI</span>
                            </a>
                            <a href="https://docs.docker.com/" target="_blank" class="flex items-center space-x-2 text-sm text-slate-300 hover:text-blue-400 transition-colors">
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                <span>Docker & Containers</span>
                            </a>
                        </div>
                        <button onclick="window.open('https://roadmap.sh/backend', '_blank')" class="w-full py-2 px-4 bg-gradient-to-r from-blue-600/20 to-blue-700/20 border border-blue-600/30 rounded-lg text-sm font-medium text-white hover:from-blue-600/30 hover:to-blue-700/30 transition-all">
                            Explorer Backend
                        </button>
                    </div>

                    <!-- Database -->
                    <div class="card-gradient rounded-xl p-6 hover-glow transition-all duration-300 group">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i data-lucide="database" class="w-5 h-5 text-white"></i>
                            </div>
                            <h3 class="text-lg sm:text-xl font-semibold text-white">Base de données</h3>
                        </div>
                        <p class="text-sm text-slate-300 mb-6 leading-relaxed">
                            Gestion et optimisation des données
                        </p>
                        <div class="space-y-3 mb-6">
                            <a href="https://www.postgresql.org/docs/" target="_blank" class="flex items-center space-x-2 text-sm text-slate-300 hover:text-blue-400 transition-colors">
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                <span>PostgreSQL</span>
                            </a>
                            <a href="https://www.mongodb.com/docs/" target="_blank" class="flex items-center space-x-2 text-sm text-slate-300 hover:text-blue-400 transition-colors">
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                <span>MongoDB</span>
                            </a>
                            <a href="https://redis.io/documentation" target="_blank" class="flex items-center space-x-2 text-sm text-slate-300 hover:text-blue-400 transition-colors">
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                <span>Redis Cache</span>
                            </a>
                        </div>
                        <button onclick="window.open('https://db-engines.com/', '_blank')" class="w-full py-2 px-4 bg-gradient-to-r from-blue-700/20 to-blue-800/20 border border-blue-700/30 rounded-lg text-sm font-medium text-white hover:from-blue-700/30 hover:to-blue-800/30 transition-all">
                            Explorer Base de données
                        </button>
                    </div>
                </div>
            </section>

            <!-- Security Resources -->
            <section class="mb-16">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                        <i data-lucide="shield" class="w-4 h-4 text-white"></i>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-white">Cybersécurité</h2>
                    <div class="flex-1 h-px bg-gradient-to-r from-blue-500/50 to-transparent"></div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Penetration Testing -->
                    <div class="card-gradient rounded-xl p-6 hover-glow transition-all duration-300 group">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-gradient-to-r from-cyan-500 to-cyan-600 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i data-lucide="bug" class="w-5 h-5 text-white"></i>
                            </div>
                            <h3 class="text-lg sm:text-xl font-semibold text-white">Pentest</h3>
                        </div>
                        <p class="text-sm text-slate-300 mb-6 leading-relaxed">
                            Tests d'intrusion et évaluation de sécurité
                        </p>
                        <div class="space-y-3 mb-6">
                            <a href="https://portswigger.net/web-security" target="_blank" class="flex items-center space-x-2 text-sm text-slate-300 hover:text-cyan-400 transition-colors">
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                <span>Web Security</span>
                            </a>
                            <a href="https://www.offensive-security.com/metasploit-unleashed/" target="_blank" class="flex items-center space-x-2 text-sm text-slate-300 hover:text-cyan-400 transition-colors">
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                <span>Metasploit</span>
                            </a>
                            <a href="https://portswigger.net/web-security" target="_blank" class="flex items-center space-x-2 text-sm text-slate-300 hover:text-cyan-400 transition-colors">
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                <span>Web Security Academy</span>
                            </a>
                        </div>
                        <button onclick="window.open('https://www.hackthebox.com/', '_blank')" class="w-full py-2 px-4 bg-gradient-to-r from-cyan-600/20 to-cyan-700/20 border border-cyan-600/30 rounded-lg text-sm font-medium text-white hover:from-cyan-600/30 hover:to-cyan-700/30 transition-all">
                            Explorer Pentest
                        </button>
                    </div>

                    <!-- Cryptography -->
                    <div class="card-gradient rounded-xl p-6 hover-glow transition-all duration-300 group">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-gradient-to-r from-cyan-400 to-cyan-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i data-lucide="key" class="w-5 h-5 text-white"></i>
                            </div>
                            <h3 class="text-lg sm:text-xl font-semibold text-white">Cryptographie</h3>
                        </div>
                        <p class="text-sm text-slate-300 mb-6 leading-relaxed">
                            Chiffrement et sécurité des données
                        </p>
                        <div class="space-y-3 mb-6">
                            <a href="https://cryptopals.com/" target="_blank" class="flex items-center space-x-2 text-sm text-slate-300 hover:text-cyan-400 transition-colors">
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                <span>Crypto Challenges</span>
                            </a>
                            <a href="https://www.coursera.org/learn/crypto" target="_blank" class="flex items-center space-x-2 text-sm text-slate-300 hover:text-cyan-400 transition-colors">
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                <span>Crypto Course</span>
                            </a>
                        </div>
                        <button onclick="window.open('https://tryhackme.com/paths', '_blank')" class="w-full py-2 px-4 bg-gradient-to-r from-cyan-500/20 to-cyan-600/20 border border-cyan-500/30 rounded-lg text-sm font-medium text-white hover:from-cyan-500/30 hover:to-cyan-600/30 transition-all">
                            Explorer Cybersécurité
                        </button>
                    </div>

                    <!-- Network Security -->
                    <div class="card-gradient rounded-xl p-6 hover-glow transition-all duration-300 group">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-gradient-to-r from-cyan-600 to-cyan-700 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i data-lucide="wifi" class="w-5 h-5 text-white"></i>
                            </div>
                            <h3 class="text-lg sm:text-xl font-semibold text-white">Sécurité Réseau</h3>
                        </div>
                        <p class="text-sm text-slate-300 mb-6 leading-relaxed">
                            Protection des infrastructures réseau
                        </p>
                        <div class="space-y-3 mb-6">
                            <a href="https://nmap.org/book/toc.html" target="_blank" class="flex items-center space-x-2 text-sm text-slate-300 hover:text-cyan-400 transition-colors">
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                <span>Nmap Guide</span>
                            </a>
                            <a href="https://www.wireshark.org/docs/" target="_blank" class="flex items-center space-x-2 text-sm text-slate-300 hover:text-cyan-400 transition-colors">
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                <span>Wireshark Docs</span>
                            </a>
                            <a href="https://github.com/danielmiessler/SecLists" target="_blank" class="flex items-center space-x-2 text-sm text-slate-300 hover:text-cyan-400 transition-colors">
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                <span>SecLists</span>
                            </a>
                        </div>
                        <button onclick="window.open('https://owasp.org/www-project-top-ten/', '_blank')" class="w-full py-2 px-4 bg-gradient-to-r from-cyan-700/20 to-cyan-800/20 border border-cyan-700/30 rounded-lg text-sm font-medium text-white hover:from-cyan-700/30 hover:to-cyan-800/30 transition-all">
                            Explorer Réseau
                        </button>
                    </div>
                </div>
            </section>

            <!-- Tools & APIs -->
            <section class="mb-16">
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                        <i data-lucide="tool-case" class="w-4 h-4 text-white"></i>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-white">Outils & APIs</h2>
                    <div class="flex-1 h-px bg-gradient-to-r from-blue-500/50 to-transparent"></div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Popular APIs -->
                    <div class="card-gradient rounded-xl p-6">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg flex items-center justify-center">
                                <i data-lucide="cpu" class="w-5 h-5 text-white"></i>
                            </div>
                            <h3 class="text-xl font-bold text-white">APIs Populaires</h3>
                        </div>
                        <div class="space-y-4">
                            <a href="https://www.postman.com/api-documentation-tool/" target="_blank" class="block">
                                <div class="flex items-center p-3 rounded-lg bg-slate-800/50 hover:bg-slate-800/70 transition-colors">
                                    <div class="w-8 h-8 bg-indigo-500/20 rounded-lg flex items-center justify-center">
                                        <i data-lucide="cloud" class="w-4 h-4 text-indigo-400"></i>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <h4 class="text-sm font-medium text-white">REST API</h4>
                                        <p class="text-xs text-slate-400">Architecture d'API moderne</p>
                                    </div>
                                    <i data-lucide="external-link" class="w-4 h-4 text-slate-500"></i>
                                </div>
                            </a>
                            <a href="https://graphql.org/" target="_blank" class="block">
                                <div class="flex items-center p-3 rounded-lg bg-slate-800/50 hover:bg-slate-800/70 transition-colors">
                                    <div class="w-8 h-8 bg-indigo-500/20 rounded-lg flex items-center justify-center">
                                        <i data-lucide="zap" class="w-4 h-4 text-indigo-400"></i>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <h4 class="text-sm font-medium text-white">GraphQL</h4>
                                        <p class="text-xs text-slate-400">Query language pour APIs</p>
                                    </div>
                                    <i data-lucide="external-link" class="w-4 h-4 text-slate-500"></i>
                                </div>
                            </a>
                            <a href="https://auth0.com/" target="_blank" class="block">
                                <div class="flex items-center p-3 rounded-lg bg-slate-800/50 hover:bg-slate-800/70 transition-colors">
                                    <div class="w-8 h-8 bg-indigo-500/20 rounded-lg flex items-center justify-center">
                                        <i data-lucide="shield" class="w-4 h-4 text-indigo-400"></i>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <h4 class="text-sm font-medium text-white">Auth0</h4>
                                        <p class="text-xs text-slate-400">Authentification sécurisée</p>
                                    </div>
                                    <i data-lucide="external-link" class="w-4 h-4 text-slate-500"></i>
                                </div>
                            </a>
                        </div>
                        <a href="https://rapidapi.com/hub" target="_blank" class="mt-6 inline-flex items-center text-blue-400 hover:text-blue-300 text-sm">
                            Découvrir plus d'APIs
                            <i data-lucide="external-link" class="w-3 h-3 ml-1"></i>
                        </a>
                    </div>

                    <!-- Development Tools -->
                    <div class="card-gradient rounded-xl p-6">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-lg flex items-center justify-center">
                                <i data-lucide="wrench" class="w-5 h-5 text-white"></i>
                            </div>
                            <h3 class="text-xl font-bold text-white">Outils Développement</h3>
                        </div>
                        <div class="space-y-4">
                            <a href="https://code.visualstudio.com/" target="_blank" class="block">
                                <div class="flex items-center p-3 rounded-lg bg-slate-800/50 hover:bg-slate-800/70 transition-colors">
                                    <div class="w-8 h-8 bg-indigo-600/20 rounded-lg flex items-center justify-center">
                                        <i data-lucide="code" class="w-4 h-4 text-indigo-400"></i>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <h4 class="text-sm font-medium text-white">VS Code</h4>
                                        <p class="text-xs text-slate-400">Éditeur de code</p>
                                    </div>
                                    <i data-lucide="external-link" class="w-4 h-4 text-slate-500"></i>
                                </div>
                            </a>
                            <a href="https://git-scm.com/" target="_blank" class="block">
                                <div class="flex items-center p-3 rounded-lg bg-slate-800/50 hover:bg-slate-800/70 transition-colors">
                                    <div class="w-8 h-8 bg-indigo-600/20 rounded-lg flex items-center justify-center">
                                        <i data-lucide="git-branch" class="w-4 h-4 text-indigo-400"></i>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <h4 class="text-sm font-medium text-white">Git & GitHub</h4>
                                        <p class="text-xs text-slate-400">Contrôle de version</p>
                                    </div>
                                    <i data-lucide="external-link" class="w-4 h-4 text-slate-500"></i>
                                </div>
                            </a>
                            <a href="https://www.docker.com/" target="_blank" class="block">
                                <div class="flex items-center p-3 rounded-lg bg-slate-800/50 hover:bg-slate-800/70 transition-colors">
                                    <div class="w-8 h-8 bg-indigo-600/20 rounded-lg flex items-center justify-center">
                                        <i data-lucide="package" class="w-4 h-4 text-indigo-400"></i>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <h4 class="text-sm font-medium text-white">Docker</h4>
                                        <p class="text-xs text-slate-400">Conteneurisation</p>
                                    </div>
                                    <i data-lucide="external-link" class="w-4 h-4 text-slate-500"></i>
                                </div>
                            </a>
                        </div>
                        <a href="https://github.com/topics/developer-tools" target="_blank" class="mt-6 inline-flex items-center text-blue-400 hover:text-blue-300 text-sm">
                            Découvrir plus d'outils
                            <i data-lucide="external-link" class="w-3 h-3 ml-1"></i>
                        </a>
                    </div>
                </div>
            </section>

            <!-- Quick Actions -->
            <section class="text-center">
                <div class="card-gradient rounded-2xl p-8 sm:p-12">
                    <div class="max-w-3xl mx-auto">
                        <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                            <i data-lucide="rocket" class="w-8 h-8 text-white"></i>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">Prêt à commencer ?</h2>
                        <p class="text-lg text-slate-300 mb-8">
                            Rejoignez notre prochain hackathon et mettez en pratique vos nouvelles compétences
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <button class="px-8 py-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg text-white font-medium hover:from-blue-600 hover:to-blue-700 transition-all transform hover:scale-105" onclick="window.location.href='/hackathons'">
                                Voir les Hackathons
                            </button>
                            <button class="px-8 py-3 border border-white/20 rounded-lg text-white font-medium hover:bg-white/10 transition-all" onclick="window.location.href='/auth'">
                                Commencer l'aventure
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <?php require_once '../includes/footer.php'; ?>

</body>

</html>