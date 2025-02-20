<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>EsgisHub - Challenges</title>
        <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/challenge.css">
        <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/header.css">
        <!-- Lucide Icons -->
        <script defer src="/HACKATHON_ESGIS/public/js/challenge.js"></script>
        <script src="https://unpkg.com/lucide@latest"></script>
    </head>
    <body>
        <!-- Header -->
        <?php include '../includes/header.php'; ?>

        <!-- Main Content -->
        <main class="container">
        <section class="challenges-header">
            <h1>Active Challenges</h1>
            <p>Choose from our curated list of development and security challenges</p>
            
            <div class="search-bar">
                <div class="search-input-wrapper">
                    <i data-lucide="search"></i>
                    <input type="text" placeholder="Search challenges...">
                </div>
                <button class="filter-btn">
                    <i data-lucide="sliders"></i>
                    Filters
                </button>
            </div>
        </section>

        <section class="challenges-grid">
            <!-- Chat App Challenge -->
            <div class="challenge-card">
                <div class="card-header">
                    <div class="difficulty medium">Medium</div>
                    <h3>Build a Real-time Chat App</h3>
                    <div class="category">Development</div>
                </div>
                <div class="card-content">
                    <p>Create a modern chat application using WebSocket technology and React</p>
                    <div class="tags">
                        <span>React</span>
                        <span>WebSocket</span>
                        <span>Firebase</span>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="stats">
                        <span>128 participants</span>
                        <span class="dot">•</span>
                        <span>7 days left</span>
                    </div>
                    <button class="view-btn">View Challenge</button>
                </div>
            </div>

            <!-- Security Audit Challenge -->
            <div class="challenge-card">
                <div class="card-header">
                    <div class="difficulty hard">Hard</div>
                    <h3>Web Application Security Audit</h3>
                    <div class="category">Hacking</div>
                </div>
                <div class="card-content">
                    <p>Conduct a security assessment of a web application and identify vulnerabilities</p>
                    <div class="tags">
                        <span>Security</span>
                        <span>Pentesting</span>
                        <span>OWASP</span>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="stats">
                        <span>256 participants</span>
                        <span class="dot">•</span>
                        <span>14 days left</span>
                    </div>
                    <button class="view-btn">View Challenge</button>
                </div>
            </div>

            <!-- Portfolio Challenge -->
            <div class="challenge-card">
                <div class="card-header">
                    <div class="difficulty easy">Easy</div>
                    <h3>Portfolio Website</h3>
                    <div class="category">Development</div>
                </div>
                <div class="card-content">
                    <p>Design and build a responsive portfolio website with modern animations</p>
                    <div class="tags">
                        <span>HTML</span>
                        <span>CSS</span>
                        <span>JavaScript</span>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="stats">
                        <span>64 participants</span>
                        <span class="dot">•</span>
                        <span>5 days left</span>
                    </div>
                    <button class="view-btn">View Challenge</button>
                </div>
            </div>
        </section>
    </main>

</body>
</html>