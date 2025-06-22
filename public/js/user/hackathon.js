console.log("hackathon.js chargé");
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
    // Utilise une icône par défaut ou une icône basée sur le nom/type si possible
    const icon = name.toLowerCase().includes('sec') ? '&#128274;' : '&lt;/&gt;';
    const formattedStartDate = formatDate(start_date);
    const formattedEndDate = formatDate(end_date);

    return `
        <div class="card p-8 flex-1 flex flex-col items-center m-2">
            <div class="text-3xl mb-2" style="color: var(--blue)">${icon}</div>
            <h3 class="text-2xl font-bold mb-2">${name}</h3>
            <p class="text-secondary mb-2 text-center">${description}</p>
            <p style="color: var(--blue)" class="mb-4">Du ${formattedStartDate} au ${formattedEndDate}</p>
            <a href="/user/challenge_dev.php?hackathon_id=${id}" class="btn w-full text-center">Découvrir</a>
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
        <div class="card p-8 flex-1 m-2">
            <h3 class="text-2xl font-bold mb-4">${name}</h3>
            <ul class="space-y-2">
                <li class="flex items-center">
                    <span class="mr-2">📅</span>Inscription
                    <span class="ml-auto text-secondary">Jusqu'au ${formattedStartDate}</span>
                </li>
                <li class="flex items-center">
                    <span class="mr-2">🚀</span>Lancement
                    <span class="ml-auto text-secondary">${launchDate.toLocaleDateString('fr-FR', {day: 'numeric', month: 'long'})} ${launchDate.toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'})}</span>
                </li>
                <li class="flex items-center">
                    <span class="mr-2">⏫</span>Développement
                    <span class="ml-auto text-secondary">${formattedStartDate} - ${formattedEndDate}</span>
                </li>
                <li class="flex items-center">
                    <span class="mr-2">🏆</span>Présentation
                    <span class="ml-auto text-secondary">${presentationDate.toLocaleDateString('fr-FR', {day: 'numeric', month: 'long'})} ${presentationDate.toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'})}</span>
                </li>
            </ul>
        </div>
    `;
}

/**
 * Récupère les hackathons depuis l'API et les affiche
 */
console.log("yo");
async function fetchAndDisplayHackathons() {
    const hackathonsContainer = document.getElementById('hackathons-container');
    const timelineContainer = document.getElementById('timeline-container');

    if (!hackathonsContainer || !timelineContainer) return;

    try {
        const response = await apiRequest('/hackathons');
        console.log(response);
        if (!response.ok) {
            // throw new Error(`Erreur HTTP: ${response.status}`);
           console.log("erreur");
        }else{
          console.log("ok");
        }
        const result = await response.json();
        console.log(result);
        console.log('Réponse API hackathons:', result);
        const hackathons = result.data || [];

        // Vider les conteneurs
        hackathonsContainer.innerHTML = '';
        timelineContainer.innerHTML = '';

        if (hackathons.length > 0) {
            hackathons.forEach(hackathon => {
                hackathonsContainer.innerHTML += createHackathonCard(hackathon);
                timelineContainer.innerHTML += createTimelineCard(hackathon);
            });
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
      } else {
        el.classList.remove("opacity-100", "translate-y-0");
      }
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
