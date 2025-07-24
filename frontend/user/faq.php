<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Foire aux Questions</title>
    <!-- Preload critical CSS -->
    <link rel="preload" href="/css/styles/user/faq.css" as="style">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles/user/faq.css">
    <link rel="stylesheet" href="/css/styles/user/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="/js/user/faq.js"></script>
    <meta name="description" content="Trouvez les réponses à vos questions sur les hackathons et la plateforme EsgisHub.">
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
                                <p>Pour participer, il suffit de créer un compte sur notre plateforme, former ou rejoindre une équipe, et s'inscrire aux événements qui vous intéressent. Les hackathons peuvent être en présentiel ou en ligne. Notre équipe est là pour vous guider à chaque étape du processus.</p>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="50">
                            <div class="accordion-header">
                                <h3>Quel est le processus de sélection des équipes ?</h3>
                                <i data-lucide="chevron-down" class="accordion-icon"></i>
                            </div>
                            <div class="accordion-content">
                                <p>Les équipes sont sélectionnées sur la base de plusieurs critères : la pertinence du projet proposé, la diversité des compétences au sein de l'équipe, et la motivation des participants. Nous encourageons particulièrement les équipes pluridisciplinaires qui combinent différentes expertises pour des projets innovants.</p>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="100">
                            <div class="accordion-header">
                                <h3>Y a-t-il des prérequis techniques ?</h3>
                                <i data-lucide="chevron-down" class="accordion-icon"></i>
                            </div>
                            <div class="accordion-content">
                                <p>Les prérequis varient selon les hackathons. Certains sont ouverts aux débutants, d'autres nécessitent des compétences spécifiques. Chaque événement précise clairement ses prérequis dans sa description. Nous proposons également des ressources d'apprentissage pour vous aider à vous préparer.</p>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="150">
                            <div class="accordion-header">
                                <h3>Comment sont attribués les points et les récompenses ?</h3>
                                <i data-lucide="chevron-down" class="accordion-icon"></i>
                            </div>
                            <div class="accordion-content">
                                <p>Les points sont attribués en fonction de plusieurs critères : la participation aux événements, la qualité des projets soumis, les victoires dans les hackathons, et les contributions à la communauté. Les récompenses peuvent inclure des prix en espèces, du matériel, des stages ou des opportunités professionnelles avec nos partenaires.</p>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="200">
                            <div class="accordion-header">
                                <h3>Comment trouver des coéquipiers ?</h3>
                                <i data-lucide="chevron-down" class="accordion-icon"></i>
                            </div>
                            <div class="accordion-content">
                                <p>Vous pouvez trouver des coéquipiers via notre plateforme en utilisant la section 'Teams', en participant aux événements de networking pré-hackathon, ou en rejoignant notre communauté Discord. Nous organisons également des sessions de team building pour faciliter les rencontres.</p>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="250">
                            <div class="accordion-header">
                                <h3>Quels types de projets peuvent être réalisés ?</h3>
                                <i data-lucide="chevron-down" class="accordion-icon"></i>
                            </div>
                            <div class="accordion-content">
                                <p>Les projets peuvent couvrir un large éventail de domaines : développement web/mobile, intelligence artificielle, IoT, blockchain, développement durable, etc. Chaque hackathon a ses propres thèmes et objectifs spécifiques. Nous encourageons l'innovation et la créativé dans tous les domaines technologiques.</p>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="300">
                            <div class="accordion-header">
                                <h3>Comment se déroule le mentorat ?</h3>
                                <i data-lucide="chevron-down" class="accordion-icon"></i>
                            </div>
                            <div class="accordion-content">
                                <p>Des mentors expérimentés sont disponibles pendant les hackathons pour guider les équipes. Ils peuvent aider sur des aspects techniques, business, ou design. Vous pouvez les solliciter via la plateforme ou pendant les sessions dédiées. Nous organisons également des ateliers et des conférences pour enrichir votre expérience.</p>
                            </div>
                        </div>

                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="350">
                            <div class="accordion-header">
                                <h3>Quelle est la durée typique d'un hackathon ?</h3>
                                <i data-lucide="chevron-down" class="accordion-icon"></i>
                            </div>
                            <div class="accordion-content">
                                <p>La durée varie selon l'événement. Les hackathons classiques durent généralement 24 à 48 heures. Nous organisons aussi des hackathons plus longs (1 semaine) ou plus courts (12 heures) selon les objectifs. Les détails précis sont toujours indiqués dans la description de chaque événement.</p>
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