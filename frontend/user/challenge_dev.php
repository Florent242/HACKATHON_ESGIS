<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Défis de développement</title>
<!--    <link rel="stylesheet" href="/css/styles/user/challenge_dev.css">-->
    <link rel="stylesheet" href="/css/styles/user/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script defer src="/js/user/challenge_dev.js"></script>
    <style>
      :root {
        --background: #0a0f1c;
        
        --card-bg: linear-gradient(135deg, #1a1f2b 0%, #141925 100%);
        --card-hover: #1e293b;
        --primary: #3b82f6;
        --primary-dark: #2563eb;
        --text: #ffffff;
        --text-secondary: #94a3b8;
        --border: #2d3441;
        --green: #22c55e;
        --yellow: #eab308;
        --red: #ef4444;
      }
      body {
        background-color: var(--background);
        color: var(--text);
      }
      .bg-card {
        background: var(--card-bg);
      }
      .border-main {
        border-color: var(--border);
      }
      .text-main {
        color: var(--text);
      }
      .text-sec {
        color: var(--text-secondary);
      }
      .bg-primary {
        background-color: var(--primary);
      }
      .hover-bg-primary-dark:hover {
        background-color: var(--primary-dark);
      }
      .progress-bar-bg {
        background-color: var(--border);
      }
      .card {
        border: 1px solid var(--border);
        border-radius: 12px;
        transition: transform 0.2s ease, border-color 0.2s ease,
          box-shadow 0.2s ease;
      }
      .card:hover {
        transform: translateY(-4px) scale(1.02);
        border-color: var(--primary);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
      }
      .custom-btn {
        transition: transform 0.2s ease, background-color 0.2s ease;
      }
      .custom-btn:hover {
        transform: scale(1.05);
      }
      
      /* Animation d'apparition pour les cartes */
      @keyframes fadeInUp {
        from {
          opacity: 0;
          transform: translateY(30px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }
      
      .challenge-card {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
      }
      
      .challenge-card:nth-child(1) { animation-delay: 0.1s; }
      .challenge-card:nth-child(2) { animation-delay: 0.2s; }
      .challenge-card:nth-child(3) { animation-delay: 0.3s; }
      .challenge-card:nth-child(4) { animation-delay: 0.4s; }
      .challenge-card:nth-child(5) { animation-delay: 0.5s; }
      .challenge-card:nth-child(6) { animation-delay: 0.6s; }
      .challenge-card:nth-child(7) { animation-delay: 0.7s; }
      .challenge-card:nth-child(8) { animation-delay: 0.8s; }
      
      .tag-category {
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        display: inline-block;
      }
      .tag-purple {
        background-color: rgba(168, 85, 247, 0.1);
        color: #d8b4fe;
        border: 1px solid rgba(168, 85, 247, 0.2);
        box-shadow: 0 0 12px rgba(168, 85, 247, 0.3);
        text-shadow: 0 0 5px rgba(216, 180, 254, 0.5);
      }
      .tag-blue {
        background-color: rgba(59, 130, 246, 0.1);
        color: #93c5fd;
        border: 1px solid rgba(59, 130, 246, 0.2);
        box-shadow: 0 0 12px rgba(59, 130, 246, 0.3);
        text-shadow: 0 0 5px rgba(147, 197, 253, 0.5);
      }
      .tag-green {
        background-color: rgba(34, 197, 94, 0.1);
        color: #86efac;
        border: 1px solid rgba(34, 197, 94, 0.2);
        box-shadow: 0 0 12px rgba(34, 197, 94, 0.3);
        text-shadow: 0 0 5px rgba(134, 239, 172, 0.5);
      }
    </style>
</head>

<body class="min-h-screen">
    <?php require_once '../includes/user/header.php'; ?>

   
    

    <div class="flex flex-col lg:flex-row max-w-7xl mx-auto mt-6 md:mt-8 gap-6 md:gap-8 px-2 md:px-0">
      <!-- Sidebar -->
      <aside class="w-full lg:w-80 flex-shrink-0 flex flex-col gap-6 order-1 lg:order-none">
        <!-- Performances -->
        <div class="bg-card rounded-xl p-4 md:p-6 card mb-2">
          <div class="flex items-center gap-2 mb-2">
            <span class="text-yellow-400 text-2xl"><i class="w-4 h-4 stroke-current" data-lucide="trophy"></i></span>
            <span class="font-bold text-lg">Vos performances</span>
          </div>
          <div class="text-3xl md:text-4xl font-bold mb-1">320</div>
          <div class="text-sec text-sm mb-2">points obtenus</div>
          <div class="mb-3">
            
            
          </div>
          <div class="flex gap-2 mt-2">
            <div class="progress-bar-bg rounded px-3 py-1 text-center flex-1">
              <div class="font-bold text-lg" style="color: var(--green)">3</div>
              <div class="text-xs text-sec">Résolus</div>
            </div>
            <div class="progress-bar-bg rounded px-3 py-1 text-center flex-1">
              <div class="font-bold text-lg" style="color: var(--primary)">
                #15
              </div>
              <div class="text-xs text-sec">Rang</div>
            </div>
          </div>
        </div>
        <!-- Règles importantes -->
        <div class="bg-card rounded-xl p-3 md:p-4 card">
          <div class="flex items-center gap-2 mb-2">
            <span style="color: var(--primary); font-size:1rem;"><i class="fa fa-bullseye"></i></span>
            <span class="font-bold text-base">Règles importantes</span>
          </div>
          <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            <div style="display: flex; align-items: flex-start; gap: 0.7rem; background: var(--border); border-radius: 0.5rem; padding: 0.7rem 0.8rem;">
              <div style="background: #193c2e; color: #4ade80; width: 1.5rem; height: 1.5rem; display: flex; align-items: center; justify-content: center; border-radius: 0.4rem; font-size: 0.95rem; margin-right: 0.3rem;">
                <i class="fa fa-code"></i>
              </div>
              <div>
                <div style="font-size: 0.95rem; font-weight: 500; color: #bcbcbc;">Langages autorisés :</div>
                <div style="font-size: 0.98rem; font-weight: 600; color: #fff; margin-top: 0.1rem;">Python, Java, C++,<br>JavaScript</div>
              </div>
            </div>
            <div style="display: flex; align-items: flex-start; gap: 0.7rem; background: var(--border); border-radius: 0.5rem; padding: 0.7rem 0.8rem;">
              <div style="background: #3a2e19; color: #FFD600; width: 1.5rem; height: 1.5rem; display: flex; align-items: center; justify-content: center; border-radius: 0.4rem; font-size: 0.95rem; margin-right: 0.3rem;">
                <i class="fa fa-clock"></i>
              </div>
              <div>
                <div style="font-size: 0.95rem; font-weight: 500; color: #bcbcbc;">Temps d'exécution max :</div>
                <div style="font-size: 0.98rem; font-weight: 600; color: #fff; margin-top: 0.1rem;">2 secondes par test</div>
              </div>
            </div>
          </div>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="flex-1 order-0 lg:order-none">
        <h1 class="text-2xl md:text-3xl font-bold mb-1">
          Phase 1 : Challenges Algorithmiques
        </h1>
        <p class="text-sec mb-4 md:mb-6">
          Résolvez les défis pour gagner des points et grimper le classement
        </p>
        <!-- Search & Filters -->
        <div class="flex flex-row sm:flex-row gap-2 md:gap-3 mb-6 md:mb-8">
          <div class="flex-1 relative flex items-center gap-2 bg-card border border-main text-main rounded-lg px-4 py-2">
            <i class="w-4 h-4 stroke-current" data-lucide="search"></i>
            <input
              type="text"
              placeholder="Rechercher un challenge par mot-clé..."
              class="w-full placeholder-gray-400 focus:outline-none"
            />
          </div>
          <div class="flex gap-2">
            <button
              class="custom-btn bg-primary text-white px-4 py-2 rounded-lg font-semibold hover-bg-primary-dark"
            >
              Tous
            </button>
            <button
              class="custom-btn bg-card text-main px-4 py-2 rounded-lg font-semibold border border-main hover-bg-primary-dark"
            >
              Facile
            </button>
            <button
              class="custom-btn bg-card text-main px-4 py-2 rounded-lg font-semibold border border-main hover-bg-primary-dark"
            >
              Moyen
            </button>
            <button
              class="custom-btn bg-card text-main px-4 py-2 rounded-lg font-semibold border border-main hover-bg-primary-dark"
            >
              Difficile
            </button>
          </div>
        </div>
        <!-- Challenges Grid -->
        <div id="challenges-grid" class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
          <!-- Les cartes de challenge seront insérées ici par JavaScript -->
        </div>
      </main>
    </div>
  </body>
</html>