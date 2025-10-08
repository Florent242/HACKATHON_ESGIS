<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hack & Stack - Foire aux Questions</title>
    <!-- Preload critical CSS -->
    <link rel="stylesheet" href="/css/styles/user/faq.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php require_once '../includes/user/head.php'; ?>
    <script defer src="/js/user/faq.js"></script>
    <meta name="description" content="Trouvez les réponses à vos questions sur les hackathons et la plateforme Hack & Stack.">
</head>

<body class="bg-gray-900 text-gray-100">
    <?php require_once '../includes/user/header.php'; ?>

    <main>
        <div class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <span class="inline-block px-3 py-1 text-sm font-semibold text-blue-400 bg-blue-900/30 rounded-full mb-4">FAQ</span>
                <h1 class="text-4xl md:text-5xl font-bold mb-4">Questions Fréquemment Posées</h1>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto">Trouvez des réponses aux questions les plus courantes sur nos hackathons et notre plateforme.</p>
            </div>

            <div class="max-w-3xl mx-auto">
                <div id="accordion-collapse" data-accordion="collapse" class="space-y-4">
                    <div class="accordion">
                        <div class="accordion-item" data-aos="fade-up">
                            <div class="accordion-header">
                                <h3>Comment participer aux hackathons ?</h3>
                                <i data-lucide="chevron-down" class="accordion-icon"></i>
                            </div>
                            <div class="accordion-content">
                                <p>Pour participer, il suffit de créer un compte sur notre plateforme, former ou rejoindre une équipe, et s'inscrire aux événements qui vous intéressent. Notre équipe est là pour vous guider à chaque étape du processus.</p>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="50">
                            <div class="accordion-header">
                                <h3>Quel est le processus de sélection des équipes pour se qualifier ?</h3>
                                <i data-lucide="chevron-down" class="accordion-icon"></i>
                            </div>
                            <div class="accordion-content">
                                <p>Les équipes sont sélectionnées sur la base de plusieurs critères : la pertinence du projet proposé, la diversité des compétences au sein de l'équipe, et la motivation des participants. Nous encourageons particulièrement les équipes pluridisciplinaires qui combinent différentes expertises pour des projets innovants.</p>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="50">
                            <div class="accordion-header">
                                <h3> Combien de hackathons avez-vous ?</h3>
                                <i data-lucide="chevron-down" class="accordion-icon"></i>
                            </div>
                            <div class="accordion-content">
                                <p>Nous organisons actuellement 2 hackathons, un côté dev et un autre côté ctf</p>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="50">
                            <div class="accordion-header">
                                <h3>Comment se déroulera les hackathons ?</h3>
                                <i data-lucide="chevron-down" class="accordion-icon"></i>
                            </div>
                            <div class="accordion-content">
                                <p>Les hackathons se dérouleront en ligne sur notre plateforme. 
                                L'hackathon du ctf se déroulera en 2 phases : une phase de qualification et une phase finale.
                                L'hackathon du dev se déroulera en 3 phases : une phase de qualification, une phase de développement et une phase finale.</p>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="50">
                            <div class="accordion-header">
                                <h3>En quoi consiste les phases de l'hackathon du ctf ?</h3>
                                <i data-lucide="chevron-down" class="accordion-icon"></i>
                            </div>
                            <div class="accordion-content">
                                <p>La phase de qualification consiste à résoudre des défis CTF dont les 05 premiers se qualifient pour la phase finale. La phase finale se déroulera en présentiel et consistera à trouver des flags dans une machine virtuelle. Plus d'informations sur la phases finale vous seront données au moment du lancement.</p>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="50">
                            <div class="accordion-header">
                                <h3>Quelle est la durée des phases de l'hackathon du ctf ?</h3>
                                <i data-lucide="chevron-down" class="accordion-icon"></i>
                            </div>
                            <div class="accordion-content">
                                <p>La phase de qualification dure 10 jours et la phase finale dure 24 heures.</p>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="50">
                            <div class="accordion-header">
                                <h3>Comment sont déterminés les gagnants de l'hackathon ctf ?</h3>
                                <i data-lucide="chevron-down" class="accordion-icon"></i>
                            </div>
                            <div class="accordion-content">
                                <p>Les gagnants de l'hackathon ctf seront déterminés par le nombre de points obtenus durant la phase finale. A la fin de cette phase, les deux premières équipes sont récompensées.</p>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="100">
                            <div class="accordion-header">
                                <h3>En quoi consiste les phases de l'hackathon du dev ?</h3>
                                <i data-lucide="chevron-down" class="accordion-icon"></i>
                            </div>
                            <div class="accordion-content">
                                <p>La phase de qualification consiste à résoudre des défis en Algorithme (semblables au coding game) dont chaque defis octroie des points. Le quota de points est de 3000 points pour se qualifier. Les qualifiés participent à une seconde phase qui consiste à développer un projet. Et la phase finale ......................................................</p>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="50">
                            <div class="accordion-header">
                                <h3>Quelle est la durée des phases de l'hackathon du dev ?</h3>
                                <i data-lucide="chevron-down" class="accordion-icon"></i>
                            </div>
                            <div class="accordion-content">
                                <p>La phase de qualification dure 24 heures, la phase de développement dure 10 jours et la phase finale dure 24 heures.</p>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="50">
                            <div class="accordion-header">
                                <h3>Comment sont déterminés les gagnants de l'hackathon dev ?</h3>
                                <i data-lucide="chevron-down" class="accordion-icon"></i>
                            </div>
                            <div class="accordion-content">
                                <p>Les gagnants de l'hackathon dev seront déterminés par XXXXXXXXXXXXXXX. A la fin de cette phase, les deux premières équipes sont récompensées.</p>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="150">
                            <div class="accordion-header">
                                <h3>Comment sont attribués les points et les récompenses ?</h3>
                                <i data-lucide="chevron-down" class="accordion-icon"></i>
                            </div>
                            <div class="accordion-content">
                                <p>Les points sont attribués en fonction des défis. 
                                Les récompenses pour chacun des deux hackathons sont : Top 1 : 600 000 FCFA, Top 2 : 350 000 FCFA</p>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="200">
                            <div class="accordion-header">
                                <h3>De combien sont composées les équipes ?</h3>
                                <i data-lucide="chevron-down" class="accordion-icon"></i>
                            </div>
                            <div class="accordion-content">
                                <p>Les équipes pour le ctf sont composées de 2-4 membres tandis que les équipes pour le dev sont composées de 2-5 membres.</p>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="250">
                            <div class="accordion-header">
                                <h3>Quels types de projets peuvent être réalisés ?</h3>
                                <i data-lucide="chevron-down" class="accordion-icon"></i>
                            </div>
                            <div class="accordion-content">
                                <p>Les projets peuvent couvrir un large éventail de domaines : développement web/mobile, intelligence artificielle, IoT, blockchain, développement durable, etc. Chaque phases a ses propres thèmes et objectifs spécifiques. Nous encourageons l'innovation et la créativé dans tous les domaines technologiques.</p>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="300">
                            <div class="accordion-header">
                                <h3>Est il possible de participer aux deux hackathons ?</h3>
                                <i data-lucide="chevron-down" class="accordion-icon"></i>
                            </div>
                            <div class="accordion-content">
                                <p>Non, vous pouvez participer à un hackathon à la fois.</p>
                            </div>
                        </div>

                    </div>
                </div>

                <section class="contact-section mt-16" data-aos="fade-up">
                    <div class="max-w-2xl mx-auto text-center">
                        <h2>Vous n'avez pas trouvé de réponse à votre question ?</h2>
                        <p class="mb-8">Notre équipe est à votre disposition pour toute information complémentaire. N'hésitez pas à nous contacter et nous vous répondrons dans les plus brefs délais.</p>
                        <a href="https://discord.gg/FbztK5Uagd" class="contact-button">
                            <span>Nous contacter</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <script>        
        // Initialize AOS (Animate On Scroll) if available
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 600,
                once: true,
                easing: 'ease-out-cubic',
                offset: 100
            });
        }
    </script>
</body>

</html>