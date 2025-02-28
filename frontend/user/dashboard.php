<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Challenge Yourself</title>
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/user/dashboard.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/user/header.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/dist/output.css">
    <script defer src="/HACKATHON_ESGIS/public/js/user/dashboard.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <!-- Navigation -->
    <?php require_once '../includes/user/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1 class="hero-title fade-in">
                Challenge Yourself with
                <span class="highlight">EsgisHub</span>
            </h1>
            <p class="hero-subtitle fade-in">
                Join our community of developers and security enthusiasts in building amazing projects,
                mastering new technologies, and discovering cybersecurity challenges.
            </p>
            <div class="hero-buttons">
                <button class="btn btn-primary fade-in">
                    Start Your Journey
                    <i data-lucide="arrow-right"></i>
                </button>
                <button class="btn btn-secondary fade-in">
                    Explore Challenges
                </button>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="stats-container">
            <div class="stat-item">
                <h2><span>1200+</span></h2>
                <p>Active Members</p>
            </div>
            <div class="stat-item">
                <h2><span>50+</span></h2>
                <p>Weekly Challenges</p>
            </div>
            <div class="stat-item">
                <h2>$<span>50</span>K</h2>
                <p>In Prizes</p>
            </div>
            <div class="stat-item">
                <h2><span>2</span></h2>
                <p>Challenge Types</p>
            </div>
        </div>
    </section>

    <!-- Featured Challenges Section -->
    <section class="featured-challenges">
        <div class="section-header">
            <h2>Featured Challenges</h2>
            <p>From web development to cybersecurity, find your next challenge</p>
        </div>

        <div class="challenges-grid">
            <!-- Challenge Card 1 -->
            <div class="challenge-card">
                <div class="card-header">
                    <span class="difficulty medium">Medium</span>
                    <h3>Build a Real-time Chat App</h3>
                    <span class="category">Development</span>
                </div>
                <p class="description">Create a modern chat application using WebSocket technology and React</p>
                <div class="technologies">
                    <span class="tech-tag">React</span>
                    <span class="tech-tag">WebSocket</span>
                    <span class="tech-tag">Firebase</span>
                </div>
                <div class="card-footer">
                    <div class="stats">
                        <span class="participants">128 participants</span>
                        <span class="time-left">7 days left</span>
                    </div>
                    <button class="view-challenge">View Challenge</button>
                </div>
            </div>

            <!-- Challenge Card 2 -->
            <div class="challenge-card">
                <div class="card-header">
                    <span class="difficulty hard">Hard</span>
                    <h3>Web Application Security Audit</h3>
                    <span class="category">Hacking</span>
                </div>
                <p class="description">Conduct a security assessment of a web application and identify vulnerabilities</p>
                <div class="technologies">
                    <span class="tech-tag">Security</span>
                    <span class="tech-tag">Pentesting</span>
                    <span class="tech-tag">OWASP</span>
                </div>
                <div class="card-footer">
                    <div class="stats">
                        <span class="participants">256 participants</span>
                        <span class="time-left">14 days left</span>
                    </div>
                    <button class="view-challenge">View Challenge</button>
                </div>
            </div>

            <!-- Challenge Card 3 -->
            <div class="challenge-card">
                <div class="card-header">
                    <span class="difficulty easy">Easy</span>
                    <h3>Portfolio Website</h3>
                    <span class="category">Development</span>
                </div>
                <p class="description">Design and build a responsive portfolio website with modern animations</p>
                <div class="technologies">
                    <span class="tech-tag">HTML</span>
                    <span class="tech-tag">CSS</span>
                    <span class="tech-tag">JavaScript</span>
                </div>
                <div class="card-footer">
                    <div class="stats">
                        <span class="participants">64 participants</span>
                        <span class="time-left">5 days left</span>
                    </div>
                    <button class="view-challenge">View Challenge</button>
                </div>
            </div>
        </div>
    </section>
</body>
</html>