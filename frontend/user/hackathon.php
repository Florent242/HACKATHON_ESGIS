<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Hackathons</title>
    <link rel="stylesheet" href="/css/styles/user/hackaton.css">
    <link rel="stylesheet" href="/css/styles/user/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
</head>
<body>
    <?php require_once '../includes/user/header.php'; ?>

    <main>
        <div class="hackathons-container">
            <div class="hackathons-header">
                <div>
                    <h1>Upcoming Hackathons</h1>
                    <p>Compete in exciting hackathons and win amazing prizes</p>
                </div>
                <button class="host-hackathon">Host a Hackathon</button>
            </div>

            <div class="hackathons-grid">
                <!-- First Hackathon Card -->
                <div class="hackathon-card">
                    <div class="card-badge upcoming">Upcoming</div>
                    <h2>EsgisHub Global Hackathon 2024</h2>
                    <p>Join the biggest hackathon of the year! Build innovative solutions for real-world problems.</p>
                    
                    <div class="hackathon-details">
                        <div class="detail-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            500 participants
                        </div>
                        <div class="detail-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            2024-03-15
                        </div>
                        <div class="detail-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                            $10,000
                        </div>
                    </div>
                    
                    <button class="view-details">View Details</button>
                </div>

                <!-- Second Hackathon Card -->
                <div class="hackathon-card">
                    <div class="card-badge registration">Registration Open</div>
                    <h2>Security Challenge Week</h2>
                    <p>A week-long event focused on cybersecurity challenges and penetration testing.</p>
                    
                    <div class="hackathon-details">
                        <div class="detail-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            250 participants
                        </div>
                        <div class="detail-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            2024-04-01
                        </div>
                        <div class="detail-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                            $5,000
                        </div>
                    </div>
                    
                    <button class="view-details">View Details</button>
                </div>
            </div>
        </div>
    </main>
</body>
</html>