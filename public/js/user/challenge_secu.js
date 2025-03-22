document.addEventListener("DOMContentLoaded", function () {
    // INITIALISATION DES ICONES LUCIDE
    lucide.createIcons();


    // --- Gestion des filtres dans le sidebar ---

    // Sélectionner tous les groupes de filtres (difficulté, catégorie, statut)
    const filterGroups = document.querySelectorAll(".filter-buttons[data-type]");
    // Bouton pour réinitialiser les filtres
    const clearFiltersBtn = document.querySelector(".clear-filters");
    // Champ de recherche
    const searchInput = document.querySelector('.search-input-wrapper input');
    // Conteneur des cartes de challenges
    const cardsContainer = document.querySelector('.challenge-grid');
    // Bouton de tri
    const sortBtn = document.querySelector('.sort-btn');
    // Options de tri (par date, nombre de solutions, difficulté)
    const sortOptions = document.querySelectorAll('.sort-option');
    // Modale pour afficher les détails d'un challenge
    const modal = document.getElementById("challenge-modal");
    // Bouton pour fermer la modale
    const closeButton = document.querySelector(".close-modal");
    // Boutons "Hack Now" pour ouvrir la modale depuis une carte
    const openButtons = document.querySelectorAll(".hack-now");

    // Gestion des filtres
    filterGroups.forEach(group => {
        group.addEventListener("click", function (e) {
            // Vérifie si l'élément cliqué est un bouton de filtre
            const btn = e.target.closest(".filter-btn");
            // Désactive tous les boutons du groupe avant d'activer le bouton sélectionné
            group.querySelectorAll(".filter-btn").forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
            // Applique les filtres après sélection
            applyFilters();
        });
    });

    // Gestion du bouton pour réinitialiser les filtres
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener("click", function () {
        filterGroups.forEach(group => {
            group.querySelectorAll(".filter-btn").forEach(b => b.classList.remove("active"));
        });
        applyFilters(); // Réapplique les filtres (réaffiche tout)
    });
}

    // Fonction pour appliquer les filtres sélectionnés
    function applyFilters() {
        const filters = {};
        // Parcours des groupes de filtres pour récupérer les valeurs sélectionnées
        filterGroups.forEach(group => {
            const type = group.getAttribute("data-type");
            const activeBtn = group.querySelector(".filter-btn.active");
            if (activeBtn) {
                filters[type] = activeBtn.textContent.trim().toLowerCase();
            }
        });

        // Filtrage des cartes de challenge
        document.querySelectorAll(".cyber-card").forEach(card => {
            let show = true;
            // Vérifie si la carte correspond aux filtres sélectionnés
        if (filters.difficulty && card.getAttribute("data-difficulty")?.toLowerCase() !== filters.difficulty) {
            show = false;
        }
        if (filters.category && card.getAttribute("data-category")?.toLowerCase() !== filters.category) {
            show = false;
        }
        if (filters.status) {
            const cardStatus = card.getAttribute("data-solved");
            if ((filters.status === "solved" && cardStatus !== "true") || (filters.status === "unsolved" && cardStatus !== "false")) {
                show = false;
            }
        }
        // Affiche ou masque la carte selon le résultat des filtres
        card.style.display = show ? "" : "none";
    });
    }

    // Gestion de la recherche en temps réel
    if (searchInput) {
        searchInput.addEventListener('input', debounce(() => {
            const searchTerm = searchInput.value.toLowerCase();
        document.querySelectorAll('.cyber-card').forEach(card => {
            const title = card.querySelector('h3')?.textContent.toLowerCase() || "";
            const description = card.querySelector('p')?.textContent.toLowerCase() || "";
            // Affiche la carte si le titre ou la description contient le terme recherché
            card.style.display = title.includes(searchTerm) || description.includes(searchTerm) ? '' : 'none';
        });
    }, 300)); // Ajoute un délai pour éviter de déclencher trop d'événements
    }

    // Fonction debounce pour éviter trop d'appels lors de la saisie rapide
    function debounce(func, delay) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => func(...args), delay);
        };
    }

    // Gestion du tri des challenges
    if (sortBtn) {
        sortOptions.forEach(option => {
            option.addEventListener("click", () => {
                sortChallenges(option.textContent);
            });
        });
    }

    // Fonction pour trier les challenges selon le critère sélectionné
    function sortChallenges(sortBy) {
        const challengesArray = Array.from(document.querySelectorAll(".cyber-card"));
    
        challengesArray.sort((a, b) => {
            if (sortBy === "Latest") {
                // Trie par date (du plus récent au plus ancien)
                return new Date(b.dataset.date) - new Date(a.dataset.date);
            }
            if (sortBy === "Most Solved") {
            // Trie par nombre de solutions (du plus résolu au moins résolu)
            return b.querySelector(".stat .value").textContent - a.querySelector(".stat .value").textContent;
        }
        if (sortBy === "Difficulty") {
            // Trie par difficulté croissante
            return a.dataset.difficulty - b.dataset.difficulty;
        }
    });

    // Vide le conteneur et ajoute les challenges triés
    cardsContainer.innerHTML = "";
    challengesArray.forEach(challenge => cardsContainer.appendChild(challenge));
    }


    
});




    // Délégation d'événements pour divers boutons
    document.body.addEventListener('click', (e) => {
        if (e.target.matches('.view-btn')) {
            const challengeTitle = e.target.closest('.challenge-card').querySelector('h3').textContent;
            alert(`Viewing challenge: ${challengeTitle}`);
        } else if (e.target.matches('.badge')) {
            console.log('Challenge started!');
        } else if (e.target.matches('.tag')) {
            console.log(`Filtering by ${e.target.textContent}`);
        } else if (e.target.matches('.filter-btn')) {
            document.querySelector('.filter-btn.active')?.classList.remove('active');
            e.target.classList.add('active');
            console.log(`Filtering by ${e.target.textContent}`);
        }
    });

    // Effet hover sur les cartes
    document.querySelectorAll('.cyber-card').forEach(card => {
        card.style.transition = 'transform 0.2s ease-in-out';
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-5px)';
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
        });
    });

    
    // Charger le classement des hackers
    fetch("/HACKATHON_ESGIS/public/api/get_top_hackers.php")
        .then(response => response.json())
        .then(data => {
            let list = document.getElementById("top-hackers");
            list.innerHTML = "";
            data.forEach((hacker, index) => {
                let li = document.createElement("li");
                li.textContent = `${index + 1}. ${hacker.username} - ${hacker.points} pts`;
                list.appendChild(li);
            });
        })
        .catch(error => console.error("Erreur lors du chargement des hackers :", error));
    
    
    // --- Gestion de la modale ---
    const modal = document.getElementById("challenge-modal");
    const openButtons = document.querySelectorAll(".hack-now");
    const closeButton = document.querySelector(".close-modal");
    
    // Vérification des éléments essentiels de la modale
    if (!modal || !closeButton) {
        console.error("Un élément de la modale est introuvable !");
    }
    if (openButtons.length === 0) {
        console.error("Aucun bouton 'HACK NOW' trouvé !");
    }

    // Ouvrir la modale au clic sur un bouton "HACK NOW"
    openButtons.forEach(button => {
        button.addEventListener("click", function (e) {
            e.preventDefault();
            const card = button.closest(".cyber-card");
            if (!card) {
                console.error("Carte du challenge introuvable !");
            }

            // Récupérer les informations depuis les data-attributes de la carte 
            const title = card.getAttribute("data-title") || (card.querySelector("h3") ? card.querySelector("h3").textContent : "");
            const description = card.getAttribute("data-description") || (card.querySelector(".description") ? card.querySelector(".description").textContent : "");
            const difficulty = card.getAttribute("data-difficulty") || "Difficulty";
            const category = card.getAttribute("data-category") || "Category";
            const time = card.getAttribute("data-time") || "Time";
            const points = card.getAttribute("data-points") || "Points";
            const hint = card.getAttribute("data-hint") || "Hint";
            const tagsContainer = document.getElementById("challenge-tags");



            // Créer un objet qui contient les détails du challenge
            const challengeDetails = {
                title: title,
                description: description,
                difficulty: difficulty,
                category: category,
                time: time,
                points: points,
                hint: hint
            };
            
            // Afficher les informations dans la modale
            const modalElements = {
                title: document.getElementById("challenge-title"),
                description: document.getElementById("challenge-description"),
                difficulty: document.getElementById("challenge-difficulty"),
                category: document.getElementById("challenge-category"),
                time: document.getElementById("challenge-time"),
                points: document.getElementById("challenge-points"),
                hint: document.getElementById("challenge-hint")
            };

            // Mettre à jour les éléments de la modale avec les données récupérées
            Object.entries(modalElements).forEach(([key, element]) => {
                if (element) {
                    element.textContent = challengeDetails[key] || '';
                }
            });
            
            // Afficher les tags
            tagsContainer.innerHTML = "";
            const tags = card.getAttribute("data-tags").split(",");
            tags.forEach(tag => {
                const tagElement = document.createElement("span");
                tagElement.textContent = tag;
                tagsContainer.appendChild(tagElement);
            });

            modal.style.display = "flex"; // Afficher la modale en mode flex
        });
    });

    // Fermer la modale en cliquant sur la croix
    closeButton.addEventListener("click", function () {
        console.log("Fermeture de la modale via le bouton X");
        modal.style.display = "none";
    });

    // Fermer la modale en cliquant en dehors du contenu
    window.addEventListener("click", function (e) {
        if (e.target === modal) {
            console.log("Clique en dehors de la modale, fermeture.");
            modal.style.display = "none";
        }
    });




    
    // Fonction pour récupérer et mettre à jour le nombre de résolution de challenge
    function updateSolvesCount() {
        $.ajax({
            url: '/HACKATHON_ESGIS/public/api/get_solves.php',  // Remplacer ceci par l'URL de l'API.............................................
            method: 'GET',
            success: function(response) {
                // Assurez-vous que la réponse contient le nombre de solves
            if (response && response.solves) {
                $('#solves-count').text(response.solves + ' solves');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur de récupération des données:', error);
        }
    });
    }

    // Appel initial pour obtenir le nombre de résolutions
    updateSolvesCount();

    // Actualiser les résolutions toutes les 10 secondes
    setInterval(updateSolvesCount, 10000);





   // Gestion du statut résolu pour chaque carte
    document.querySelectorAll('.cyber-card').forEach(card => {
        const hackNowButton = card.querySelector('.hack-now');
        const status = card.querySelector('.status.solved');
        
        // Vérifier si le challenge est résolu
        const isSolved = card.getAttribute("data-solved") === "true";
    
    // Mettre à jour l'affichage
    if (isSolved) {
        hackNowButton.style.display = 'none';
        status.style.display = 'flex';
    } else {
        hackNowButton.style.display = 'inline-block';
        status.style.display = 'none';
    }
    });
    


