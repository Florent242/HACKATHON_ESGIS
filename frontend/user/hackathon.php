<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Hackathons</title>
    
    <link rel="stylesheet" href="/css/styles/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;900&display=swap" rel="stylesheet" />
    <style>
      :root {
        --background: #030b20;
        --card-bg: linear-gradient(135deg, #030b20 0%, #030f2a 100%);
        --card-hover: #1e293b;
        --blue: #3b82f6;
        --text: #ffffff;
        --text-secondary: #94a3b8;
        --border: #1e293b;
        --background-alt: #10192b;
      }
      body {
        font-family: "Montserrat", sans-serif;
        background: var(--background);
        color: var(--text);
      }
      .gradient-text {
        background: linear-gradient(90deg, #3b82f6 0%,rgb(17, 39, 99) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-fill-color: transparent;
      }
      .card {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 1.25rem;
        box-shadow: 0 4px 24px 0 rgba(30, 41, 59, 0.15);
        transition: box-shadow 0.3s, background 0.3s;
      }
      .card:hover {
        background: var(--card-hover);
        box-shadow: 0 8px 32px 0 rgba(59, 130, 246, 0.15);
      }
      .btn {
        background: linear-gradient(60deg, var(--blue) 0%,rgb(18, 39, 92) 100%);
        color: var(--text);
        border-radius: 9999px;
        font-weight: bold;
        padding: 0.75rem 2rem;
        box-shadow: 0 2px 8px 0 rgba(59, 130, 246, 0.1);
        transition: transform 0.2s, box-shadow 0.2s;
      }
      .btn:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 16px 0 rgba(59, 130, 246, 0.18);
      }
      .bar {
        background: linear-gradient(90deg, var(--blue) 0%, #030b20 100%);
      }
      .text-secondary {
        color: var(--text-secondary);
      }
      .bordered {
        border: 1px solid var(--border);
      }
    </style>
</head>
<body style="background: var(--background); color: var(--text)" class="min-h-screen">
    <?php require_once '../includes/header.php'; ?>

    <!-- Hero Section -->
    <section
      class="reveal opacity-0 translate-y-8 transition-all duration-700 flex flex-col items-center justify-center min-h-[80vh] text-center px-4"
      style="background: var(--background)"
    >
      <h1 class="text-4xl md:text-6xl font-extrabold mb-4">
        <span class="text-white">Participez à nos</span><br />
        <span class="gradient-text">hackathons</span>
        <span class="text-white">2025</span>
      </h1>
      <div class="w-24 h-1 bar rounded-full mx-auto mb-4"></div>
      <p class="text-lg md:text-2xl text-secondary mb-8">
        Codez. Hackez. Collaborez.
      </p>
      <button class="btn text-lg mb-4" id="scrollToEvents">
        Voir les événements <span class="ml-2">↓</span>
      </button>
    </section>

    <!-- Choix du défi -->
    <section
      id="defis"
      class="reveal opacity-0 translate-y-8 transition-all duration-700 py-16"
      style="background: var(--background-alt)"
    >
      <h2 class="text-3xl md:text-5xl font-bold text-center mb-2">
        Choisissez votre défi
      </h2>
      <div class="w-24 h-1 bar rounded-full mx-auto mb-8"></div>
      <div
        id="hackathons-container"
        class="flex flex-col md:flex-row justify-center gap-8 max-w-4xl mx-auto"
      >
        <!-- Les cartes de hackathon seront insérées ici par JavaScript -->
      </div>
    </section>

    <!-- Pourquoi participer -->
    <section
      class="reveal opacity-0 translate-y-8 transition-all duration-700 py-16"
      style="background: var(--background)"
    >
      <h2 class="text-3xl md:text-5xl font-bold text-center mb-2">
        Pourquoi participer ?
      </h2>
      <div class="w-24 h-1 bar rounded-full mx-auto mb-8"></div>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 max-w-6xl mx-auto">
        <div class="card p-6 flex flex-col items-center">
          <div class="text-3xl mb-2" style="color: gold">💡</div>
          <h3 class="font-bold mb-1">Gagner en expérience</h3>
          <p class="text-secondary text-center text-sm">
            Développez vos compétences techniques dans un cadre stimulant.
          </p>
        </div>
        <div class="card p-6 flex flex-col items-center">
          <div class="text-3xl mb-2" style="color: var(--blue)">👥</div>
          <h3 class="font-bold mb-1">Travailler en équipe</h3>
          <p class="text-secondary text-center text-sm">
            Collaborez avec des passionnés et partagez vos idées.
          </p>
        </div>
        <div class="card p-6 flex flex-col items-center">
          <div class="text-3xl mb-2" style="color: #eab308">🏆</div>
          <h3 class="font-bold mb-1">Remporter des prix</h3>
          <p class="text-secondary text-center text-sm">
            Des récompenses exceptionnelles et de la reconnaissance.
          </p>
        </div>
        <div class="card p-6 flex flex-col items-center">
          <div class="text-3xl mb-2" style="color: #a78bfa">⭐</div>
          <h3 class="font-bold mb-1">Rencontrer des experts</h3>
          <p class="text-secondary text-center text-sm">
            Échangez avec des professionnels du secteur.
          </p>
        </div>
      </div>
    </section>

    <!-- Timeline des événements -->
    <section
      class="reveal opacity-0 translate-y-8 transition-all duration-700 py-16"
      style="background: var(--background-alt)"
    >
      <h2 class="text-3xl md:text-5xl font-bold text-center mb-2">
        Timeline des événements
      </h2>
      <div class="w-24 h-1 bar rounded-full mx-auto mb-8"></div>
      <div
        id="timeline-container"
        class="flex flex-col md:flex-row justify-center gap-8 max-w-4xl mx-auto"
      >
        <!-- Les timelines des hackathons seront insérées ici par JavaScript -->
      </div>
    </section>

    <!-- Call to action final -->
    <section
      class="reveal opacity-0 translate-y-8 transition-all duration-700 py-16 text-center"
      style="background: var(--background)"
    >
      <h2 class="text-3xl md:text-5xl font-bold mb-2">
        Rejoignez-nous pour
        <span class="gradient-text">repousser vos limites !</span>
      </h2>
      <p class="text-secondary mb-8">
        Ne manquez pas cette opportunité unique de vous dépasser et de
        rencontrer la communauté tech.
      </p>
      <div class="flex flex-col md:flex-row justify-center gap-4">
        <button
          class="flex items-center gap-2 px-8 py-3 rounded-full font-bold text-lg shadow-lg transition-transform bg-[var(--blue)] text-white hover:scale-105"
        >
          <span
            class="text-white bg-transparent"
            style="color: var(--white); font-size: 1.3em"
            >&lt;/&gt;</span
          >
          Participer au HackDev
        </button>
        <button
          class="flex items-center gap-2 px-8 py-3 rounded-full font-bold text-lg shadow-lg transition-transform bg-[#030B20] text-[var(--blue)] border border-[var(--blue)] hover:bg-[var(--blue)] hover:text-white"
        >
          <span style="font-size: 1.3em">🔒</span>
          Participer au HackSec
        </button>
      </div>
    </section>

    <script defer src="/js/main.js"></script>
    <script defer src="/js/user/hackathon.js"></script>
</body>
</html>