/**
 * Formate une date au format "jour mois année" (ex: 25 juin 2025)
 * @param {string} dateString - La date en format ISO (YYYY-MM-DD HH:MM:SS)
 * @returns {string} - La date formatée
 */
function formatDate(dateString) {
    if (!dateString) return "Date non définie";
    const date = new Date(dateString);
    const options = { day: 'numeric', month: 'long', year: 'numeric' };
    return date.toLocaleDateString('fr-FR', options);
}

/**
 * Crée une carte HTML pour un hackathon dans la section "Choisissez votre défi"
 * @param {object} hackathon - L'objet hackathon
 * @returns {string} - Le HTML de la carte
 */
function createHackathonCard(hackathon) {
    const { id, name, description, start_date, end_date } = hackathon;
    // Utilise une icône Lucide par défaut ou une icône basée sur le nom/type
    const iconName = name.toLowerCase().includes('sec') ? 'lock' : 'code';
    const iconColor = name.toLowerCase().includes('sec') ? '#3b82f6' : '#ffffff';
    const formattedStartDate = formatDate(start_date);
    const formattedEndDate = formatDate(end_date);

    return `
        <div class="card rounded-xl shadow-lg p-6 flex-1 flex flex-col justify-between items-center m-3 border border-[var(--border)] hover:shadow-xl hover:scale-[1.02] transition-all duration-300 transform-gpu">
            <i data-lucide="${iconName}" class="w-10 h-10 mb-4 text-[var(--primary)]" style="color: ${iconColor}"></i>
            <h3 class="text-xl font-bold text-[var(--text)] mb-3 text-center">${name}</h3>
            <p class="text-[var(--text-secondary)] mb-4 text-center text-sm">${description}</p>
            <p class="text-[var(--primary)] font-medium mb-5 text-sm">
                <i data-lucide="calendar" class="w-4 h-4 inline-block mr-1"></i>
                Du ${formattedStartDate} au ${formattedEndDate}
            </p>
            <a href="/user/hackathon/overview/${id}" class="w-full text-center py-2.5 px-4 rounded-lg bg-gradient-to-r from-[var(--blue)] to-[var(--blue-dark)] text-white font-medium hover:shadow-lg hover:shadow-blue-500/30 hover:scale-[1.02] transition-all duration-300 transform-gpu">
                Découvrir
            </a>
        </div>
    `;
}

/**
 * Crée une carte HTML pour la timeline d'un hackathon
 * @param {object} hackathon - L'objet hackathon
 * @returns {string} - Le HTML de la carte timeline
 */
function createTimelineCard(hackathon) {
    const { name, start_date, end_date } = hackathon;
    const formattedStartDate = formatDate(start_date);
    const formattedEndDate = formatDate(end_date);
    const launchDate = new Date(start_date);
    const presentationDate = new Date(end_date);

    return `
        <div class="card rounded-xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-xl hover:scale-[1.02] hover:-translate-y-1 flex-1 m-3 border border-[var(--border)] transform-gpu">
            <div class="p-6">
                <h3 class="text-2xl font-bold text-[var(--text)] mb-6 pb-4 border-b border-[var(--border)]">${name}</h3>
                <ul class="space-y-4">
                    <li class="flex items-start p-3 rounded-lg hover:bg-[var(--card-hover)] transition-colors duration-200">
                        <div class="bg-[var(--blue-opac)] p-2 rounded-lg mr-4">
                            <i data-lucide="calendar" class="w-5 h-5 text-[var(--blue)]"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-[var(--text)]">Inscription</p>
                            <p class="text-sm text-[var(--text-secondary)]">Jusqu'au ${formattedStartDate}</p>
                        </div>
                    </li>
                    <li class="flex items-start p-3 rounded-lg hover:bg-[var(--card-hover)] transition-colors duration-200">
                        <div class="bg-[var(--blue-opac)] p-2 rounded-lg mr-4">
                            <i data-lucide="rocket" class="w-5 h-5 text-[var(--blue-light)]"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-[var(--text)]">Lancement</p>
                            <p class="text-sm text-[var(--text-secondary)]">${launchDate.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long' })} • ${launchDate.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}</p>
                        </div>
                    </li>
                    <li class="flex items-start p-3 rounded-lg hover:bg-[var(--card-hover)] transition-colors duration-200">
                        <div class="bg-[var(--blue-opac)] p-2 rounded-lg mr-4">
                            <i data-lucide="trophy" class="w-5 h-5 text-[var(--yellow)]"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-[var(--text)]">Présentation</p>
                            <p class="text-sm text-[var(--text-secondary)]">${presentationDate.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long' })} • ${presentationDate.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    `;
}

/**
 * Récupère les hackathons depuis l'API et les affiche
 */

async function fetchAndDisplayHackathons() {
    const hackathonsContainer = document.getElementById('hackathons-container');
    const timelineContainer = document.getElementById('timeline-container');

    if (!hackathonsContainer || !timelineContainer) return;

    try {
        const response = await apiRequest('/hackathons');

        if (!response.success) {
            throw new Error(`Erreur HTTP: ${response.status}`);

        }
        const result = response;



        const hackathons = result.data || [];

        // Vider les conteneurs
        hackathonsContainer.innerHTML = '';
        timelineContainer.innerHTML = '';

        if (hackathons.length > 0) {
            hackathons.forEach(hackathon => {
                hackathonsContainer.innerHTML += createHackathonCard(hackathon);
                timelineContainer.innerHTML += createTimelineCard(hackathon);
            });
            // Rafraîchir les icônes Lucide après l'ajout du contenu
            if (window.lucide) {
                lucide.createIcons();
            }
            displayCTABtns(hackathons);
        } else {
            hackathonsContainer.innerHTML = '<p class="text-center text-secondary col-span-full">Aucun hackathon trouvé pour le moment.</p>';
        }

    } catch (error) {
        console.error("Erreur lors de la récupération des hackathons:", error);
        hackathonsContainer.innerHTML = `<p class="text-center text-red-500 col-span-full">Impossible de charger les événements.</p>`;
    }
}

// Animation d'apparition au scroll (fade/slide)
function revealOnScroll() {
    const reveals = document.querySelectorAll(".reveal");
    for (const el of reveals) {
        const windowHeight = window.innerHeight;
        const elementTop = el.getBoundingClientRect().top;
        const revealPoint = 100;
        if (elementTop < windowHeight - revealPoint) {
            el.classList.add("opacity-100", "translate-y-0");
            el.classList.remove("opacity-0", "translate-y-8");
        } else {
            el.classList.remove("opacity-100", "translate-y-0");
            el.classList.add("opacity-0", "translate-y-8");
        }
    }
}


function displayCTABtns(hackathons) {
    const ctaContainer = document.getElementById('cta-buttons');
    if (!ctaContainer) return;
    ctaContainer.innerHTML = ''; // Vide le conteneur

    // On ne prend que les deux premiers hackathons pour l'instant
    hackathons.slice(0, 2).forEach((hackathon, idx) => {
        const isSec = hackathon.name.toLowerCase().includes('sec');
        const iconName = isSec ? 'lock' : 'code';
        // Style dynamique
        let btnClass, textColor, hoverClass;
        if (isSec) {
            btnClass = "bg-gradient-to-r from-[#030B20] to-[#0f172a] text-[var(--blue)] border border-[var(--blue)]";
            textColor = "text-[var(--blue)]";
            hoverClass = "hover:from-[var(--blue)] hover:to-[var(--blue-dark)] hover:text-white hover:scale-105";
        } else {
            btnClass = "bg-gradient-to-r from-[var(--blue)] to-[var(--blue-dark)] text-white";
            textColor = "text-white";
            hoverClass = "hover:shadow-xl hover:scale-105 hover:shadow-blue-500/30";
        }
        ctaContainer.innerHTML += `
        <button
        class="flex items-center justify-center gap-3 px-8 py-3 rounded-full font-bold text-lg shadow-lg transition-all duration-300 transform-gpu ${btnClass} ${hoverClass}"
        onclick="window.location.href='/user/hackathon/overview/${hackathon.id}'"
        >
        <i data-lucide="${iconName}" class="w-5 h-5"></i>
        Participer au ${hackathon.name}
        </button>
        `;
    });
    // Rafraîchir les icônes Lucide après l'ajout des boutons
    if (window.lucide) {
        lucide.createIcons();
    }
}

window.addEventListener("scroll", revealOnScroll);
window.addEventListener("DOMContentLoaded", () => {
    revealOnScroll();
    fetchAndDisplayHackathons();

    // Scroll vers la section "Choisissez votre défi"
    const btn = document.getElementById("scrollToEvents");
    const defis = document.getElementById("defis");
    if (btn && defis) {
        btn.addEventListener("click", () => {
            defis.scrollIntoView({ behavior: "smooth" });
        });
    }
});
