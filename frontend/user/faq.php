<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EsgisHub - FAQ</title>
<link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/user/faq.css">
<link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/user/header.css">
<link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/dist/output.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<!-- Tailwind + Flowbite (CDN) -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.2.7/dist/tailwind.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/flowbite@1.6.5/dist/flowbite.min.css">
<script src="https://unpkg.com/flowbite@1.6.5/dist/flowbite.js"></script>
</head>

<body class="bg-gray-900 text-gray-100">

<?php require_once '../includes/user/header.php'; ?>

<main class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-2">Foire aux Questions (FAQ)</h1>
    <p class="text-gray-400 mb-8" style="text-align: center;">Tout ce que vous devez savoir sur nos hackathons et notre plateforme</p>

    <!-- Accordéon en mode "collapse" (un seul item à la fois) -->
    <div id="accordion-collapse" data-accordion="collapse" class="w-full max-w-2xl mx-auto space-y-2">
    <!-- ITEM 1 -->
    <h2 id="accordion-collapse-heading-1">
        <button
            type="button"
            class="flex items-center justify-between w-full p-5 font-medium text-left text-gray-400 
                    border border-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-700 rounded-lg"
            data-accordion-target="#accordion-collapse-body-1"
            aria-expanded="false"
            aria-controls="accordion-collapse-body-1"
        >
            <span>Comment participer aux hackathons ?</span>
            <svg data-accordion-icon class="w-3 h-3 shrink-0 text-gray-400 transition-transform"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 10 6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M1 1 5 5 9 1"/>
            </svg>
        </button>
    </h2>
        <div id="accordion-collapse-body-1" class="hidden" aria-labelledby="accordion-collapse-heading-1">
        <div class="p-5 border border-gray-700 rounded-lg">
            <p class="mb-2 text-gray-300">
            Pour participer, il suffit de créer un compte sur notre plateforme, former ou rejoindre une équipe, et s'inscrire aux événements qui vous intéressent. Les hackathons peuvent être en présentiel ou en ligne.
            </p>
        </div>
        </div>

        <!-- ITEM 2 -->
        <h2 id="accordion-collapse-heading-2">
        <button
            type="button"
            class="flex items-center justify-between w-full p-5 font-medium text-left text-gray-400
                    border border-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-700 rounded-lg"
            data-accordion-target="#accordion-collapse-body-2"
            aria-expanded="false"
            aria-controls="accordion-collapse-body-2"
        >
            <span>Quel est le processus de sélection des équipes ?</span>
            <svg data-accordion-icon class="w-3 h-3 shrink-0 text-gray-400 transition-transform"
                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 10 6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M1 1 5 5 9 1"/>
            </svg>
        </button>
        </h2>
        <div id="accordion-collapse-body-2" class="hidden" aria-labelledby="accordion-collapse-heading-2">
        <div class="p-5 border border-gray-700 rounded-lg">
            <p class="mb-2 text-gray-300">
            Les équipes sont sélectionnées sur la base de plusieurs critères : la pertinence du projet proposé, la diversité des compétences au sein de l'équipe, et la motivation des participants. Nous encourageons particulièrement les équipes pluridisciplinaires.
            </p>
        </div>
        </div>

        <!-- ITEM 3 -->
        <h2 id="accordion-collapse-heading-3">
        <button
            type="button"
            class="flex items-center justify-between w-full p-5 font-medium text-left text-gray-400
                    border border-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-700 rounded-lg"
            data-accordion-target="#accordion-collapse-body-3"
            aria-expanded="false"
            aria-controls="accordion-collapse-body-3"
        >
            <span>Y a-t-il des prérequis techniques ?</span>
            <svg data-accordion-icon class="w-3 h-3 shrink-0 text-gray-400 transition-transform"
                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 10 6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M1 1 5 5 9 1"/>
            </svg>
        </button>
        </h2>
        <div id="accordion-collapse-body-3" class="hidden" aria-labelledby="accordion-collapse-heading-3">
        <div class="p-5 border border-gray-700 rounded-lg">
            <p class="mb-2 text-gray-300">
            Les prérequis varient selon les hackathons. Certains sont ouverts aux débutants, d'autres nécessitent des compétences spécifiques. Chaque événement précise clairement ses prérequis dans sa description.
            </p>
        </div>
        </div>

        <!-- ITEM 4 -->
        <h2 id="accordion-collapse-heading-4">
        <button
            type="button"
            class="flex items-center justify-between w-full p-5 font-medium text-left text-gray-400
                    border border-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-700 rounded-lg"
            data-accordion-target="#accordion-collapse-body-4"
            aria-expanded="false"
            aria-controls="accordion-collapse-body-4"
        >
            <span>Comment sont attribués les points et les récompenses ?</span>
            <svg data-accordion-icon class="w-3 h-3 shrink-0 text-gray-400 transition-transform"
                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 10 6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M1 1 5 5 9 1"/>
            </svg>
        </button>
        </h2>
        <div id="accordion-collapse-body-4" class="hidden" aria-labelledby="accordion-collapse-heading-4">
        <div class="p-5 border border-gray-700 rounded-lg">
            <p class="mb-2 text-gray-300">
            Les points sont attribués en fonction de plusieurs critères : la participation aux événements, la qualité des projets soumis, les victoires dans les hackathons, et les contributions à la communauté. Les récompenses peuvent inclure des prix en espèces, du matériel, des stages ou des opportunités professionnelles.
            </p>
        </div>
        </div>

        <!-- ITEM 5 -->
        <h2 id="accordion-collapse-heading-5">
        <button
            type="button"
            class="flex items-center justify-between w-full p-5 font-medium text-left text-gray-400
                    border border-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-700 rounded-lg"
            data-accordion-target="#accordion-collapse-body-5"
            aria-expanded="false"
            aria-controls="accordion-collapse-body-5"
        >
            <span>Comment trouver des coéquipiers ?</span>
            <svg data-accordion-icon class="w-3 h-3 shrink-0 text-gray-400 transition-transform"
                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 10 6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M1 1 5 5 9 1"/>
            </svg>
        </button>
        </h2>
        <div id="accordion-collapse-body-5" class="hidden" aria-labelledby="accordion-collapse-heading-5">
        <div class="p-5 border border-gray-700 rounded-lg">
            <p class="mb-2 text-gray-300">
            Vous pouvez trouver des coéquipiers via notre plateforme en utilisant la section 'Teams', en participant aux événements de networking pré-hackathon, ou en rejoignant notre communauté Discord.
            </p>
        </div>
        </div>

        <!-- ITEM 6 -->
        <h2 id="accordion-collapse-heading-6">
        <button
            type="button"
            class="flex items-center justify-between w-full p-5 font-medium text-left text-gray-400
                    border border-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-700 rounded-lg"
            data-accordion-target="#accordion-collapse-body-6"
            aria-expanded="false"
            aria-controls="accordion-collapse-body-6"
        >
            <span>Quels types de projets peuvent être réalisés ?</span>
            <svg data-accordion-icon class="w-3 h-3 shrink-0 text-gray-400 transition-transform"
                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 10 6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M1 1 5 5 9 1"/>
            </svg>
        </button>
        </h2>
        <div id="accordion-collapse-body-6" class="hidden" aria-labelledby="accordion-collapse-heading-6">
        <div class="p-5 border border-gray-700 rounded-lg">
            <p class="mb-2 text-gray-300">
            Les projets peuvent couvrir un large éventail de domaines : développement web/mobile, intelligence artificielle, IoT, blockchain, développement durable, etc. Chaque hackathon a ses propres thèmes et objectifs spécifiques.
            </p>
        </div>
        </div>

        <!-- ITEM 7 -->
        <h2 id="accordion-collapse-heading-7">
        <button
            type="button"
            class="flex items-center justify-between w-full p-5 font-medium text-left text-gray-400
                    border border-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-700 rounded-lg"
            data-accordion-target="#accordion-collapse-body-7"
            aria-expanded="false"
            aria-controls="accordion-collapse-body-7"
        >
            <span>Comment se déroule le mentorat ?</span>
            <svg data-accordion-icon class="w-3 h-3 shrink-0 text-gray-400 transition-transform"
                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 10 6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M1 1 5 5 9 1"/>
            </svg>
        </button>
        </h2>
        <div id="accordion-collapse-body-7" class="hidden" aria-labelledby="accordion-collapse-heading-7">
        <div class="p-5 border border-gray-700 rounded-lg">
            <p class="mb-2 text-gray-300">
            Des mentors expérimentés sont disponibles pendant les hackathons pour guider les équipes. Ils peuvent aider sur des aspects techniques, business, ou design. Vous pouvez les solliciter via la plateforme ou pendant les sessions dédiées.
            </p>
        </div>
        </div>

        <!-- ITEM 8 -->
        <h2 id="accordion-collapse-heading-8">
        <button
            type="button"
            class="flex items-center justify-between w-full p-5 font-medium text-left text-gray-400
                    border border-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-700 rounded-lg"
            data-accordion-target="#accordion-collapse-body-8"
            aria-expanded="false"
            aria-controls="accordion-collapse-body-8"
        >
            <span>Quelle est la durée typique d'un hackathon ?</span>
            <svg data-accordion-icon class="w-3 h-3 shrink-0 text-gray-400 transition-transform"
                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 10 6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M1 1 5 5 9 1"/>
            </svg>
        </button>
        </h2>
        <div id="accordion-collapse-body-8" class="hidden" aria-labelledby="accordion-collapse-heading-8">
        <div class="p-5 border border-gray-700 rounded-lg">
            <p class="mb-2 text-gray-300">
            La durée varie selon l'événement. Les hackathons classiques durent généralement 24 à 48 heures. Nous organisons aussi des hackathons plus longs (1 semaine) ou plus courts (12 heures) selon les objectifs.
            </p>
        </div>
        </div>

        <!-- ITEM 9 -->
        <h2 id="accordion-collapse-heading-9">
        <button
            type="button"
            class="flex items-center justify-between w-full p-5 font-medium text-left text-gray-400
                    border border-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-700 rounded-lg"
            data-accordion-target="#accordion-collapse-body-9"
            aria-expanded="false"
            aria-controls="accordion-collapse-body-9"
        >
            <span>Comment sont évalués les projets ?</span>
            <svg data-accordion-icon class="w-3 h-3 shrink-0 text-gray-400 transition-transform"
                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 10 6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M1 1 5 5 9 1"/>
            </svg>
        </button>
        </h2>
        <div id="accordion-collapse-body-9" class="hidden" aria-labelledby="accordion-collapse-heading-9">
        <div class="p-5 border border-gray-700 rounded-lg">
            <p class="mb-2 text-gray-300">
            Les projets sont évalués par un jury composé d'experts du domaine selon plusieurs critères : innovation, faisabilité technique, impact potentiel, qualité de la présentation, et respect des contraintes du hackathon.
            </p>
        </div>
        </div>

        <!-- ITEM 10 -->
        <h2 id="accordion-collapse-heading-10">
        <button
            type="button"
            class="flex items-center justify-between w-full p-5 font-medium text-left text-gray-400
                    border border-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-700 rounded-lg"
            data-accordion-target="#accordion-collapse-body-10"
            aria-expanded="false"
            aria-controls="accordion-collapse-body-10"
        >
            <span>Peut-on participer à distance ?</span>
            <svg data-accordion-icon class="w-3 h-3 shrink-0 text-gray-400 transition-transform"
                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 10 6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M1 1 5 5 9 1"/>
            </svg>
        </button>
        </h2>
        <div id="accordion-collapse-body-10" class="hidden" aria-labelledby="accordion-collapse-heading-10">
        <div class="p-5 border border-gray-700 rounded-lg">
            <p class="mb-2 text-gray-300">
            Oui, la plupart de nos hackathons proposent une option de participation à distance. Nous utilisons des outils de collaboration en ligne pour faciliter le travail d'équipe et les présentations virtuelles.
            </p>
        </div>
        </div>


    </div>
    <br> <br>
    <h2 style="text-align:center; color:white; font-weight: bold;">Vous n'avez pas trouvé la reponse à votre question ?</h2> <br>
    <p style="text-align:center; color:gray">Notre équipe est à votre disposition pour toute information complémentaire. N'hésitez pas à nous contacter.</p> <br> 

    <div class="flex justify-center">
    <button class="bg-blue-500 hover:bg-blue-700 text-white py-2 px-4 rounded font-weight-bold text-center" type="submit" name="contact" value="contact"> 
    <i class="fas fa-envelope"></i> Contactez-nous
    </button>
    </div>

</main>

<!-- Script pour faire défiler l'élément ouvert dans la vue -->
<script>
    document.addEventListener('accordion:open', function(event) {
        const openedElement = event.detail && event.detail.target ? event.detail.target : event.target;
        openedElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
</script>
</body>
</html>
