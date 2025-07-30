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
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    }
    if (data && method !== "GET") {
        data['csrf_token'] = document.querySelector('input[name="csrf_token"]').value;
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

const renderHackathonPhases = (phases = []) => {
    if (phases.length)
        return phases.map((phase) => `<div class="hackathon-phase">${phase}</div>`).join('');

    return '<p style="color:var(--text-secondary);">Planning détaillé bientot disponible.</p>';
}

const renderHackathonPrize = () => {
    const prizes = JSON.parse(hackathon.prizes);
    const icon = ['🥇', '🥈', '🥉']
    return prizes.map((prize, index) =>
        `<div class="prize">
        <p>${icon[index] + prize.label} </p>

        <p style="margin:10px auto;">${prize.reward}</p>
    </div>`).join('');
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
    //  userTeams=userTeams.filter((team)=>team['leader_id']===userConnected.id);
}
const createHeader = () => {
    console.log(hackathon);
    const header = document.querySelector('#header');

    header.innerHTML = `
        <div class="flexDiv flag">
            <i data-lucide="zap" class="rounded" stroke="#fff"></i>
            <strong>A venir</strong>
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

               <p class="flexDiv">Build innovation from EsgisHub to the world.</p>
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
        <div class="description card">
            <div class="flexDiv">
                 <i class="rounded" data-lucide="sparkle" stroke="#fff"></i>
                <h2>Description & objectifs</h2>
            </div>
        <p class="flexDiv">${hackathon['description']}</p>
        </div>


            <div class="card w-full" style="text-align:left!important;">
                
                <p style="text-align:center;">Exigences</p>

               <p class="flexDiv" style="margin:20px;">
                    <i data-lucide="shield" stroke="var(--blue)"></i>
                    Nombre max d'équipes: <strong>${hackathon['max_teams']}</strong>
               </p>
               
               <p class="flexDiv">
                    <i data-lucide="users" stroke='var(--blue)'></i>
                    Taille max des équipes: <strong>${hackathon['max_team_members']}</strong>
               </p>
            </div>

            <div class="card">
                <p class="flex justify-center" >
                    <i data-lucide="clock" stroke="var(--blue)" class="bg-[var(--blue-opac)] p-2 rounded" ></i>
                    <strong  style="font-size:1.6em;">Phases du hackathon</strong>
                </p>

                <div class="flex justify-center items-center mt-5">${renderHackathonPhases()}</div>
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

       <button id="registerForHackathon" class="btn flexDiv">
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
        <div class="card deadline">
            <p class="flexDiv" style="transform:translateX(-10px);">
                <i data-lucide="clock" stroke="white" class="bg-[#c23c3c] p-2 rounded" ></i>
                <strong style="color:#c23c3c;">Date limite d'inscription:</strong> 
            </p>
            <strong class="flexDiv" style="margin:20px auto 0; text-align:center;">${hackathon['registration_deadline'].replace(' ', ' à ')}</strong>
        </div>
    </div>
    `;
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
    // console.log(userTeams)
    try {
        // Vérifier si userTeams est un tableau non vide
        if (!Array.isArray(userTeams) || userTeams.length === 0) {
            console.log('Aucune équipe trouvée pour cet utilisateur');
            return null;
        }

        // Trouver l'équipe où l'utilisateur est leader
        const leaderTeam = userTeams.find(team => team && team.leader_id === userConnected?.id);

        if (!leaderTeam) {
            console.log('Utilisateur non trouvé comme leader d\'une équipe');
            return null;
        }

        // Créer une copie de l'équipe pour éviter de modifier l'original
        const teamCopy = { ...leaderTeam };
        teamCopy.team_id = teamCopy.id;
        delete teamCopy.id;

        return teamCopy;
    } catch (error) {
        console.error('Erreur lors de la récupération de l\'équipe leader:', error);
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
                    <p><strong>Attention !</strong> Une fois inscrit, vous ne pourrez plus vous desinscrire ou vous inscrire a un autre evenement pour cette edition .</p>
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
                <button class="btn-cancel" style="
                    padding: 12px 24px;
                    border: 1px solid var(--border);
                    background: transparent;
                    color: var(--text);
                    border-radius: 6px;
                    cursor: pointer;
                    transition: all 0.3s ease;
                ">Annuler</button>
                
                <button class="btn-confirm" style="
                    padding: 12px 24px;
                    background: var(--blue);
                    color: white;
                    border: none;
                    border-radius: 6px;
                    cursor: pointer;
                    transition: all 0.3s ease;
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

                const response = await apiReq(`participants/${hackathon.id}/register-team`, 'POST', userTeam);
                if (response.success) {
                    closeModal(modal, 'slide-to-top', () => {
                        showNotification('Félicitations !', response.message || 'Inscription reussie', 'success');
                        // setTimeout(() => { location.reload(); }, 2000);
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
                    handleError("Erreur d'inscription", response.message || response.error || response || "Erreur inconnue", 'error');
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
                console.log(error)
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

        console.log(userConnected, userTeams);

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
        console.error('Erreur lors du chargement:', error);

        // Masquer l'animation de chargement
        hideLoadingAnimation(loadingOverlay);

        // En cas d'erreur, afficher un message d'erreur
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