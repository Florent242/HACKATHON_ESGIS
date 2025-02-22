<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Resources</title>
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/admin/ressources.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/admin/header.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/dist/output.css">
</head>
<body>
<?php require_once '../includes/admin/header.php'; ?>

    <main>
        <div class="resources-header">
            <h1>Learning Resources</h1>
            <p>Everything you need to excel in challenges and hackathons</p>
        </div>

        <div class="resources-grid">
            <!-- Development Guides Card -->
            <div class="resource-card">
                <div class="card-header">
                    <div class="icon-wrapper dev-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 18l6-6-6-6M8 6l-6 6 6 6"/>
                        </svg>
                    </div>
                    <h2>Development Guides</h2>
                </div>
                <p>Comprehensive guides for web development, from basics to advanced topics</p>
                <ul class="resource-links">
                    <li><a href="#">React Fundamentals</a></li>
                    <li><a href="#">API Integration</a></li>
                    <li><a href="#">State Management</a></li>
                    <li><a href="#">Testing Strategies</a></li>
                </ul>
                <a href="#" class="explore-btn">Explore Development Guides</a>
            </div>

            <!-- Security Resources Card -->
            <div class="resource-card">
                <div class="card-header">
                    <div class="icon-wrapper security-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </div>
                    <h2>Security Resources</h2>
                </div>
                <p>Learn about cybersecurity, penetration testing, and secure coding practices</p>
                <ul class="resource-links">
                    <li><a href="#">OWASP Top 10</a></li>
                    <li><a href="#">Penetration Testing</a></li>
                    <li><a href="#">Security Tools</a></li>
                    <li><a href="#">Best Practices</a></li>
                </ul>
                <a href="#" class="explore-btn">Explore Security Resources</a>
            </div>

            <!-- Documentation Card -->
            <div class="resource-card">
                <div class="card-header">
                    <div class="icon-wrapper doc-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                    </div>
                    <h2>Documentation</h2>
                </div>
                <p>Official documentation and references for various technologies</p>
                <ul class="resource-links">
                    <li><a href="#">API References</a></li>
                    <li><a href="#">Framework Guides</a></li>
                    <li><a href="#">Security Standards</a></li>
                    <li><a href="#">Code Examples</a></li>
                </ul>
                <a href="#" class="explore-btn">Explore Documentation</a>
            </div>

            <!-- Learning Paths Card -->
            <div class="resource-card">
                <div class="card-header">
                    <div class="icon-wrapper path-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                        </svg>
                    </div>
                    <h2>Learning Paths</h2>
                </div>
                <p>Structured learning paths for different skill levels and interests</p>
                <ul class="resource-links">
                    <li><a href="#">Beginner Track</a></li>
                    <li><a href="#">Advanced Development</a></li>
                    <li><a href="#">Security Expert</a></li>
                    <li><a href="#">Full Stack Path</a></li>
                </ul>
                <a href="#" class="explore-btn">Explore Learning Paths</a>
            </div>
        </div>
    </main>
</body>
</html>