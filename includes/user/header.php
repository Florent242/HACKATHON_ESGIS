<script defer src="/HACKATHON_ESGIS/public/js/lucide.js"></script>
<script defer src="/HACKATHON_ESGIS/public/js/user/header.js"></script>
<header>
        <div class="header-container">
            <div class="logo-nav">
                <div class="logo">
                    <div class="logo-circle">E</div>
                    <span>EsgisHub</span>
                </div>
                <a href="/HACKATHON_ESGIS/public/user">Dashboard</a>
                <div class="nav-container">
                    <nav class="main-nav">
                        <!-- verifie et attribut la classe active au lien correspondant -->
                        <li data-item="0">Événements <i data-lucide="chevron-down"></i></li>
                        <li data-item="1">Communauté <i data-lucide="chevron-down"></i></li>
                        <li data-item="2">Resources <i data-lucide="chevron-down"></i></li>
                    </nav>  
                    <nav class="header-dropdown">
                        <div class="dropdown-container">
                            <div class="dropdown">
                                <ul class="dropdown-item" data-item="0">
                                    <a href="/HACKATHON_ESGIS/public/user/challenges">
                                        <li>
                                            Challenges
                                        </li>
                                    </a>
                                    <a href="/HACKATHON_ESGIS/public/user/hackathon">
                                        <li>
                                            Hackathons
                                        </li>
                                    </a>
                                    <a href="/HACKATHON_ESGIS/public/user/workshop">
                                        <li>
                                            Workshop
                                        </li>
                                    </a>
                                </ul>
                                <ul class="dropdown-item" data-item="1">
                                    <a href="/HACKATHON_ESGIS/public/user/teams">
                                        <li>
                                            Teams
                                        </li>
                                    </a>
                                    <a href="/HACKATHON_ESGIS/public/user/mentors">
                                        <li>
                                            Mentors
                                        </li>
                                    </a>
                                    <a href="/HACKATHON_ESGIS/public/user/leaderboard">
                                        <li>
                                            Leaderboard
                                        </li>
                                    </a>
                                </ul>
                                <ul class="dropdown-item" data-item="2">
                                    <a href="/HACKATHON_ESGIS/public/user/documentation">
                                        <li>
                                            Documentation
                                        </li>
                                    </a>
                                    <a href="/HACKATHON_ESGIS/public/user/blog">
                                        <li>
                                            Blog
                                        </li>
                                    </a>
                                    <a href="/HACKATHON_ESGIS/public/user/faq">
                                        <li>
                                            FAQ
                                        </li>
                                    </a>
                                </ul>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
            <div class="header-actions">
                <div class="notification-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                </div>
                <div class="profile-btn"><i data-lucide="circle-user"></i></div>
            </div>
        </div>
    </header>