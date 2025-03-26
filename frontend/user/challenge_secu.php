<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EsgisHub - Challenges</title>
  <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/user/challenge_secu.css">
  <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/user/header.css">
  <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/dist/output.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script defer src="/HACKATHON_ESGIS/public/js/user/challenge_secu.js"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  
</head>
<body>
  <!-- Header -->
  <?php require_once '../includes/user/header.php'; ?>

  <!-- Main structure -->
  <section class="main-container">

    <div>

        <!-- Sidebar Filters -->
        <div class="filters-container">
        <aside class="filters-sidebar">

            <!-- Filtre global -->
            <div class="filter-group">
                <h2 style="display: flex; align-items: center; gap: 0.5rem;"> <i data-lucide="filter"></i> <span>Filters</span></h2>

                <!-- Difficulté sous forme de boutons -->
                <h3 style="display: flex; align-items: center; gap: 0.5rem;"> <i data-lucide="gauge"></i> <span>Difficulty</span></h3>
                <div class="filter-buttons" data-type="difficulty">
                <button class="filter-btn" id="easy" style="background-color: var(--green); color: var(--text); border-color: var(--green);">
                    Easy
                </button>
                <button class="filter-btn" id="medium" style="background-color: var(--yellow); color: var(--text); border-color: var(--yellow);">
                    Medium
                </button>
                <button class="filter-btn" id="hard" style="background-color: var(--red); color: var(--text); border-color: var(--red);">
                    Hard
                </button>
                <button class="filter-btn" id="expert" style="background-color: var(--purple); color: var(--text); border-color: var(--purple);">
                    Expert
                </button>
                </div>

                <!-- Catégorie en liste -->
                <h3 style="display: flex; align-items: center; gap: 0.5rem;"> <i data-lucide="layers"></i> <span>Category</span></h3>
                <div class="filter-buttons" data-type="category">
                <button class="filter-btn">
                    <i data-lucide="globe"></i>
                    <span>Web</span>
                </button>
                <button class="filter-btn">
                    <i data-lucide="code"></i>
                    <span>Binary</span>
                </button>
                <button class="filter-btn">
                    <i data-lucide="lock-keyhole"></i>
                    <span>Crypto</span>
                </button>
                <button class="filter-btn">
                    <i data-lucide="server"></i>
                    <span>Network</span>
                </button>
                <button class="filter-btn">
                    <i data-lucide="rotate-ccw"></i>
                    <span>Reversing</span>
                </button>
                <button class="filter-btn">
                    <i data-lucide="eye-off"></i>
                    <span>Steganography</span>
                </button>
                </div>
                <br>

                <!-- Statut en liste -->
                <h3 style="display: flex; align-items: center; gap: 0.5rem;"> <i data-lucide="activity"></i> <span>Status</span></h3>
                <div class="filter-buttons" data-type="status">
                <button class="filter-btn">
                    <i data-lucide="check-circle"></i>
                    <span>Solved</span>
                </button>
                <button class="filter-btn">
                    <i data-lucide="x-circle"></i>
                    <span>Unsolved</span>
                </button>
                </div>
                <br>

                <!-- Bouton Clear Filters -->
                <button class="clear-filters">
                <i data-lucide="refresh-ccw"></i>
                Clear Filters
                </button>
            </div>

        </aside>
        </div>

        
        <div class="filters-container">
            <aside class="filters-sidebar">
                <h3 style="display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="users"></i> <span>Top Users</span>
                </h3>
                <br>
                <ol id="top-hackers">
                    <li>Chargement...</li> <!-- Contenu remplacé dynamiquement -->
                </ol>
                <br>
                <button id="view-leaderboard" onclick="window.location.href='leaderboard.php'">
                    View Full Leaderboard
                </button>
                
            </aside>
        </div>


    </div>

    <!-- Main content -->
    <div class="challenges-main">
        <!-- Search and filters -->
        <div class="search-container" style="background: var(--card-bg); border-radius: 1rem; padding: 1rem; margin-bottom: 1.5rem;">
            <div class="search-bar" style="width: 100%;">
                <div class="search-input-wrapper">
                    <i data-lucide="search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                    <input type="text" placeholder="Search challenges by name, category, or tag..." style="width: 100%; padding: 1rem 1rem 1rem 2.5rem; background: transparent; border: none; color: white; font-size: 0.875rem;">
                </div>
            </div>
            <div class="popular-tags">
                <span>Popular:</span>
                <div class="tags">
                    <button class="tag">SQL Injection</button>
                    <button class="tag">XSS</button>
                    <button class="tag">Buffer Overflow</button>
                    <button class="tag">Password Cracking</button>
                </div>
            </div>
        </div>

        <!-- Challenges section -->
        <section class="challenges-section">
            <!-- Section header with filters -->
            <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <div class="filters-section" style="display: flex; align-items: center; gap: 1rem;">
                    <div class="filter-btn-group" style="display: flex; gap: 0.5rem;">
                        <button class="filter-btn active">All Challenges</button>
                        <button class="filter-btn">Recent</button>
                        <button class="filter-btn">Popular</button>
                    </div>
                    <div class="progress-section" style="display: flex; align-items: center; gap: 1rem;">
                        <span style="color: var(--text-secondary); font-size: 0.875rem;">My Progress</span>
                        
                    </div>
                </div>
                <div class="sort-filter" style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="color: var(--text-secondary); font-size: 0.875rem;">Sort By</span>
                    <div class="sort-select" style="position: relative;">
                        <button class="sort-btn" style="background: transparent; border: none; color: white; font-size: 0.875rem; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                            <span>Latest</span>
                            <i data-lucide="chevron-down" style="color: var(--text-secondary);"></i>
                        </button>
                        <div class="sort-options" style="position: absolute; top: 100%; right: 0; background: var(--card-bg); border-radius: 0.5rem; padding: 0.5rem; margin-top: 0.25rem; display: none;">
                            <div class="sort-option" style="padding: 0.5rem; cursor: pointer; color: white; font-size: 0.875rem;">Latest</div>
                            <div class="sort-option" style="padding: 0.5rem; cursor: pointer; color: white; font-size: 0.875rem;">Most Solved</div>
                            <div class="sort-option" style="padding: 0.5rem; cursor: pointer; color: white; font-size: 0.875rem;">Difficulty</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Challenge grid -->
            <div class="challenge-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
                <!-- Challenge card -->
                <div class="cyber-card" 
                    data-title="SQL Injection Basics"
                    data-description="Learn the fundamentals of SQL injection attacks and how to prevent them. Explore how to bypass authentication and extra..."
                    data-difficulty="Easy"
                    data-category="Web"
                    data-time="30 min"
                    data-points="100"
                    data-hint="Hint"
                    data-tags="database, sql, authentication bypass"
                    style="background: var(--card-bg); border-radius: 1rem; overflow: hidden; transition: transform 0.3s ease;">
                    <div class="card-header" style="padding: 1.5rem;">
                        <div class="card-header-info">
                            <div class="left-info"><i data-lucide="file-text" style="color:var(--blue);"></i> <span class="difficulty" style="color: var(--green); ">Easy</span></div>
                            <div class="right-info"><i data-lucide="trophy" style="color: gold;"></i> <span>100 pts</span></div>
                        </div>
                        <h3 style="color: white; font-size: 1.125rem; margin-bottom: 0.75rem;">SQL Injection Basics</h3>
                        <div class="meta" style="display: flex; gap: 1rem; color: var(--text-secondary); font-size: 0.875rem;">
                            <span class="category" style="background: rgba(59, 130, 246, 0.2); padding: 0.25rem 0.75rem; border-radius: 0.5rem;">Web</span>
                            <div style="display: flex; align-items: center; gap: 0.5rem;"><i data-lucide="timer"></i><span class="time" style="color: var(--text-secondary);">30 min</span></div>
                        </div>
                    </div>
                    <p class="description" style="padding: 1.5rem; color: var(--text-secondary); font-size: 0.875rem; line-height: 1.4;">
                        Learn the fundamentals of SQL injection attacks and how to prevent them. Explore how to bypass authentication and extra...
                    </p>
                    <div class="tags">
                        <span class="tag">AUTHENTICATION BYPASS</span>
                        <span class="tag">DATABASES</span>
                        <span class="tag">SQL</span>
                    </div>
                    <div class="stats-table" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; padding: 1.5rem;">
                        <div class="stat" style="display: flex; flex-direction: row; gap: 0.25rem;">
                            <i data-lucide="user" style="gap: 0.5rem;"></i>
                            <span class="value" id="solves-count" style="color: white; font-size: 1rem; font-weight: 500;">432 solves</span>     
                        </div>
                    </div>
                    <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-top: 1px solid var(--border);">
                        <button class="badge hack-now" id="hack-now-btn">HACK NOW</button>
                        <div class="status solved" id="status" style="display: none;">
                            <i data-lucide="check-circle" style="color: var(--green);"></i>
                            <span>Solved</span>
                        </div>
                    </div>
                </div>

                <!-- 2nd challenge -->
                <div class="cyber-card" 
                    data-title="XSS Attack Simulation"
                    data-description="Simulate cross-site scripting attacks and learn defensive techniques. Find vulnerabilities in a simulated web application..."
                    data-difficulty="Medium"
                    data-category="Web"
                    data-time="45 min"
                    data-points="150"
                    data-hint="Hint"
                    data-tags="web, xss, attack, simulation"
                    style="background: var(--card-bg); border-radius: 1rem; overflow: hidden; transition: transform 0.3s ease;">
                    <div class="card-header" style="padding: 1.5rem;">
                        <div class="card-header-info">
                            <div class="left-info"><i data-lucide="file-text" style="color:var(--blue);"></i> <span class="difficulty" style="color: var(--yellow); ">Medium</span></div>
                            <div class="right-info"><i data-lucide="trophy" style="color: gold;"></i> <span>150 pts</span></div>
                        </div>
                        <h3 style="color: white; font-size: 1.125rem; margin-bottom: 0.75rem;">XSS Attack Simulation</h3>
                        <div class="meta" style="display: flex; gap: 1rem; color: var(--text-secondary); font-size: 0.875rem;">
                            <span class="category" style="background: rgba(59, 130, 246, 0.2); padding: 0.25rem 0.75rem; border-radius: 0.5rem;">Web</span>
                            <div style="display: flex; align-items: center; gap: 0.5rem;"><i data-lucide="timer"></i><span class="time" style="color: var(--text-secondary);">45 min</span></div>
                        </div>
                    </div>
                    <p class="description" style="padding: 1.5rem; color: var(--text-secondary); font-size: 0.875rem; line-height: 1.4;">
                        Simulate cross-site scripting attacks and learn defensive techniques. Find vulnerabilities in a simulated web application...
                    </p>
                    <div class="tags">
                        <span class="tag">WEB</span>
                        <span class="tag">JAVASCRIPT</span>
                        <span class="tag">CLIENT SIDE</span>
                    </div>
                    <div class="stats-table" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; padding: 1.5rem;">
                        <div class="stat" style="display: flex; flex-direction: row; gap: 0.25rem;">
                            <i data-lucide="user" style="gap: 0.5rem;"></i>
                            <span class="value" id="solves-count" style="color: white; font-size: 1rem; font-weight: 500;">287 solves</span>
                        </div>
                    </div>
                    <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-top: 1px solid var(--border);">
                        <button class="badge hack-now" id="hack-now-btn">HACK NOW</button>
                        <div class="status solved" id="status" style="display: none;">
                            <i data-lucide="check-circle"></i>
                            <span>Solved</span>
                        </div>
                    </div>
                </div>   

                <!-- 3rd challenge -->
                <div class="cyber-card" 
                    data-title="Password Cracking"
                    data-description="Use various techniques to crack encrypted passwords. Apply dictionary attacks, rainbow tables, and brute force methods to recover credentials."
                    data-difficulty="Medium"
                    data-category="Cryptography"
                    data-time="45 min"
                    data-points="200"
                    data-hint="Hint"
                    data-tags="cryptography, password, cracking, security"
                    style="background: var(--card-bg); border-radius: 1rem; overflow: hidden; transition: transform 0.3s ease;">
                    <div class="card-header" style="padding: 1.5rem;">
                        <div class="card-header-info">
                            <div class="left-info"><i data-lucide="file-text" style="color:var(--blue);"></i> <span class="difficulty" style="color: var(--yellow); ">Medium</span></div>
                            <div class="right-info"><i data-lucide="trophy" style="color: gold;"></i> <span>200 pts</span></div>
                        </div>
                        <h3 style="color: white; font-size: 1.125rem; margin-bottom: 0.75rem;">Password Cracking</h3>
                        <div class="meta" style="display: flex; gap: 1rem; color: var(--text-secondary); font-size: 0.875rem;">
                            <span class="category" style="background: rgba(59, 130, 246, 0.2); padding: 0.25rem 0.75rem; border-radius: 0.5rem;">Cryptography</span>
                            <div style="display: flex; align-items: center; gap: 0.5rem;"><i data-lucide="timer"></i><span class="time" style="color: var(--text-secondary);">45 min</span></div>
                        </div>
                    </div>
                    <p class="description" style="padding: 1.5rem; color: var(--text-secondary); font-size: 0.875rem; line-height: 1.4;">
                        Use various techniques to crack encrypted passwords. Apply dictionary attacks, rainbow tables, and brute force methods to recover credentials...
                    </p>
                    <div class="tags">
                        <span class="tag">CRYPTOGRAPHY</span>
                        <span class="tag">PASSWORD</span>
                        <span class="tag">CRACKING</span>
                    </div>
                    <div class="stats-table" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; padding: 1.5rem;">
                        <div class="stat" style="display: flex; flex-direction: row; gap: 0.25rem;">
                            <i data-lucide="user" style="gap: 0.5rem;"></i>
                            <span class="value" id="solves-count" style="color: white; font-size: 1rem; font-weight: 500;">287 solves</span>
                        </div>
                    </div>
                    <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-top: 1px solid var(--border);">
                        <button class="badge hack-now" id="hack-now-btn">HACK NOW</button>
                        <div class="status solved" id="status" style="display: none;">
                            <i data-lucide="check-circle"></i>
                            <span>Solved</span>
                        </div>
                    </div>
                </div> 
                
            </div>
        </section>
    </div>
  </section>




  <!-- Modal pour Hack Now -->
  <div id="challenge-modal" class="modal">
    <div class="modal-content">
       <h2><strong>Challenge Details</strong></h2>
       <p class="subheading">
        Solve the challenge to earn points and improve your hacking skills
       </p>
      <!-- Bouton de fermeture --> 
      <span class="close-modal">&times;</span>
      <!-- Informations du challenge -->
      <div class="challenge-info">
        <span class="challenge-difficulty" id="challenge-difficulty"></span>
        <span class="challenge-category" id="challenge-category"></span>
        <span class="challenge-points" id="challenge-points"></span>
      </div>
      <!-- Titre du challenge -->
      <h2 class="challenge-title" id="challenge-title">Challenge Title</h2>

      <!-- Nombre de resolutions -->
      <p>
        Solved by 
        <span class="challenge-solved" id="challenge-solved">432</span> users
      </p>

      <!-- Description du challenge -->
      <p class="challenge-description" id="challenge-description">Challenge description goes here.</p>

      <!-- Tags -->
      <div class="tags">
        <span class="tag" id="challenge-tags"></span>
      </div>

      <!-- Resources -->
      <div class="resources">
        <button class="resource-btn"><i data-lucide="download"></i>Download files</button>
        <button class="resource-btn"><i data-lucide="play-circle"></i>Launch instance</button>
      </div>
      
      <!-- Hint -->
      <div class="hint">
        <strong>Hint: </strong> <span id="challenge-hint"></span>
      </div>
      <div class="submit-flag">
        <input type="text" placeholder="Flag{...}">
        <button id="submit-flag-btn" class="submit-flag-btn">Submit</button>
      </div>
    </div> 
  </div>


   


</body>
</html>
