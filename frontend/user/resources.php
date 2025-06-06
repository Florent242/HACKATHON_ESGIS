<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Resources</title>
    <link rel="stylesheet" href="/css/styles/user/ressources.css">
    <link rel="stylesheet" href="/css/styles/user/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/lucide@latest"></script>

</head>

<body>
    <?php require_once '../includes/user/header.php'; ?>

    <main>
        <div class="resources-container">
            <div class="resources-header">
                <h1>Learning Resources</h1>
                <p>Everything you need to excel in challenges and hackathons</p>
            </div>

            <div class="stats-container">
                <div class="stat-card">
                    <i class="fas fa-book"></i>
                    <h3>9</h3>
                    <p>Total Resources</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3>1234</h3>
                    <p>Active Users</p>
                </div>
            </div>

            <div class="search-container">
                <input type="text" class="search-bar" id="searchInput" placeholder="Search resources...">
            </div>

            <div class="filters-container" id="filterTags">
                <span class="filter-tag active" data-category="all">All Levels</span>
                <span class="filter-tag" data-category="development">Development</span>
                <span class="filter-tag" data-category="security">Security</span>
                <span class="filter-tag" data-category="frontend">Frontend</span>
                <span class="filter-tag" data-category="backend">Backend</span>
                <span class="filter-tag" data-category="api">API</span>
                <span class="filter-tag" data-category="testing">Testing</span>
                <span class="filter-tag" data-category="devops">DevOps</span>
            </div>

            <div class="resources-grid" id="resourcesGrid">
                <!-- Resources will be dynamically generated -->
            </div>
        </div>
    </main>

    <script>
        const resources = [{
                title: "Development Guides",
                description: "Comprehensive guides for web development, from basics to advanced topics",
                tags: ["development", "frontend", "backend"],
                links: [{
                        name: "React Fundamentals",
                        url: "https://react.dev/learn"
                    },
                    {
                        name: "API Integration",
                        url: "https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API/Using_Fetch"
                    },
                    {
                        name: "State Management",
                        url: "https://redux.js.org/introduction/getting-started"
                    },
                    {
                        name: "Testing Strategies",
                        url: "https://www.softwaretestinghelp.com/types-of-software-testing/"
                    }
                ],
                exploreLink: "https://github.com/kamranahmedse/developer-roadmap"
            },
            {
                title: "Security Resources",
                description: "Learn about cybersecurity, penetration testing, and secure coding practices",
                tags: ["security", "testing", "devops"],
                links: [{
                        name: "OWASP Top 10",
                        url: "https://owasp.org/www-project-top-ten/"
                    },
                    {
                        name: "Penetration Testing",
                        url: "https://www.hackthebox.com/blog/what-is-penetration-testing"
                    },
                    {
                        name: "Security Tools",
                        url: "https://www.kali.org/tools/"
                    },
                    {
                        name: "Best Practices",
                        url: "https://www.sans.org/blog/security-best-practices/"
                    }
                ],
                exploreLink: "https://www.cybrary.it/catalog/"
            },
            {
                title: "Documentation",
                description: "Official documentation and references for various technologies",
                tags: ["api", "backend", "development"],
                links: [{
                        name: "API References",
                        url: "https://developer.mozilla.org/en-US/docs/Web/API"
                    },
                    {
                        name: "Framework Guides",
                        url: "https://reactjs.org/docs/getting-started.html"
                    },
                    {
                        name: "Security Standards",
                        url: "https://www.cisecurity.org/controls/cis-controls-list"
                    },
                    {
                        name: "Code Examples",
                        url: "https://github.com/public-apis/public-apis"
                    }
                ],
                exploreLink: "https://devdocs.io/"
            },
            {
                title: "Learning Paths",
                description: "Structured learning paths for different skill levels and interests",
                tags: ["frontend", "development"],
                links: [{
                        name: "Beginner Track",
                        url: "https://www.freecodecamp.org/learn/2022/responsive-web-design/"
                    },
                    {
                        name: "Advanced Development",
                        url: "https://fullstackopen.com/en/"
                    },
                    {
                        name: "Security Expert",
                        url: "https://www.offensive-security.com/pwk-oscp/"
                    },
                    {
                        name: "Full Stack Path",
                        url: "https://roadmap.sh/full-stack"
                    }
                ],
                exploreLink: "https://www.codecademy.com/catalog/all"
            }
        ];

        function displayResources(filteredResources = resources) {
            const grid = document.getElementById('resourcesGrid');
            grid.innerHTML = '';

            filteredResources.forEach(resource => {
                const card = document.createElement('div');
                card.className = 'resource-card';
                card.innerHTML = `
            <h2>${resource.title}</h2>
            <p>${resource.description}</p>
            <div class="resource-tags">
                ${resource.tags.map(tag => `<span class="resource-tag">${tag}</span>`).join('')}
            </div>
            <ul class="resource-links">
                ${resource.links.map(link => `<li><a href="${link.url}" target="_blank" rel="noopener noreferrer">${link.name}</a></li>`).join('')}
            </ul>
            <a href="${resource.exploreLink}" target="_blank" rel="noopener noreferrer" class="explore-btn">Explore ${resource.title}</a>
        `;
                grid.appendChild(card);
            });
        }

        // Filter management
        document.getElementById('filterTags').addEventListener('click', (e) => {
            if (e.target.classList.contains('filter-tag')) {
                document.querySelectorAll('.filter-tag').forEach(tag => tag.classList.remove('active'));
                e.target.classList.add('active');

                const category = e.target.dataset.category;
                if (category === 'all') {
                    displayResources();
                } else {
                    const filtered = resources.filter(resource =>
                        resource.tags.includes(category)
                    );
                    displayResources(filtered);
                }
            }
        });

        // Search management
        document.getElementById('searchInput').addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const filtered = resources.filter(resource =>
                resource.title.toLowerCase().includes(searchTerm) ||
                resource.description.toLowerCase().includes(searchTerm) ||
                resource.tags.some(tag => tag.toLowerCase().includes(searchTerm)) ||
                resource.links.some(link => link.name.toLowerCase().includes(searchTerm))
            );
            displayResources(filtered);
        });

        // Initial display
        displayResources();
    </script>

</body>

</html>