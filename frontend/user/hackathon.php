<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EsgisHub - Hackathons</title>

  <link rel="stylesheet" href="/css/styles/user/hackaton.css">
  <link rel="stylesheet" href="/css/styles/user/header.css">
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
      background: var(--background);
      color: var(--text);
    }

    .gradient-text {
      background: linear-gradient(90deg, #3b82f6 0%, rgb(17, 39, 99) 100%);
      -webkit-background-clip: text;
      background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .card {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 1.25rem;
      box-shadow: 0 4px 24px 0 rgba(30, 41, 59, 0.15);
    }

    .card:hover {
      box-shadow: 0 8px 32px 0 rgba(59, 130, 246, 0.15);
    }

    .btn {
      background: linear-gradient(60deg, var(--blue) 0%, rgb(18, 39, 92) 100%);
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

    .reveal {
      opacity: 0;
      transform: translateY(20px);
      transition: all 0.6s ease-out;
    }

    .reveal.visible {
      opacity: 1;
      transform: translateY(0);
    }
  </style>
</head>

<body style="background: var(--background); color: var(--text)" class="min-h-screen">
  <?php require_once '../includes/user/header.php'; ?>

  <!-- Hero Section -->
  <section class="min-h-screen flex flex-col items-center justify-center text-center px-4 py-16 md:py-24" style="background: var(--background)">
    <h1 class="text-4xl md:text-6xl font-extrabold mb-4">
      <span class="text-white">Participez à nos</span><br />
      <span class="gradient-text">hackathons</span>
      <span class="text-white">2025</span>
    </h1>
    <div class="w-24 h-1 bar rounded-full mx-auto mb-4"></div>
    <p class="text-lg md:text-2xl text-secondary mb-8">
      Codez. Hackez. Collaborez.
    </p>
    <button class="btn text-lg mb-4 flex items-center justify-center" id="scrollToEvents">
      Voir les événements <i data-lucide="arrow-down" class="ml-2 stroke-current w-4 h-4 animate-bounce-slow"></i>
    </button>
  </section>

  <!-- Choix du défi -->
  <section id="defis" class="reveal py-12 md:py-20" style="background: var(--background-alt)">
    <h2 class="text-3xl md:text-5xl font-bold text-center mb-2">
      Choisissez votre défi
    </h2>
    <div class="w-24 h-1 bar rounded-full mx-auto mb-8"></div>
    <div id="hackathons-container" class="flex flex-col md:flex-row justify-center gap-8 max-w-4xl mx-auto">
      <!-- Les cartes de hackathon seront insérées ici par JavaScript -->
    </div>
  </section>

  <!-- Pourquoi participer -->
  <section class="reveal py-16 md:py-24 px-4">
    <h2 class="text-3xl md:text-5xl font-bold text-center mb-2">
      Pourquoi participer ?
    </h2>
    <div class="w-24 h-1 bar rounded-full mx-auto mb-8"></div>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 max-w-6xl mx-auto">
      <div class="card p-6 flex flex-col items-center">
        <i data-lucide="lightbulb" class="w-8 h-8 mb-2" style="color: gold"></i>
        <h3 class="font-bold mb-1">Gagner en expérience</h3>
        <p class="text-secondary text-center text-sm">
          Développez vos compétences techniques dans un cadre stimulant.
        </p>
      </div>
      <div class="card p-6 flex flex-col items-center">
        <i data-lucide="users" class="w-8 h-8 mb-2" style="color: var(--blue)"></i>
        <h3 class="font-bold mb-1">Travailler en équipe</h3>
        <p class="text-secondary text-center text-sm">
          Collaborez avec des passionnés et partagez vos idées.
        </p>
      </div>
      <div class="card p-6 flex flex-col items-center">
        <i data-lucide="trophy" class="w-8 h-8 mb-2" style="color: #eab308"></i>
        <h3 class="font-bold mb-1">Remporter des prix</h3>
        <p class="text-secondary text-center text-sm">
          Des récompenses exceptionnelles et de la reconnaissance.
        </p>
      </div>
      <div class="card p-6 flex flex-col items-center">
        <i data-lucide="star" class="w-8 h-8 mb-2" style="color: #a78bfa"></i>
        <h3 class="font-bold mb-1">Rencontrer des experts</h3>
        <p class="text-secondary text-center text-sm">
          Échangez avec des professionnels du secteur.
        </p>
      </div>
    </div>
  </section>

  <!-- Timeline des événements -->
  <section class="reveal py-16 md:py-24" style="background: var(--background-alt)">
    <h2 class="text-3xl md:text-5xl font-bold text-center mb-2">
      Timeline des événements
    </h2>
    <div class="w-24 h-1 bar rounded-full mx-auto mb-8"></div>
    <div id="timeline-container" class="flex flex-col md:flex-row justify-center gap-8 max-w-4xl mx-auto">
      <!-- Les timelines des hackathons seront insérées ici par JavaScript -->
    </div>
  </section>

  <!-- Call to action final -->
  <section class="reveal py-16 md:py-24 px-4 text-center">
    <div class="max-w-4xl mx-auto">
      <h2 class="text-3xl md:text-5xl font-bold mb-6">
        Prêt à relever le défi ?
      </h2>
      <p class="text-xl text-secondary mb-8">
        Rejoignez-nous pour une expérience unique de développement et de collaboration.
        Montrez votre talent et repoussez vos limites !
      </p>
      <div id="cta-buttons" class="flex flex-col sm:flex-row justify-center gap-4">
        <!-- Les boutons CTA seront insérés ici par JavaScript -->
      </div>
    </div>
  </section>

  <script src="https://unpkg.com/lucide@latest"></script>
  <script>
    function revealOnScroll() {
      const reveals = document.querySelectorAll(".reveal");
      for (const el of reveals) {
        const windowHeight = window.innerHeight;
        const elementTop = el.getBoundingClientRect().top;
        const revealPoint = 100;
        
        if (elementTop < windowHeight - revealPoint) {
          el.classList.add("visible");
        } else {
          el.classList.remove("visible");
        }
      }
    }

    window.addEventListener('scroll', revealOnScroll);
    
    document.addEventListener('DOMContentLoaded', () => {
      revealOnScroll();
      if (typeof lucide !== 'undefined') {
        lucide.createIcons();
      }
    });

    document.getElementById('scrollToEvents')?.addEventListener('click', () => {
      document.getElementById('defis')?.scrollIntoView({ behavior: 'smooth' });
    });
  </script>
  
  <script src="/js/user/hackathon.js" defer></script>
</body>

</html>