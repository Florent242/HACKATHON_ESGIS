<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Challenge Yourself</title>
    <link rel="stylesheet" href="/css/styles/home.css">
    <?php require_once "../includes/head.php"; ?>
    <script defer src="/js/home.js"></script>
</head>

<body class="bg-slate-950 text-white">
    <?php require_once '../includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero relative min-h-screen flex items-center justify-center overflow-hidden">
        <!-- Animated background elements -->
        <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-blue-950/30 to-slate-950"></div>
        <div class="absolute inset-0">
            <div class="grid-background"></div>
            <div class="floating-orbs">
                <div class="orb orb-1"></div>
                <div class="orb orb-2"></div>
                <div class="orb orb-3"></div>
            </div>
        </div>

        <div class="container relative z-10 max-w-6xl mx-auto px-6 text-center">
            <div class="hero-badge inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-300 text-sm mb-8 fade-in">
                <i data-lucide="zap" class="w-4 h-4"></i>
                Plateforme de hackathons, par ESGIS
            </div>

            <h1 class="hero-title text-5xl md:text-7xl font-black mb-6 fade-in">
                Challengez-vous avec
                <span class="highlight bg-gradient-to-r from-blue-400 to-cyan-300 bg-clip-text text-transparent">EsgisHub</span>
            </h1>

            <p class="hero-subtitle text-xl md:text-2xl text-slate-400 max-w-4xl mx-auto mb-12 leading-relaxed fade-in">
                Rejoignez notre communauté élite de développeurs et d'experts en cybersécurité.
                Participez à des hackathons exclusifs, maîtrisez les dernières technologies et
                relevez des défis de sécurité.
            </p>

            <div class="hero-buttons flex flex-col sm:flex-row gap-4 justify-center mb-16">
                <button class="btn-primary group bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 px-8 py-4 rounded-lg font-semibold transition-all duration-300 transform hover:scale-105 hover:shadow-xl hover:shadow-blue-500/25 fade-in">
                    Commencer votre voyage
                    <i data-lucide="arrow-right" class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform"></i>
                </button>
                <button class="btn-secondary border-2 border-slate-700 hover:border-slate-600 bg-slate-900/50 hover:bg-slate-800/50 px-8 py-4 rounded-lg font-semibold transition-all duration-300 backdrop-blur-sm fade-in">
                    Découvrir les hackathons
                </button>
            </div>

            <!-- Scroll indicator -->
            <div class="scroll-indicator animate-bounce">
                <i data-lucide="chevron-down" class="w-6 h-6 text-slate-500"></i>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section id="stats" class="stats-section py-20 relative">
        <div class="container max-w-6xl mx-auto px-6">
            <div class="stats-container grid grid-cols-2 lg:grid-cols-4 gap-8 bg-gradient-to-br from-slate-900/80 to-slate-800/50 backdrop-blur-sm border border-slate-700/50 rounded-2xl p-12">
                <div class="stat-item text-center group">
                    <div class="stat-icon bg-blue-500/10 p-4 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-500/20 transition-colors">
                        <i data-lucide="users" class="w-8 h-8 text-blue-400"></i>
                    </div>
                    <h2 class="text-4xl font-bold mb-2">
                        <span class="counter" data-target="200">0</span>+
                    </h2>
                    <p class="text-slate-400">Membres attendus</p>
                </div>
                <div class="stat-item text-center group">
                    <div class="stat-icon bg-blue-500/10 p-4 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-500/20 transition-colors">
                        <i data-lucide="calendar" class="w-8 h-8 text-blue-400"></i>
                    </div>
                    <h2 class="text-4xl font-bold mb-2">
                        <span class="counter" data-target="50">0</span>+
                    </h2>
                    <p class="text-slate-400">Défis à venir</p>
                </div>
                <div class="stat-item text-center group">
                    <div class="stat-icon bg-blue-500/10 p-4 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-500/20 transition-colors">
                        <i data-lucide="trophy" class="w-8 h-8 text-blue-400"></i>
                    </div>
                    <h2 class="text-4xl font-bold mb-2">
                        $<span class="counter" data-target="2">0</span>K
                    </h2>
                    <p class="text-slate-400">En jeu</p>
                </div>
                <div class="stat-item text-center group">
                    <div class="stat-icon bg-blue-500/10 p-4 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-500/20 transition-colors">
                        <i data-lucide="swords" class="w-8 h-8 text-blue-400"></i>
                    </div>
                    <h2 class="text-4xl font-bold mb-2">
                        <span class="counter" data-target="2">0</span>
                    </h2>
                    <p class="text-slate-400">Types de challenge</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Arguments Section -->
    <section class="arguments-section py-24 relative">
        <div class="container max-w-6xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-6">
                    Pourquoi Choisir
                    <span class="bg-gradient-to-r from-blue-400 to-cyan-300 bg-clip-text text-transparent">EsgisHub</span> ?
                </h2>
                <p class="text-xl text-slate-400 max-w-2xl mx-auto">
                    Découvrez les avantages qui font de notre plateforme le choix des professionnels
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="argument-card group bg-gradient-to-br from-slate-900/50 to-slate-800/30 backdrop-blur-sm border border-slate-700/50 rounded-xl p-8 hover:border-blue-500/50 transition-all duration-500 hover:-translate-y-2 fade-in">
                    <div class="icon-container bg-blue-500/10 p-4 rounded-lg w-16 h-16 flex items-center justify-center mb-6 group-hover:bg-blue-500/20 transition-colors">
                        <i data-lucide="code" class="w-8 h-8 text-blue-400"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Défis de Développement</h3>
                    <p class="text-slate-400 leading-relaxed">
                        Participez à des défis de programmation stimulants qui repoussent vos limites techniques
                    </p>
                </div>

                <div class="argument-card group bg-gradient-to-br from-slate-900/50 to-slate-800/30 backdrop-blur-sm border border-slate-700/50 rounded-xl p-8 hover:border-blue-500/50 transition-all duration-500 hover:-translate-y-2 fade-in">
                    <div class="icon-container bg-blue-500/10 p-4 rounded-lg w-16 h-16 flex items-center justify-center mb-6 group-hover:bg-blue-500/20 transition-colors">
                        <i data-lucide="shield" class="w-8 h-8 text-blue-400"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Défis de Sécurité</h3>
                    <p class="text-slate-400 leading-relaxed">
                        Maîtrisez la cybersécurité avec des scénarios réalistes et des challenges de haut niveau
                    </p>
                </div>

                <div class="argument-card group bg-gradient-to-br from-slate-900/50 to-slate-800/30 backdrop-blur-sm border border-slate-700/50 rounded-xl p-8 hover:border-blue-500/50 transition-all duration-500 hover:-translate-y-2 fade-in">
                    <div class="icon-container bg-blue-500/10 p-4 rounded-lg w-16 h-16 flex items-center justify-center mb-6 group-hover:bg-blue-500/20 transition-colors">
                        <i data-lucide="users" class="w-8 h-8 text-blue-400"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Communauté Elite</h3>
                    <p class="text-slate-400 leading-relaxed">
                        Rejoignez un réseau exclusif de développeurs et d'experts
                    </p>
                </div>

                <div class="argument-card group bg-gradient-to-br from-slate-900/50 to-slate-800/30 backdrop-blur-sm border border-slate-700/50 rounded-xl p-8 hover:border-blue-500/50 transition-all duration-500 hover:-translate-y-2 fade-in">
                    <div class="icon-container bg-blue-500/10 p-4 rounded-lg w-16 h-16 flex items-center justify-center mb-6 group-hover:bg-blue-500/20 transition-colors">
                        <i data-lucide="trophy" class="w-8 h-8 text-blue-400"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Compétitions Premium</h3>
                    <p class="text-slate-400 leading-relaxed">
                        Participez à des hackathons avec des prix exceptionnels
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Events Section -->
    <section class="events-section py-24 relative bg-gradient-to-b from-transparent to-slate-900/50">
        <div class="container max-w-6xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-6">Événements à Venir</h2>
                <p class="text-xl text-slate-400">Ne manquez pas nos prochains hackathons exclusifs</p>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                <div class="event-card bg-gradient-to-br from-slate-900/80 to-slate-800/50 backdrop-blur-sm border border-slate-700/50 rounded-2xl p-8 hover:border-blue-500/50 transition-all duration-500 hover:-translate-y-2 fade-in-left">
                    <div class="flex items-start justify-between mb-6">
                        <div class="event-badge bg-blue-500/10 text-blue-300 px-3 py-1 rounded-full text-sm font-medium">
                            Hackathon Dev
                        </div>
                    </div>

                    <h3 class="text-2xl font-bold mb-4">ESGIS Hackathon 2024</h3>
                    <p class="text-slate-400 mb-6">L'un des plus grands événements de développement de l'année avec des défis innovants</p>

                    <div class="event-details space-y-3 mb-8">
                        <div class="flex items-center gap-3 text-slate-300">
                            <i data-lucide="calendar" class="w-5 h-5 text-blue-400"></i>
                            <span>Arrive bientôt</span>
                        </div>
                        <div class="flex items-center gap-3 text-slate-300">
                            <i data-lucide="users" class="w-5 h-5 text-blue-400"></i>
                            <span>100+ participants attendus</span>
                        </div>
                        <div class="flex items-center gap-3 text-slate-300">
                            <i data-lucide="clock" class="w-5 h-5 text-blue-400"></i>
                            <span>Une période de développement intense</span>
                        </div>
                    </div>

                    <button class="btn-event w-full bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 py-3 rounded-lg font-semibold transition-all duration-300 hover:shadow-lg">
                        En savoir plus
                        <i data-lucide="arrow-right" class="w-5 h-5 ml-2 inline"></i>
                    </button>
                </div>

                <div class="event-card bg-gradient-to-br from-slate-900/80 to-slate-800/50 backdrop-blur-sm border border-slate-700/50 rounded-2xl p-8 hover:border-emerald-500/50 transition-all duration-500 hover:-translate-y-2 fade-in-right">
                    <div class="flex items-start justify-between mb-6">
                        <div class="event-badge bg-emerald-500/10 text-emerald-300 px-3 py-1 rounded-full text-sm font-medium">
                            Cyber Sécurité
                        </div>
                    </div>

                    <h3 class="text-2xl font-bold mb-4">Challenges de Sécurité</h3>
                    <p class="text-slate-400 mb-6">Démontrez vos compétences en sécurité avec des scénarios réalistes</p>

                    <div class="event-details space-y-3 mb-8">
                        <div class="flex items-center gap-3 text-slate-300">
                            <i data-lucide="calendar" class="w-5 h-5 text-emerald-400"></i>
                            <span>Arrive bientôt</span>
                        </div>
                        <div class="flex items-center gap-3 text-slate-300">
                            <i data-lucide="users" class="w-5 h-5 text-emerald-400"></i>
                            <span>100+ experts attendus</span>
                        </div>
                        <div class="flex items-center gap-3 text-slate-300">
                            <i data-lucide="shield" class="w-5 h-5 text-emerald-400"></i>
                            <span>Challenges CTF</span>
                        </div>
                    </div>

                    <button class="btn-event w-full bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 hover:to-emerald-600 py-3 rounded-lg font-semibold transition-all duration-300 hover:shadow-lg">
                        En savoir plus
                        <i data-lucide="arrow-right" class="w-5 h-5 ml-2 inline"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Partenaires Simplifiée -->
    <section class="py-16 bg-slate-900/50">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl font-bold mb-6 bg-gradient-to-r from-blue-400 to-cyan-300 bg-clip-text text-transparent">
                    Devenez Partenaire
                </h2>
                <p class="text-slate-300 mb-8 text-lg">
                    Rejoignez notre communauté d'entreprises innovantes et bénéficiez d'une visibilité privilégiée 
                    auprès des talents de demain.
                </p>
                
                <div class="grid md:grid-cols-3 gap-6 mb-10">
                    <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700/50 hover:border-blue-500/30 transition-colors">
                        <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center mb-4 mx-auto">
                            <i data-lucide="users" class="w-6 h-6 text-blue-400"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">Visibilité</h3>
                        <p class="text-slate-400">Mettez en avant votre marque auprès de notre communauté</p>
                    </div>
                    
                    <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700/50 hover:border-blue-500/30 transition-colors">
                        <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center mb-4 mx-auto">
                            <i data-lucide="lightbulb" class="w-6 h-6 text-blue-400"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">Innovation</h3>
                        <p class="text-slate-400">Participez à des projets innovants avec nos étudiants</p>
                    </div>
                    
                    <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700/50 hover:border-blue-500/30 transition-colors">
                        <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center mb-4 mx-auto">
                            <i data-lucide="briefcase" class="w-6 h-6 text-blue-400"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">Recrutement</h3>
                        <p class="text-slate-400">Accédez à des profils talentueux et motivés</p>
                    </div>
                </div>
                
                <a href="https://discord.gg/FbztK5Uagd" class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 rounded-lg font-semibold text-white transition-all duration-300 transform hover:scale-105">
                    Nous contacter
                    <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section py-24 relative">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-950/50 to-slate-950/50"></div>
        <div class="container max-w-4xl mx-auto px-6 text-center relative z-10">
            <h2 class="text-4xl md:text-6xl font-bold mb-6">
                Prêt à relever le
                <span class="bg-gradient-to-r from-blue-400 to-cyan-300 bg-clip-text text-transparent">défi</span> ?
            </h2>
            <p class="text-xl text-slate-400 mb-12 max-w-2xl mx-auto">
                Rejoignez des milliers de développeurs qui font déjà partie de l'élite technologique
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button class="cta-primary bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 px-12 py-4 rounded-lg font-bold text-lg transition-all duration-300 transform hover:scale-105 hover:shadow-xl hover:shadow-blue-500/25">
                    Commencer maintenant
                    <i data-lucide="rocket" class="w-6 h-6 ml-2 inline"></i>
                </button>
                <button class="cta-secondary border-2 border-slate-600 hover:border-slate-500 bg-slate-900/50 hover:bg-slate-800/50 px-12 py-4 rounded-lg font-semibold text-lg transition-all duration-300 backdrop-blur-sm">
                    Voir les hackathons
                </button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php require_once '../includes/footer.php'; ?>
</body>

</html>