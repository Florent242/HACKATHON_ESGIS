let hackathon = null
let userConnected = null;
let userTeams = [];
let userTeam = null;
// État de chargement
let isLoading = true;


// Fonction pour créer l'animation de chargement
const createLoadingAnimation = () => {
    // Créer l'overlay de chargement
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';
    loadingOverlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(16, 12, 28, 0.8);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 1;
        transition: opacity 0.5s ease;
    `;

    loadingOverlay.innerHTML = `
        <div class="loading-content" style="
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
            text-align: center;
        ">
            <div class="loading-spinner" style="
                width: 80px;
                height: 80px;
                border: 6px solid rgba(255, 255, 255, 0.1);
                border-top: 6px solid var(--blue);
                border-radius: 50%;
                animation: spin 1s linear infinite;
            "></div>
            
            <div class="loading-text" style="
                color: var(--text);
            ">
                <h3 style="margin: 0 0 10px 0; color: var(--text); font-size: 1.5em;">Chargement du hackathon</h3>
                <p style="margin: 0; font-size: 1em; color: var(--text-secondary);">Récupération des informations...</p>
            </div>
            
            <div class="loading-dots" style="
                display: flex;
                gap: 8px;
                margin-top: 20px;
            ">
                <div class="dot" style="
                    width: 10px;
                    height: 10px;
                    background: var(--blue);
                    border-radius: 50%;
                    animation: bounce 1.4s ease-in-out infinite both;
                "></div>
                <div class="dot" style="
                    width: 10px;
                    height: 10px;
                    background: var(--blue);
                    border-radius: 50%;
                    animation: bounce 1.4s ease-in-out infinite both;
                    animation-delay: 0.2s;
                "></div>
                <div class="dot" style="
                    width: 10px;
                    height: 10px;
                    background: var(--blue);
                    border-radius: 50%;
                    animation: bounce 1.4s ease-in-out infinite both;
                    animation-delay: 0.4s;
                "></div>
            </div>
        </div>
        
        <style>
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            @keyframes bounce {
                0%, 80%, 100% {
                    transform: scale(0);
                    opacity: 0.5;
                }
                40% {
                    transform: scale(1);
                    opacity: 1;
                }
            }
        </style>
    `;

    document.body.appendChild(loadingOverlay);
    return loadingOverlay;
};

// Fonction pour masquer l'animation de chargement
const hideLoadingAnimation = (loadingOverlay) => {
    if (loadingOverlay) {
        loadingOverlay.style.opacity = '0';
        setTimeout(() => {
            if (document.body.contains(loadingOverlay)) {
                document.body.removeChild(loadingOverlay);
            }
        }, 500);
    }
};

// Fonction pour l'animation bounceIn
const animateBounceIn = () => {
    // Animation du header en premier (0.5s avant les autres)
    const headerBadge = document.querySelector('#header .flexDiv.rounded');
    const headerSection = document.querySelector('#header section');

    if (headerBadge) {
        setTimeout(() => {
            headerBadge.style.transition = 'all 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
            headerBadge.style.opacity = '1';
            headerBadge.style.transform = 'translateY(0)';
        }, 300);
    }

    if (headerSection) {
        setTimeout(() => {
            headerSection.style.transition = 'all 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
            headerSection.style.opacity = '1';
            headerSection.style.transform = 'translateY(0)';
        }, 500);
    }

    // Animation du contenu principal (1s après le début du header)
    const contentContainer = document.querySelector('.content-container');
    const inscriptionContainer = document.querySelector('.inscription');

    if (contentContainer) {
        setTimeout(() => {
            contentContainer.style.transition = 'all 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
            contentContainer.style.opacity = '1';
            contentContainer.style.transform = 'scale(1)';
        }, 500);
    }

    if (inscriptionContainer) {
        setTimeout(() => {
            inscriptionContainer.style.transition = 'all 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
            inscriptionContainer.style.opacity = '1';
            inscriptionContainer.style.transform = 'scale(1)';
        }, 500);
    }
};

const apiReq = async (apiRoute, method = 'GET', data = null) => {
    const optionRequest = {
        method: method,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    }
    if (data && method !== "GET") {
        data['csrf_token'] = document.querySelector('meta[name="csrf-token"]').content;
        optionRequest.body = JSON.stringify(data);
    }

    const reponse =
        await fetch('/api/' + apiRoute, optionRequest)
            .then(rep => rep.json())
            .catch(err => err);

    return reponse;
};


const getHackathon = async (id) => {
    const response = await apiReq(`hackathons/${id}`);
    if (response.success) {
        hackathon = response.data;
    }

}

const renderHackathonTechno = (hackathon) => {
    return hackathon.map((techno) => `<p class="techno">${techno}</p>`).join('');
}

/**
 * Affiche les phases du hackathon
 * @param {Array<string>} phases - Tableau de chaînes représentant les phases
 * @example
 * // Format attendu :
 * [
 *   "Phase 1: Inscription",
 *   "Phase 2: Sélection des équipes",
 *   "Phase 3: Développement"
 * ]
 */
const renderHackathonPhases = (phases = []) => {
    if (!phases.length) {
        return '<p style="color:var(--text-secondary);">Planning détaillé bientôt disponible.</p>';
    }

    return phases.map((phase) => `
        <div class="phase-card w-full" style="
            background: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);">
            
            <h3 style="
                color: var(--primary);
                margin: 0 0 15px 0;
                font-size: 1.2em;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 10px;">
                <i data-lucide="flag" stroke="var(--primary)" width="20" height="20"></i>
                ${phase.name}
            </h3>
            
            <p style="
                color: var(--text-secondary);
                margin: 0 0 15px 0;
                font-size: 0.95em;
                line-height: 1.5;">
                ${phase.description}
            </p>
            
            <div style="
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
                margin-top: 15px;">
                
                <div style="
                    display: flex;
                    align-items: center;
                    color: var(--text);
                    font-size: 0.9em;
                    background: var(--bg-secondary);
                    padding: 6px 12px;
                    border-radius: 20px;
                    gap: 5px;">
                    <i data-lucide="calendar" width="16" height="16" stroke="var(--primary)"></i>
                    <span>${new Date(phase.start).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' })}</span>
                    <i data-lucide="arrow-right" width="14" height="14" stroke="var(--text-secondary)"></i>
                    <span>${new Date(phase.end).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' })}</span>
                </div>
                
                <div style="
                    display: flex;
                    align-items: center;
                    color: var(--text);
                    font-size: 0.9em;
                    background: var(--bg-secondary);
                    padding: 6px 12px;
                    border-radius: 20px;
                    gap: 5px;">
                    <i data-lucide="${phase.phase_type === 'open' ? 'unlock' : 'lock'}" width="16" height="16" stroke="var(--primary)"></i>
                    <span>${phase.phase_type === 'open' ? 'Ouverte à tous' : 'Sur invitation'}</span>
                </div>
                
                ${phase.team_qualified ? `
                <div style="
                    display: flex;
                    align-items: center;
                    color: var(--success);
                    font-size: 0.9em;
                    background: rgba(74, 222, 128, 0.1);
                    padding: 6px 12px;
                    border-radius: 20px;
                    gap: 5px;">
                    <i data-lucide="award" width="16" height="16" stroke="currentColor"></i>
                    <span>${phase.team_qualified} équipes qualifiées</span>
                </div>` : ''}
            </div>
        </div>
        `).join('');
}

/**
 * Affiche les prix du hackathon
 * @returns {string} HTML des prix formatés
 * @example
 * // Format attendu dans hackathon.prizes (chaîne JSON) :
 * [
 *   { label: "1er Prix", reward: "Ordinateur portable" },
 *   { label: "2ème Prix", reward: "Tablette" },
 *   { label: "3ème Prix", reward: "Smartphone" }
 * ]
 */
const renderHackathonPrize = () => {
    try {
        // Nettoyer la chaîne JSON des caractères invisibles
        const cleanedPrizes = hackathon.prizes.replace(/[\u200B-\u200D\uFEFF]/g, '');
        const prizes = JSON.parse(cleanedPrizes);
        const icon = ['🥇', '🥈', '🥉'];
        return prizes?.map((prize, index) =>
            `<div class="prize" style="background: var(--card-bg); border-radius: 8px; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid var(--border-color); transition: transform 0.2s, box-shadow 0.2s; text-align: center;">
                <div style="font-size: 2rem; margin-bottom: 8px;">${icon[index]}</div>
                <h4 style="font-size: 1.1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 8px;">${prize.label}</h4>
                <p style="color: var(--primary); font-weight: 500; margin: 0;">${prize.reward}</p>
            </div>`
        ).join('') || '<p class="error">Aucun prix disponible</p>';
    } catch (error) {
        return '<p class="error">Erreur lors du chargement des prix</p>';
    }
}

const renderRules = () => {
    const rules = JSON.parse(hackathon.rules);
    return rules.map((rule) =>
        `<div class="rule">
        <div class="flex" style="justify-content:space-between; height:40px; margin-bottom:10px;">
            <p class="flex" style="width:max-content;">
                <i data-lucide="circle-alert"></i>
                <strong>${rule.title}</strong>
           </p>
            <i data-lucide="chevron-down" class="chevron-icon"></i>
        </div>
        <p class="rule-description">${rule.description}</p>
        <hr style="margin:15px 0 5px; border:0.1px solid var(--text); opacity:0.1;">
    </div>`).join('');
}

const showRules = () => {
    const rules = document.querySelectorAll('.rule');
    let currentlyOpen = null;

    rules.forEach(rule => {
        const ruleDescription = rule.querySelector('.rule-description');
        // Initialiser l'état fermé avec display none et opacité 0
        ruleDescription.style.display = 'none';
        ruleDescription.style.opacity = '0';
        ruleDescription.style.transition = 'opacity 0.3s ease';

        rule.addEventListener('click', () => {
            const isCurrentlyOpen = rule === currentlyOpen;
            const chevronIcon = rule.querySelector('.chevron-icon');

            // Fermer la règle actuellement ouverte (si différente)
            if (currentlyOpen && currentlyOpen !== rule) {
                const openDescription = currentlyOpen.querySelector('.rule-description');
                const openChevron = currentlyOpen.querySelector('[data-lucide="chevron-down"]');
                // Animation de fermeture
                openDescription.style.opacity = '0';
                setTimeout(() => {
                    openDescription.style.display = 'none';
                }, 300);
                // Rotation du chevron
                openChevron.style.transition = 'transform 0.3s ease';
                openChevron.style.transform = 'rotate(0deg)';
                currentlyOpen.classList.remove('active');
                currentlyOpen = null;
            }
            // Gérer l'état de la règle cliquée
            if (!isCurrentlyOpen) {
                // Ouvrir la règle
                ruleDescription.style.display = 'block';
                setTimeout(() => {
                    ruleDescription.style.opacity = '1';
                }, 10); // Légère attente pour déclencher la transition
                // Rotation du chevron
                chevronIcon.style.transition = 'transform 0.3s ease';
                chevronIcon.style.transform = 'rotate(180deg)';
                rule.classList.add('active');
                currentlyOpen = rule;
            } else {
                // Fermer la règle actuellement ouverte
                ruleDescription.style.opacity = '0';
                setTimeout(() => {
                    ruleDescription.style.display = 'none';
                }, 300);
                // Rotation du chevron
                chevronIcon.style.transition = 'transform 0.3s ease';
                chevronIcon.style.transform = 'rotate(0deg)';
                rule.classList.remove('active');
                currentlyOpen = null;
            }
        });
    });
}

const getUserConnected = async () => {
    const response = await apiReq('users/me');
    if (response.success) {
        userConnected = response.data;
    }
}

const getUserTeams = async () => {
    const response = await apiRequest(`/teams/user/${userConnected.id}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    });
    if (userTeams)
        userTeams = response.data
}
const createHeader = () => {
    const header = document.querySelector('#header');

    header.innerHTML = `
        <div class="flexDiv flag">
            <i data-lucide="zap" class="rounded" stroke="#fff"></i>
            <strong>${getHackathonStatus(hackathon)}</strong>
        </div>

        <section class="flexDiv w-full" style="opacity: 0; transform: translateY(-30px);">
            <div class="infos">
               <h1 class="text-2xl font-semibold text-center" style="margin-top:15px;">${hackathon['name']}</h1>
               <div class="flexDiv" style="margin: 30px;">
                    <i data-lucide="calendar" class="calendar" stroke="#fff"></i>
                    <p>
                        <span>Du</span> <strong>${new Date(hackathon['start_date']).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' }) + ' à ' + new Date(hackathon['start_date']).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}</strong> <span>au</span> <strong>${new Date(hackathon['end_date']).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' }) + ' à ' + new Date(hackathon['end_date']).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}</strong>
                     </p>
               </div>

               <p class="flexDiv" style="margin: 30px;">
                    <i data-lucide="users" stroke="#fff"></i>
                    <span>${hackathon['teams_count']} équipes inscrites</span>
               </p>

               <p class="flexDiv">${hackathon['theme'] || 'Build innovation from Hack & Stack to the world.'}</p>
            </div>
            ${hackathon['image'] ? `<img id="hackathonImage" src="${hackathon['image']}" alt="Image du hackathon">` : ''}
        </section> 
    `;
    lucide.createIcons();
}


const createMain = () => {
    const main = document.querySelector('main');
    main.innerHTML = `
    <div class="content-container flex flex-col justify-center items-center">
        <div class="card w-full">
            <div class="flex items-center gap-3 mb-4">
                <i data-lucide="sparkle" class="w-8 h-8 p-1.5 rounded-full bg-[var(--blue-opac)]" stroke="var(--blue)"></i>
                <h2 class="text-xl font-semibold">Description & objectifs</h2>
            </div>
            <div class="prose max-w-none text-[var(--text-secondary)]">
                ${hackathon['description']}
            </div>
        </div>

        <div class="card w-full">
            <div class="flex items-center gap-3 mb-4">
                <i data-lucide="shield-check" class="w-8 h-8 p-1.5 rounded-full bg-[var(--blue-opac)]" stroke="var(--blue)"></i>
                <h2 class="text-xl font-semibold">Exigences</h2>
            </div>
            <div class="space-y-4">
                <div class="flex items-center gap-3 p-3 bg-[var(--bg-secondary)] rounded-lg">
                    <div class="p-2 rounded-full bg-[var(--blue-opac)]">
                        <i data-lucide="users" class="w-5 h-5" stroke="var(--blue)"></i>
                    </div>
                    <div>
                        <p class="text-sm text-[var(--text-secondary)]">Taille maximale des équipes</p>
                        <p class="font-medium">${hackathon['max_team_members']} membres</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-[var(--bg-secondary)] rounded-lg">
                    <div class="p-2 rounded-full bg-[var(--blue-opac)]">
                        <i data-lucide="shield" class="w-5 h-5" stroke="var(--blue)"></i>
                    </div>
                    <div>
                        <p class="text-sm text-[var(--text-secondary)]">Nombre maximum d'équipes</p>
                        <p class="font-medium">${hackathon['max_teams']} équipes</p>
                    </div>
                </div>
            </div>
        </div>

            <div class="card w-full">
                <div class="flex items-center gap-3 mb-4">
                    <i data-lucide="clock" class="w-8 h-8 p-1.5 rounded-full bg-[var(--blue-opac)]" stroke="var(--blue)"></i>
                    <h2 class="text-xl font-semibold">Phases du hackathon</h2>
                </div>
                <div class="space-y-4">
                    ${renderHackathonPhases(hackathon['phases'])}
                </div>
            </div>

            <div class="card" style="margin:10px 0;">
                <p class="flex justify-center">
                    <i data-lucide="trophy" class="bg-[var(--icon-yellow-bg)] p-2 rounded" stroke="yellow" ></i>
                    <strong style="font-size:1.6em;">Récompenses</strong>
                </p>

                <div class="flex flex-col justify-center items-center mt-5">
                    ${renderHackathonPrize()}
                </div>
            </div>

            <div class="card">
                <p class="flex justify-center">
                    <i data-lucide="circle-check-big" stroke="var(--blue)" class="bg-[var(--blue-opac)] p-2 rounded" ></i>
                    <strong style="font-size:1.4em;">Règlements du hackathon</strong>
                </p>
                
                <div style="margin:20px auto; width:98%;">${renderRules()}</div>
                
            </div>
    </div>

    <div class="inscription flex flex-col justify-center items-center" style="opacity: 0; transform: scale(0.8);">
        <div class="card">
       <div class="flexDiv" style="justify-content: left;">
            <i data-lucide="users" class="bg-[var(--blue-opac)] p-2 rounded" stroke="#fff"></i>
            <h2>Inscription</h2>
       </div>

       <p style="font-size: 1.2rem;">Gérez votre participation à ce hackathon.</p>
         
       <hr style="margin: 30px 0 20px; border: 0.1px solid var(--text); opacity: 0.1;">

       <button id="registerForHackathon" class="btn btn-primary btn-neon">
            <i data-lucide="users" stroke="#fff"></i>
            <strong style="color:var(--text);">Inscrire mon équipe</strong>
       </button>

       <p class="subtitle">
            En tant que leader, vous pouvez inscrire votre équipe à ce hackathon.
       </p>

       <hr style="margin: 30px 0 20px; border: 0.1px solid var(--text); opacity: 0.1;">

        <p class="subtitle" style="text-align: left;">
            Organisateur: <strong>${hackathon['created_by']}</strong>
        </p>

        <p class="subtitle" style="text-align: left;">
            Lieu: <strong>${hackathon['location']}</strong>
        </p>

        </div>
        <div class="card w-full" style=" border-radius: 8px; padding: 20px; margin: 10px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-left: 4px solid var(--primary);">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 flex items-center justify-center rounded-full" style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary);">
                    <i data-lucide="alarm-clock" width="20" height="20" stroke="currentColor" stroke-width="1.5"></i>
                </div>
                <h3 class="text-lg font-semibold" style="color: var(--text-primary);">Date limite d'inscription</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-center gap-3 p-3" style="background: var(--bg-secondary); border-radius: 8px;">
                    <div class="w-8 h-8 flex items-center justify-center rounded-full" style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary);">
                        <i data-lucide="calendar" width="16" height="16" stroke="currentColor"></i>
                    </div>
                    <div>
                        <p class="text-xs" style="color: var(--text-secondary);">Date limite</p>
                        <p class="text-sm font-medium" style="color: var(--text-primary);">${new Date(hackathon['registration_deadline']).toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3 p-3" style="background: var(--bg-secondary); border-radius: 8px;">
                    <div class="w-8 h-8 flex items-center justify-center rounded-full" style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary);">
                        <i data-lucide="clock" width="16" height="16" stroke="currentColor"></i>
                    </div>
                    <div>
                        <p class="text-xs" style="color: var(--text-secondary);">Heure limite</p>
                        <p class="text-sm font-medium" style="color: var(--text-primary);">${new Date(hackathon['registration_deadline']).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3 p-3" style="background: rgba(var(--accent-rgb), 0.1); border-radius: 8px;">
                    <div class="w-8 h-8 flex items-center justify-center rounded-full" style="background: rgba(var(--accent-rgb), 0.2); color: var(--accent);">
                        <i data-lucide="hourglass" width="16" height="16" stroke="currentColor"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs" style="color: var(--accent);">Temps restant</p>
                        <p class="text-sm font-medium deadline-counter" style="color: var(--accent);" data-deadline="${new Date(hackathon['registration_deadline']).toISOString()}">Chargement...</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 w-full rounded-full h-1.5 overflow-hidden" style="background: var(--text-secondary);">
                <div class="h-full rounded-full deadline-progress" style="width: 0%; background: var(--primary);"></div>
            </div>
        </div>

    </div>
    `;

    // Mettre à jour le compteur immédiatement puis toutes les minutes
    updateDeadlineCounter();
    setInterval(updateDeadlineCounter, 60000);
}

/**
 * Met à jour le compteur de temps restant avant la fin des inscriptions
 * et gère la barre de progression
 */
function updateDeadlineCounter() {
    const now = new Date();
    
    document.querySelectorAll('.deadline-counter').forEach(element => {
        try {
            // Récupération de la date limite depuis l'attribut data
            const deadlineString = element.getAttribute('data-deadline');
            if (!deadlineString) {
                console.error('Aucune date de deadline définie');
                return;
            }
            
            const deadline = new Date(deadlineString);
            if (isNaN(deadline.getTime())) {
                console.error('Format de date invalide:', deadlineString);
                return;
            }
            
            const diff = deadline - now; // différence en millisecondes
            const card = element.closest('.card');
            const progressBar = card?.querySelector('.deadline-progress');
            
            // Si la date est dépassée
            if (diff <= 0) {
                element.textContent = 'Inscriptions closes';
                element.style.color = 'var(--red)';
                if (progressBar) {
                    progressBar.style.background = 'var(--red)';
                    progressBar.style.width = '100%';
                }
                return;
            }
            
            // Calcul du temps restant
            const seconds = Math.floor(diff / 1000);
            const minutes = Math.floor(seconds / 60);
            const hours = Math.floor(minutes / 60);
            const days = Math.floor(hours / 24);
            
            // Formatage du texte de temps restant
            const remainingHours = hours % 24;
            const remainingMinutes = minutes % 60;
            
            if (days > 0) {
                element.textContent = `${days}j ${remainingHours}h restants`;
            } else if (hours > 0) {
                element.textContent = `${hours}h ${remainingMinutes}min restantes`;
            } else {
                element.textContent = `${minutes}min restantes`;
            }
            
            // Calcul de la progression (sur 30 jours par défaut)
            if (progressBar) {
                // On définit la date de début comme étant 30 jours avant la deadline
                const startDate = new Date(deadline);
                startDate.setDate(deadline.getDate() - 30);
                
                const totalDuration = deadline - startDate;
                const elapsed = now - startDate;
                
                // Calcul du pourcentage de progression
                let progress = Math.min(100, Math.max(0, (elapsed / totalDuration) * 100));
                
                // Si la date de début est dans le futur
                if (elapsed < 0) progress = 0;
                
                // Mise à jour de la barre de progression
                progressBar.style.width = `${progress}%`;
                
                // Changement de couleur en fonction de la progression
                if (progress > 80) {
                    progressBar.style.background = 'var(--red)';
                } else if (progress > 60) {
                    progressBar.style.background = 'var(--warning)';
                } else {
                    progressBar.style.background = 'var(--success)';
                }
            }
            
        } catch (error) {
            console.error('Erreur dans updateDeadlineCounter:', error);
            element.textContent = 'Erreur de calcul';
        }
    });
}

// Fonction pour créer la modale
const createModal = (content) => {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(16, 12, 28, 0.3);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.3s ease;
    `;

    modal.innerHTML = `
        <div class="modal-content" style="
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            overflow: hidden;
            transform: scale(0.8);
            transition: transform 0.3s ease;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            position: relative;
            scrollbar-width: none;
            -ms-overflow-style: none;
        ">
            <style>
                .modal-content::-webkit-scrollbar { display: none; }
                @keyframes slideToTopModal {
                    0% { transform: translateY(0) scale(1); opacity: 1; }
                    80% { opacity: 1; }
                    100% { transform: translateY(-120px) scale(0.95); opacity: 0; }
                }
                .slide-to-top {
                    animation: slideToTopModal 0.5s cubic-bezier(.68,-0.55,.27,1.55) forwards;
                }
            </style>
            <div style="
                position: absolute;
                top: -2px;
                left: -2px;
                right: -2px;
                bottom: -2px;
                background: var(--background);
                border-radius: 16px;
                z-index: -1;
            "></div>
            ${content}
        </div>
    `;

    document.body.appendChild(modal);

    // Animation d'ouverture
    setTimeout(() => {
        modal.style.opacity = '1';
        modal.querySelector('.modal-content').style.transform = 'scale(1)';
    }, 10);

    return modal;
};

// Fonction pour fermer la modale avec animation optionnelle
const closeModal = (modal, animation = null, callback = null) => {
    if (animation === 'slide-to-top') {
        const modalContent = modal.querySelector('.modal-content');
        if (modalContent) {
            modalContent.classList.add('slide-to-top');
        }
        setTimeout(() => {
            modal.style.opacity = '0';
            setTimeout(() => {
                if (document.body.contains(modal)) {
                    document.body.removeChild(modal);
                }
                if (typeof callback === 'function') callback();
            }, 300);
        }, 500); // durée de l'animation slide
    } else {
        modal.style.opacity = '0';
        modal.querySelector('.modal-content').style.transform = 'scale(0.8)';
        setTimeout(() => {
            if (document.body.contains(modal)) {
                document.body.removeChild(modal);
            }
            if (typeof callback === 'function') callback();
        }, 300);
    }
};

// Fonction pour obtenir l'équipe dont l'utilisateur est leader
const getUserLeaderTeam = () => {
    try {
        // Vérifier si userTeams est un tableau non vide
        if (!Array.isArray(userTeams) || userTeams.length === 0) {
            return null;
        }

        // Trouver l'équipe où l'utilisateur est leader
        const leaderTeam = userTeams.find(team => team && team.leader_id === userConnected?.id);

        if (!leaderTeam) {
            return null;
        }

        // Créer une copie de l'équipe pour éviter de modifier l'original
        const teamCopy = { ...leaderTeam };
        teamCopy.team_id = teamCopy.id;
        delete teamCopy.id;

        return teamCopy;
    } catch (error) {
        return null;
    }
};

// Fonction pour afficher la modale de confirmation d'inscription
const showRegistrationModal = async () => {
    if (!userTeam) {
        userTeam = getUserLeaderTeam();
    }
    let modalContent = '';

    if (userTeam) {
        // L'utilisateur est leader d'une équipe
        modalContent = `
            <div class="flexDiv" style="margin-bottom: 20px;">
                <h2 style="margin: 0; color: var(--text);">Confirmer l'inscription</h2>
            </div>
            
            <p style="color: var(--text-secondary); margin-bottom: 25px;">
                Vous êtes sur le point d'inscrire votre équipe au hackathon <strong>${hackathon.name}</strong>.
            </p>
            
            <div class="team-info" style="
                background: var(--bg-primary);
                border-radius: 12px;
                padding: 25px;
                margin-bottom: 25px;
                border: 1px solid var(--border);
            ">
                <div style="display: flex; align-items: flex-start; gap: 20px; margin-bottom: 20px;">
                    <div style="
                        width: 60px;
                        height: 60px;
                        background: var(--blue);
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        flex-shrink: 0;
                        transform:scale(1.1);
                        border:solid 2px currentcolor;
                    ">
                    ${userTeam.name.slice(0, 2)}
                    </div>
                    
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                            <h3 style="margin: 0; color: var(--text); font-size: 1.4em;">${userTeam.name}</h3>
                            <div style="
                                background: var(--yellow);
                                color: white;
                                padding: 4px 8px;
                                border-radius: 6px;
                                font-size: 0.8em;
                                display: flex;
                                align-items: center;
                                gap: 4px;
                            ">
                                <i data-lucide="crown" stroke="white" style="width: 12px; height: 12px;"></i>
                                Leader
                            </div>
                        </div>
                        
                        <p style="text-align:left; margin:0; color: var(--text-secondary); font-size: 0.9em;">
                            ${userTeam.members_count || 1} membre(s)
                        </p>
                    </div>
                </div>

                <div style="
                background: #fef3c7;
                border: 1px solid #f59e0b;
                border-radius: 8px;
                padding: 0.5rem;
                margin-bottom: 1rem;
            ">
                <div style="display: flex; align-items: center; gap: 0.3rem; margin: 0; color: #92400e; font-size: 14px;">
                    <i data-lucide="info" style="width: 2rem; height: 2rem; display: inline; margin-right: 0.3rem;"></i>
                    <p><strong>Attention !</strong> Une fois inscrit, vous ne pourrez plus vous désinscrire ou vous inscrire à un autre événement de cette édition.</p>
                </div>
            </div>
                
                <div class="team-description-section" style="border-top: 1px solid var(--border); padding-top: 15px;">
                    <div class="description-header" style="
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        cursor: pointer;
                        padding: 10px 0;
                    ">
                        <span style="color: var(--text); font-weight: 500;">Description de l'équipe</span>
                        <i data-lucide="chevron-down" class="description-chevron" style="
                            width: 20px;
                            height: 20px;
                            color: var(--text-secondary);
                            transition: transform 0.3s ease;
                        "></i>
                    </div>
                    
                    <div class="description-content" style="
                        color: var(--text-secondary);
                        line-height: 1.6;
                        max-height: 0;
                        overflow: hidden;
                        transition: max-height 0.3s ease;
                    ">
                        ${userTeam.description || 'Aucune description disponible pour cette équipe.'}
                    </div>
                </div>
            </div>
            
            <div class="modal-actions" style="display: flex; gap: 15px; justify-content: flex-end;">
                <button class="btn-cancel btn-primary btn-standard">Annuler</button>
                
                <button class="btn-confirm btn-primary btn-cyber"
                ">Confirmer</button>
            </div>
        `;
    } else {
        // L'utilisateur n'est pas leader d'une équipe
        modalContent = `
            <div class="flexDiv" style="margin-bottom: 20px;">
                <i data-lucide="alert-circle" stroke="#f59e0b" style="width: 24px; height: 24px;"></i>
                <h2 style="margin: 0; color: var(--text);">Aucune équipe trouvée</h2>
            </div>
            
            <p style="color: var(--text-secondary); margin-bottom: 25px;">
                Vous devez être leader d'une équipe pour vous inscrire à ce hackathon.
            </p>
            
            <div style="
                background: #fef3c7;
                border: 1px solid #f59e0b;
                border-radius: 8px;
                padding: 15px;
                margin-bottom: 25px;
            ">
                <p style="margin: 0; color: #92400e; font-size: 14px;">
                    <i data-lucide="info" style="width: 16px; height: 16px; display: inline; margin-right: 8px;"></i>
                    Vous n'êtes actuellement membre d'aucune équipe ou vous n'êtes pas le leader de votre équipe.
                </p>
            </div>
            
            <div class="modal-actions" style="display: flex; gap: 15px; justify-content: flex-end;">
                <button class="btn-cancel" style="
                    padding: 12px 24px;
                    border: 1px solid var(--border);
                    background: transparent;
                    color: var(--text);
                    border-radius: 6px;
                    cursor: pointer;
                    transition: all 0.3s ease;
                ">Fermer</button>
                
                <button class="btn-create-team" style="
                    padding: 12px 24px;
                    background: var(--blue);
                    color: white;
                    border: none;
                    border-radius: 6px;
                    cursor: pointer;
                    transition: all 0.3s ease;
                ">Joindre/Créer une équipe</button>
            </div>
        `;
    }

    const modal = createModal(modalContent);

    // Gestion des événements de la modale
    const cancelBtn = modal.querySelector('.btn-cancel');
    const confirmBtn = modal.querySelector('.btn-confirm');
    const createTeamBtn = modal.querySelector('.btn-create-team');

    // Fermer la modale
    cancelBtn.addEventListener('click', () => closeModal(modal));

    // Clic en dehors de la modale pour fermer
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal(modal);
        }
    });

    // Confirmer l'inscription
    if (confirmBtn) {
        confirmBtn.addEventListener('click', async () => {
            try {
                confirmBtn.disabled = true;
                confirmBtn.textContent = 'Inscription en cours...';

                const response = await apiRequest(`/participants/${hackathon.id}/register-team`, { method: 'POST', body: JSON.stringify(userTeam) });
                if (response.success) {
                    closeModal(modal, 'slide-to-top', () => {
                        showNotification('Félicitations !', response.message || 'Inscription reussie', 'success');
                    });
                    return;
                } else {
                    setTimeout(() => {
                        confirmBtn.textContent = 'Error';
                        confirmBtn.style.transition = "all ease 0.5s";
                        confirmBtn.style.background = "rgba(227, 44, 44, 0.81)";
                    }, 1500);
                    setTimeout(() => {
                        closeModal(modal)
                    }, 3000);
                    handleError("Erreur d'inscription", response ?? "Erreur inconnue", 'error');
                }
            } catch (error) {
                setTimeout(() => {
                    confirmBtn.textContent = 'Error';
                    confirmBtn.style.transition = "all ease 0.5s";
                    confirmBtn.style.background = "rgba(227, 44, 44, 0.81)";
                }, 1500);
                setTimeout(() => {
                    closeModal(modal)
                }, 3000);
            }
        });
    }

    // Créer une équipe
    if (createTeamBtn) {
        createTeamBtn.addEventListener('click', () => {
            closeModal(modal);
            // Rediriger vers la page de création d'équipe
            window.location.href = '/user/teams';
        });
    }

    // Créer les icônes Lucide
    lucide.createIcons();

    // Gestion du dépliage de la description de l'équipe
    const descriptionHeader = modal.querySelector('.description-header');
    const descriptionContent = modal.querySelector('.description-content');
    const descriptionChevron = modal.querySelector('.description-chevron');

    if (descriptionHeader && descriptionContent) {
        descriptionHeader.addEventListener('click', () => {
            const isExpanded = descriptionContent.style.maxHeight !== '0px' && descriptionContent.style.maxHeight !== '';

            if (isExpanded) {
                // Replier
                descriptionContent.style.maxHeight = '0';
                descriptionChevron.style.transform = 'rotate(0deg)';
            } else {
                // Déplier
                descriptionContent.style.maxHeight = descriptionContent.scrollHeight + 'px';
                descriptionChevron.style.transform = 'rotate(180deg)';
            }
        });
    }
};

const register = async () => {
    const registerForHackathon = document.querySelector('#registerForHackathon');
    registerForHackathon.addEventListener('click', () => {
        showRegistrationModal();
    });
};
const getHackathonStatus = (status) => {
    switch (status) {
        case 'active':
            return 'En cours';
            break;
        case 'inactive':
            return 'Inactif';
            break;
        case 'ended':
            return 'Terminé';
            break;
        case 'draft':
            return 'Brouillon';
            break;
        default:
            return '';
            break;
    }
};

window.addEventListener('DOMContentLoaded', async () => {
    // Afficher l'animation de chargement immédiatement
    const loadingOverlay = createLoadingAnimation();

    try {
        await getUserConnected(),

            // Charger les données en parallèle
            await Promise.all([
                getUserTeams(),
                getHackathon(window.location.href.split('/').pop())
            ]);

        // Créer le contenu une fois les données chargées
        createHeader();
        createMain();
        showRules();
        register(); // Initialiser l'écouteur d'événement pour le bouton d'inscription

        // Masquer l'animation de chargement
        hideLoadingAnimation(loadingOverlay);

        // Lancer l'animation bounceIn après un court délai
        setTimeout(() => {
            animateBounceIn();
        }, 100);

        // Gestion des images
        if (document.querySelector('img'))
            document.querySelector('img').onerror = (e) => e.target.style.display = 'none';

        lucide.createIcons();

        // Marquer le chargement comme terminé
        isLoading = false;

    } catch (error) {
        hideLoadingAnimation(loadingOverlay);

        const main = document.querySelector('main');
        if (main) {
            main.innerHTML = `
                <div style="
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    min-height: 60vh;
                    text-align: center;
                    color: var(--text-secondary);
                ">
                    <i data-lucide="alert-circle" stroke="#ef4444" style="width: 48px; height: 48px; margin-bottom: 20px;"></i>
                    <h3 style="margin: 0 0 10px 0; color: var(--text);">Erreur de chargement</h3>
                    <p style="margin: 0; font-size: 0.9em;">Impossible de charger les informations du hackathon.</p>
                    <button onclick="location.reload()" style="
                        margin-top: 20px;
                        padding: 12px 24px;
                        background: var(--blue);
                        color: white;
                        border: none;
                        border-radius: 6px;
                        cursor: pointer;
                    ">Réessayer</button>
                </div>
            `;
            lucide.createIcons();
        }
    }
});