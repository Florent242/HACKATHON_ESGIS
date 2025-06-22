<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Défis de développement</title>
    <link rel="stylesheet" href="/css/styles/user/challenge_dev.css">
    <link rel="stylesheet" href="/css/styles/user/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script defer src="/js/user/challenge_dev.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Ajout Tailwind et styles custom du prompt -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
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

    <!-- Top Bar -->
    <nav
      class="w-full bg-card px-4 md:px-8 py-3 flex flex-col md:flex-row items-start md:items-center justify-between border-b border-main gap-2"
    >
      <div class="flex flex-wrap items-center gap-2 md:gap-4">
        <span class="text-base md:text-lg cursor-pointer hover:underline"
          >&larr; Retour aux hackathons</span
        >
        <span class="ml-0 md:ml-6 text-orange-400 font-semibold"
          >&#128293; Phase 1 : <span class="font-bold">8 challenges</span></span
        >
        <span class="ml-0 md:ml-2" style="color: var(--green)">• 3 résolus</span>
        <span class="ml-0 md:ml-2" style="color: var(--primary)">• 320 pts</span>
      </div>
      <span
        class="bg-green-900/30 text-green-400 px-3 py-1 rounded-full text-xs md:text-sm flex items-center gap-1 mt-2 md:mt-0"
        >&#128293; Phase active</span
      >
    </nav>

    <div class="flex flex-col lg:flex-row max-w-7xl mx-auto mt-6 md:mt-8 gap-6 md:gap-8 px-2 md:px-0">
      <!-- Sidebar -->
      <aside class="w-full lg:w-80 flex-shrink-0 flex flex-col gap-6 order-1 lg:order-none">
        <!-- Performances -->
        <div class="bg-card rounded-xl p-4 md:p-6 card mb-2">
          <div class="flex items-center gap-2 mb-2">
            <span class="text-yellow-400 text-2xl">&#x1F3C6;</span>
            <span class="font-bold text-lg">Vos performances</span>
          </div>
          <div class="text-3xl md:text-4xl font-bold mb-1">320</div>
          <div class="text-sec text-sm mb-2">points obtenus</div>
          <div class="mb-3">
            <div class="flex justify-between text-xs text-sec mb-1">
              <span>Progression</span><span>38%</span>
            </div>
            <div class="w-full h-2 progress-bar-bg rounded">
              <div class="h-2 bg-primary rounded" style="width: 38%"></div>
            </div>
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
        <div class="bg-card rounded-xl p-4 md:p-6 card">
          <div class="flex items-center gap-2 mb-3">
            <span style="color: var(--primary)">&#128712;</span>
            <span class="font-bold">Règles importantes</span>
          </div>
          <ul class="space-y-3 text-sm text-sec">
            <li class="flex items-center gap-2 flex-wrap">
              <span style="color: var(--green)">&lt;/&gt;</span> Langages
              autorisés :<br />
              <span class="ml-6 text-main">Python, Java, C++, JavaScript</span>
            </li>
            <li class="flex items-center gap-2">
              <span style="color: var(--yellow)">&#9200;</span> Temps
              d'exécution max :
              <span class="ml-1 text-main">2 secondes par test</span>
            </li>
            <li class="flex items-center gap-2">
              <span style="color: var(--primary)">&#9889;</span> Chaque
              soumission est évaluée automatiquement
            </li>
          </ul>
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
        <div class="flex flex-col sm:flex-row gap-2 md:gap-3 mb-6 md:mb-8">
          <div class="flex-1 relative">
            <input
              type="text"
              placeholder="🔍 Rechercher un challenge par mot-clé..."
              class="w-full bg-card border border-main text-main rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400"
            />
          </div>
          <div class="flex gap-2">
            <button
              class="custom-btn bg-primary text-white px-4 py-2 rounded-lg font-semibold hover-bg-primary-dark"
            >
              &#128269; Tous
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