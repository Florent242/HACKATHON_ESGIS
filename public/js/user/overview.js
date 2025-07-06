// ========================================
// VARIABLES GLOBALES ET INITIALISATION
// ========================================

// Récupération des éléments DOM
const teamId = window.location.href.split('/').pop();
const teamName = document.querySelector('#teamStats strong');
const teamAvatar = document.querySelector('#teamAvatar');
const teamCategory = document.querySelector('.name span');
const memberNumber = document.querySelector('#teamStats .member span');
const aboutSection = document.querySelector('#ulOptionContentZone');
let listItems = document.querySelectorAll('ul li');

// Données globales
let team = []; 
let userConnected = {};

// Données des demandes d'adhésion
let joinRequests = [];

// ========================================
// FONCTIONS D'AUTHENTIFICATION ET UTILISATEUR
// ========================================

const getUserConnected = async () => {
    const user = await apiReq('users/me');
    if (user.success) {
        userConnected = user.data;
    }
};

// ========================================
// FONCTIONS DE NAVIGATION ET INDICATEUR
// ========================================

const createNavIndicator = () => {
    const navBar = document.querySelector('.nav-bar');
    const indicator = document.createElement('div');
    indicator.className = 'nav-indicator';
    navBar.appendChild(indicator);
    
    // Fonction pour repositionner l'indicateur
    const repositionIndicator = () => {
        const activeLi = document.querySelector('li.focus');
        if (activeLi && indicator) {
            moveIndicator(activeLi, indicator);
        }
    };
    
    // Ajouter un écouteur pour le redimensionnement de la fenêtre
    window.addEventListener('resize', repositionIndicator);
    
    // Ajouter un écouteur pour les changements de scroll (apparition/disparition de scrollbar)
    let lastScrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
    
    const checkScrollbarChange = () => {
        const currentScrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
        if (currentScrollbarWidth !== lastScrollbarWidth) {
            lastScrollbarWidth = currentScrollbarWidth;
            // Délai pour laisser le temps au DOM de se stabiliser
            setTimeout(repositionIndicator, 10);
        }
    };
    
    // Vérifier les changements de scrollbar périodiquement
    const scrollbarCheckInterval = setInterval(checkScrollbarChange, 100);
    
    // Nettoyer l'intervalle quand la page est déchargée
    window.addEventListener('beforeunload', () => {
        clearInterval(scrollbarCheckInterval);
    });
    
    return indicator;
};

const moveIndicator = (targetLi, indicator) => {
    // Vérifier que les éléments existent
    if (!targetLi || !indicator) {
        // console.warn('moveIndicator: éléments manquants', { targetLi, indicator });
        return;
    }

    const navBar = document.querySelector('.nav-bar');
    if (!navBar) {
        // console.warn('moveIndicator: navBar non trouvé');
        return;
    }

    // S'assurer que l'élément targetLi est bien dans le DOM
    if (!document.contains(targetLi)) {
        // console.warn('moveIndicator: targetLi pas dans le DOM');
        return;
    }

    // Utiliser getBoundingClientRect() pour obtenir les positions relatives au viewport
    const navRect = navBar.getBoundingClientRect();
    const liRect = targetLi.getBoundingClientRect();
    
    // Vérifier que les dimensions sont valides
    if (liRect.width === 0 || liRect.height === 0) {
        // console.warn('moveIndicator: dimensions invalides', liRect);
        return;
    }
    
    // Détecter la taille de l'écran
    const isMobile = window.innerWidth <= 650;
    const isSmallMobile = window.innerWidth <= 400;
    
    // Calculer la position relative au conteneur de navigation
    const left = liRect.left - navRect.left;
    const width = liRect.width;
    const height = liRect.height;
    const top = liRect.top - navRect.top;
    
    // console.log('moveIndicator:', { 
    //     targetLi, 
    //     left, 
    //     width, 
    //     height, 
    //     top,
    //     liRect,
    //     navRect,
    //     windowInnerWidth: window.innerWidth,
    //     clientWidth: document.documentElement.clientWidth
    // });
    
    // Appliquer les styles avec transition fluide
    indicator.style.transition = 'all 0.3s ease-in-out';
    indicator.style.left = left + 'px';
    indicator.style.width = width + 'px';
    indicator.style.height = height + 'px';
    indicator.style.top = top + 'px';
    
    // Ajuster le border-radius selon la taille
    if (isMobile) {
        indicator.style.borderRadius = '6px';
    } else {
        indicator.style.borderRadius = '8px';
    }
};

const handleNavBar = async () => {
    const navBar = document.querySelector('.nav-bar');
    await getUserConnected();

    if (userConnected.id === team.leader_id) {
        const otherNavBarItems = `
        <li>
            <i data-lucide="settings"></i>
            <span>Paramètres</span>            
        </li>
        
        <li>
            <i data-lucide="bell"></i>
            <span>Demandes</span>  
            <span id="requestNumber"></span>          
        </li>`;
        navBar.insertAdjacentHTML('beforeend', otherNavBarItems);
        listItems = document.querySelectorAll('ul li');
        
        // Mettre à jour l'indicateur si il existe déjà
        const existingIndicator = document.querySelector('.nav-indicator');
        if (existingIndicator) {
            const activeLi = document.querySelector('li.focus');
            if (activeLi) {
                // Utiliser requestAnimationFrame pour s'assurer que le DOM est mis à jour
                requestAnimationFrame(() => {
                    moveIndicator(activeLi, existingIndicator);
                });
            }
        }
    }
    return;
};

// ========================================
// FONCTIONS D'ANIMATION ET TRANSITIONS
// ========================================

const animateContentChange = (newContent, callback = null) => {
    const contentZone = document.querySelector('#ulOptionContentZone');
    
    // Mesurer la hauteur actuelle
    const currentHeight = contentZone.offsetHeight;
    
    // Créer un élément temporaire pour mesurer la nouvelle hauteur
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = newContent;
    tempDiv.style.position = 'absolute';
    tempDiv.style.visibility = 'hidden';
    tempDiv.style.width = contentZone.offsetWidth + 'px';
    document.body.appendChild(tempDiv);
    
    const newHeight = tempDiv.offsetHeight;
    document.body.removeChild(tempDiv);
    
    // Animer la transition de hauteur
    contentZone.style.height = currentHeight + 'px';
    contentZone.style.overflow = 'hidden';
    
    // Forcer un reflow
    contentZone.offsetHeight;
    
    // Animer vers la nouvelle hauteur
    contentZone.style.height = newHeight + 65 + 'px';
    
    // Changer le contenu après un court délai
    contentZone.innerHTML = newContent;
    lucide.createIcons();
    contentZone.classList.add('content-fade-in');
    
    // Retirer la classe d'animation après l'animation
    setTimeout(() => {
        contentZone.classList.remove('content-fade-in');
        contentZone.style.height = 'auto';
        contentZone.style.overflow = 'visible';
        
        // Appeler le callback si fourni
        if (callback) callback();
    }, 500);
};

function animation(element, i, disappear = false) {
    if (disappear) {
        if (i > -1) {
            element.style.transform = `scale(${i})`;
            element.style.opacity = i;
            i -= 0.05;
        } else {
            return;
        } 
    } else {
        if (i < 1) {
            element.style.transform = `scale(${i})`
            element.style.opacity = i;
            i += 0.05;
        } else return;       
    }
    requestAnimationFrame(() => animation(element, i, disappear))
}

// ========================================
// FONCTIONS DE RENDU ET AFFICHAGE
// ========================================

const renderMember = (member) => {
    const memberDiv = document.createElement('div');
    memberDiv.className = 'member-card';
    
    // Détecter si on est sur mobile
    const isMobile = window.innerWidth <= 650;
    
    memberDiv.innerHTML = `
        <div class="member-info">
            <div class="member-avatar">${member.profil_picture ? member.profil_picture : (member.username.charAt(0).toUpperCase() + member.username.charAt(1).toUpperCase())}
            </div>
            <div class="member-details">
                <div class="member-header">
                    <h4 class="flexDivIcon" style="gap:10px;">${member.username} ${team.leader_id === member.id ? '<i data-lucide="crown" class="crown-icon"></i>' : ''} <span class="role-badge ${team.leader_id === member.id ? 'captain' : 'member'} ">${team.leader_id === member.id ? 'Capitaine' : 'Membre'}</span></h4>                    
                </div>
                <p>${member.special_comp}</p>
            </div>
        </div>
        ${(team.leader_id !== member.id && userConnected.id === team.leader_id) ? `<div class="flexDivIcon promoteRemoveBtn" style="gap:10px;"><button class="promoteLeaderBtn flexDivIcon" onclick="handlePromoteLeader(${member.id})"><i data-lucide="crown" width="15" height="15"></i><span>Promouvoir leader</span></button> <button class="removeMemberBtn flexDivIcon" onclick="handleRemoveMember(${member.id})"><i data-lucide="user-minus" width="15" height="15"></i><span>Retirer</span></button></div>` : ''}
    `;
    return memberDiv;
};

const renderSettings = () => {
    const settingContent = `
    <form action="" method="" >
        <div class="formField">
                <label for="name">Nom de l'equipe</label> <br>
                <input type="text" id="name" value="${team.name ? team.name : ''}" name="name"  class="editInput">
            </div>

            <div class="formField">
                <label for="description">Description</label> <br> 
                <textarea name="description" id="description" class="editInput" placeholder="${team.description ? '' : 'Create your team description here...'}">${team.description ? team.description : ''}</textarea>
            </div>
            <button type="submit" disabled class="submitBtn">&check; Enregistrer les modifications</button>
        <hr style="margin:30px; width:102%; border-color:gray; opacity:0.3;">
          </form>
        <div>
            <h3 class="flexDivIcon" style="justify-content:flex-start; color:hsl(4, 100%, 67%); font-size:1.5em;">
                <i data-lucide="trash-2"></i>
                <span>Zone de danger</span>
            </h3>

            <div class="dangerBtnZone">
            <button class="flexDivIcon dangerBtn" style="background:hsl(4, 100%, 67%); color:#fff;">
                <i data-lucide="trash-2"></i>
                <p>Dissoudre l'équipe</p>
            </button>
            <button class="flexDivIcon dangerBtn code" style="background:hsl(222, 85%, 7%); color:#fff;">
              <i data-lucide="copy"></i>
              <p>Nouveau code d'invitation</p>
            </button>
            </div>
        </div>

        <div class="invit">
            <p style=":#94A3B8;">Code d'invitation actuel:</p>
            <div class="invitCode" ><p>${team.invitation_code}</p>
            <i data-lucide="copy" stroke="white" id="copy" style="border-radius:10px; padding:5px;"></i></div>
        </div>
    `;
    return settingContent;
};

const renderJoinRequestsContent = () => {
    if (joinRequests.length === 0) {
        return `
            <div class="no-requests">
                <i data-lucide="inbox" style="width: 48px; height: 48px; color: var(--text-secondary);"></i>
                <p>Aucune demande d'adhésion en attente.</p>
            </div>
        `;
    }

    console.log(joinRequests);
    return joinRequests.map(request => {
        const initials = request?.fullname?.split('')?.map(n => n[0])?.join('')?.toUpperCase();

        return `
            <div class="request-card">
                <div class="request-info">
                    <div class="request-avatar">${initials}</div>
                    <div class="request-details">
                        <h4>${request.username}</h4>
                        <p class="specialty">${request.special_comp}</p>
                        <p class="time-ago">Demande reçue il y a ${request.timeAgo}</p>
                    </div>
                </div>
                <div class="request-actions">
                    <button class="btn-accept" onclick="handleJoinRequest(${request.id}, 'accept')">
                        <i data-lucide="check"></i>
                        Accepter
                    </button>
                    <button class="btn-refuse" onclick="handleJoinRequest(${request.id}, 'refuse')">
                        <i data-lucide="x"></i>
                        Refuser
                    </button> 
                </div>
            </div>
        `;
    }).join('');
};

const loadTeamMembers = () => {
    const membersList = document.querySelector('.members-list');
    if (!team.members || team.members.length === 0) {
        if (membersList) {
            membersList.innerHTML = Array(3).fill(`<div class="member-card"><span class="skeleton avatar"></span><div style="flex:1;padding-left:10px;"><span class="skeleton text" style="width:60%;"></span><span class="skeleton text" style="width:40%;"></span></div></div>`).join('');
        }
        return;
    }
    membersList.innerHTML = team.members.map(member => renderMember(member).outerHTML).join('');
    document.querySelector('.members-header span').textContent = team.members.length;
    lucide.createIcons();
    
    // Ajouter un écouteur pour le redimensionnement de la fenêtre
    const updateButtonsOnResize = () => {
        const membersList = document.querySelector('.members-list');
        if (membersList) {
            membersList.innerHTML = team.members.map(member => renderMember(member).outerHTML).join('');
            lucide.createIcons();
        }
    };
    
    // Ajouter l'écouteur seulement s'il n'existe pas déjà
    if (!window.membersResizeListener) {
        window.membersResizeListener = updateButtonsOnResize;
        window.addEventListener('resize', window.membersResizeListener);
    }
};

// ========================================
// FONCTIONS DE GESTION DES ÉVÉNEMENTS
// ========================================

const handleRemoveMember = async (id) => {
    const removeMember = await manageOverviewData.deleteMember(id);
    if (removeMember.success) {
        let name;
        team.members.forEach(member => {
            if (member.id === id) {
                name = member.username;
            }
        });
        showNotification('Success !',`${name} a été retiré de l'équipe.`, 'info');
        window.location.reload();
    }
};

const handlePromoteLeader = async (id) => {
    const promoteLeader = await manageOverviewData.promoteLeader(id);
    let name;
    team.members.forEach(member => {
        if (member.id === id) {
            name = member.username;
        }
    });
    if (promoteLeader.success) {
        showNotification('Success !',`${name} est le nouveau leader de l'équipe.`, 'info');
        window.location.reload();
    } else {
        showNotification('Echec de la promotion !', `Erreur lors de la promotion de ${name} en tant que leader.`, 'error');
    }
};

const handleJoinRequest = async(id, action, validate) => {

    if (!validate) {
    const modale=createModal(`
        <div>
            <h3>${action==='accept'? "Accepter" : "Refuser"} cette demande ?</h3>
            <div class="flexDivIcon" style="gap:10px; margin-top:10px;">
            <button type="button" style="padding:10px 20px; border-radius:10px;" onclick="handleJoinRequest(${id}, '${action}',true)">Oui</button>
            <button type="button" style="padding:10px 20px; border-radius:10px;" onclick="closeModal()">Non</button>
            </div>
        </div>
    `);
    document.body.insertAdjacentElement('beforeend', modale);
    modale.showModal();
    requestAnimationFrame(() => animation(modale, 0));
    lucide.createIcons();
    return; 
  }
  closeModal();
    const requestIndex = joinRequests.findIndex(r => r.id === id);
    if (requestIndex === -1) return;

    const animateJoinRequest = (thisId) => {
    // Trouver l'élément DOM de la demande
    const requestCards = document.querySelectorAll('.request-card');
    const targetCard = Array.from(requestCards).find(card => 
        card.querySelector('h4').textContent === joinRequests.find(r => r.id === thisId).username
    );

    if (targetCard) {
        // Récupérer la hauteur actuelle de l'élément
        const currentHeight = targetCard.offsetHeight;
        
        // Animation de disparition smooth
        targetCard.style.transition = 'all 0.5s ease-in-out';
        targetCard.style.height = currentHeight + 'px'; // Définir la hauteur actuelle
        targetCard.style.overflow = 'hidden';
        
        // Petit délai pour éviter un démarrage brusque
        setTimeout(() => {
            targetCard.style.opacity = '0';
            targetCard.style.height = '0';
            targetCard.style.margin = '0';
            targetCard.style.padding = '0';
        }, 50);
    }
}


    if (action === 'accept') {  
        // Ajouter le membre à l'équipe
         await manageOverviewData.acceptRequest(id);
    } else {
        // Refuser la demande
          await manageOverviewData.deleteRequest(id);
    }

    // Supprimer la demande après l'animation
    setTimeout(() => {
        // Supprimer la demande du tableau
        animateJoinRequest(id);
        joinRequests.splice(requestIndex, 1);
        
        // Si c'était la dernière demande, utiliser l'animation de chargement
        if (joinRequests.length === 0) {
            const newRequestsContent = `
                <div class="requests-container">
                    <div class="flexDivIcon" style="justify-content:flex-start; margin-bottom:30px;">
                        <i data-lucide='clock' class="icon" style=" color:var(--blue); background:var(--icon-yellow-bg);"></i>
                        <h3>Demandes d'adhésion</span></h3>
                     </div>
                    <div class="requests-list">
                        <div class="no-requests">
                            <i data-lucide="inbox" style="width: 48px; height: 48px; color: var(--text-secondary);"></i>
                            <p>Aucune demande d'adhésion en attente.</p>
                        </div>
                    </div>
                </div>
            `;
            
            animateContentChange(newRequestsContent, () => {
                lucide.createIcons();
            });
        } else {
            // Mettre à jour l'affichage directement sans animation de chargement
            const requestsList = document.querySelector('.requests-list');
            if (requestsList) {
                const requestsHTML = joinRequests.map(request => {
                    const initials = request.username.split(' ')
                        .map(n => n[0])
                        .join('')
                        .toUpperCase();

                    return `
                        <div class="request-card">
                            <div class="request-info">
                                <div class="request-avatar">${initials}</div>
                                <div class="request-details">
                                    <h4>${request.username}</h4>
                                    <p class="specialty">${request.special_comp}</p>
                                    <p class="time-ago">Demande reçue il y a ${request.timeAgo}</p>
                                </div>
                            </div>
                            <div class="request-actions">
                                <button class="btn-accept" onclick="handleJoinRequest(${request.id}, 'accept',false)">
                                    <i data-lucide="check"></i>
                                    Accepter
                                </button>
                                <button class="btn-refuse" onclick="handleJoinRequest(${request.id}, 'refuse',false)">
                                    <i data-lucide="x"></i>
                                    Refuser
                                </button> 
                            </div>
                        </div>
                    `;
                }).join('');
                
                lucide.createIcons();
            }
        }
        
        // Mettre à jour le compteur de demandes
        const requestCount = document.getElementById('requestNumber');
        if (requestCount) {
            requestCount.textContent = joinRequests.length > 0 ? `(${joinRequests.length})` : '';
        }
    }, 300); // Attendre la fin de l'animation (500ms)

};

const handleTabClick = () => {
    // Créer l'indicateur de navigation
    const indicator = createNavIndicator();
    
    // Positionner l'indicateur sur le premier élément actif
    const activeLi = document.querySelector('li.focus');
    if (activeLi) {
        // Utiliser requestAnimationFrame pour s'assurer que l'indicateur est créé
        requestAnimationFrame(() => {
            moveIndicator(activeLi, indicator);
        });
    }

    listItems.forEach((item, index) => {
        item.addEventListener('click', async () => {
            // Retirer la classe 'focus' de tous les li
            listItems.forEach(li => li.classList.remove('focus'));
            
            // Ajouter la classe 'focus' à l'élément cliqué
            item.classList.add('focus');

            // Déplacer l'indicateur vers l'élément cliqué avec un délai pour s'assurer que le DOM est mis à jour
            requestAnimationFrame(() => {
                moveIndicator(item, indicator);
            });

            // Mettre à jour le contenu en fonction de l'index
            switch (index) {
                case 0: // Détails
                    animateContentChange(tabContents.details, () => {
                    });
                    break;
                case 1: // Membres
                    animateContentChange(tabContents.members, () => {
                        loadTeamMembers();
                    });
                    break;
                case 2: // Paramètres
                    animateContentChange(tabContents.settings, () => {
                        handleSettingsAction();
                    });
                    break;
                case 3: // Demandes
                    await getJoinRequests();
                    renderJoinRequestsContent();
                    
                    animateContentChange(tabContents.requests, () => {
                        lucide.createIcons();
                    });
                    break;
            }
        });
    });
};

const handleAboutSection = () => {
    // Retirer la classe 'focus' de tous les li
    listItems.forEach(li => li.classList.remove('focus'));
    
    // Ajouter la classe 'focus' à l'élément cliqué
    listItems[2].classList.add('focus');

    // Déplacer l'indicateur vers l'élément cliqué
    const indicator = document.querySelector('.nav-indicator');
    if (indicator) {
        requestAnimationFrame(() => {
            moveIndicator(listItems[2], indicator);
        });
    }

    animateContentChange(tabContents.settings, () => {
        handleSettingsAction();
        lucide.createIcons();
        document.getElementById('description').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
        setTimeout(() => document.getElementById('description').focus(), 500);
    });
};

// ========================================
// FONCTIONS DE MODALES ET INTERFACE
// ========================================

const createModal = (content) => {
    const modal = document.createElement('dialog');
    modal.innerHTML = content;
    modal.style = `border:solid 0.4px var(--border); padding:20px; border-radius:10px; margin:auto; background:var(--background); color:white; position:fixed;`;

    // Create and append blur overlay
    const overlay = document.createElement('div');
    overlay.style = 'position:fixed; top:0; left:0; width:100%; height:100%; backdrop-filter:blur(5px); background:rgba(2, 2, 2, 0.3); z-index:999; transition:all 0.5s ease-in-out; opacity:0;';
    overlay.id = 'modalOverlay';
    document.body.appendChild(overlay);

    // Trigger reflow to ensure the transition works
    overlay.style.opacity = '1';

    return modal;
};

const closeModal = async () => {
    const modal = document.querySelector('dialog');
    const overlay = document.getElementById('modalOverlay');
    overlay.style.opacity = '0';
    requestAnimationFrame(() => animation(modal, 1, true));
    setTimeout(() => {
        modal.remove();
        overlay.remove();
        document.body.style = "overflow-y:visible;";
    }, 500);
};

const invitUser = () => {

    if(userConnected.id === team.leader_id){
    content = `
    <div id="invitModale">
                <div style="margin-bottom:20px;" id="invitHeader">
                    <div id="invitTitle" class="flexDivIcon">
                        <i data-lucide="user-plus"></i>
                        <p>Inviter un utilisateur</p>
                    </div>
                    <div id="invitClose">
                        <i data-lucide="x"></i>
                    </div>
                </div>
                <p style="text-align:center; font-size:0.9em; color:var(--text-secondary); ">Partagez ce code d'invitation pour permettre à quelqu'un de <br> rejoindre votre équipe.</p>

                <div style="text-align:center; font-size:1.5em; background:var(--background); padding:25px 10px; border-radius:10px; width:90%; margin:20px auto; border:dashed 2px var(--blue);">
                ${team.invitation_code}
                </div>

                <button class="flexDivIcon copyInvitCodeBtn" style=" width:100%; padding: 10px 0; background: var(--blue); border-radius: 10px; border:none;color:white;" onclick="copyInvitCode()">
                    <i data-lucide="copy"></i>  
                    <span>Copier le code</span>
                </button>
        </div>`
     
    const invitModal = createModal(content);
    document.body.insertAdjacentElement('beforeend', invitModal);
    invitModal.showModal();
    
    requestAnimationFrame(() => animation(invitModal, 0));
    // Initialiser les icônes Lucide dans la modale
    lucide.createIcons();
    document.querySelector('#invitClose').onclick = () => closeModal();
    }else{
        showNotification('Echec de l\'invitation !','Vous n\'avez pas les permissions pour inviter un utilisateur.', 'error');
    }
};

const copyInvitCode = async () => {
    navigator.clipboard.writeText(team.invitation_code)
    .then(() => {
        showNotification('Success !','Code copié avec succès.', 'info');
    })
    .catch(() => {
        showNotification('Echec de la copie !','Erreur lors de la copie du code.', 'error');
    });
}
window.onkeydown=(e)=>{
    if(e.key.toUpperCase()==='V' && e.ctrlKey){
        if(localStorage.getItem('invitation_code')){
           document.activeElement.value+=localStorage.getItem('invitation_code');
        }
    }
}

// const handleSettingsAction = () => {
//     const deleteTeam = document.querySelector('.dangerBtn');

//     deleteTeam.onclick = () => {
//         const content = `
//        <h2 style="margin-bottom:10px;">Etes-vous absolument sûr?</h2>
//        <p style="color:var(--text-secondary); font-size:0.9em;">Cette action ne peut pas être annulée. Cela supprimera définitivement <br> l'équipe et retirera tous les membres de l'équipe.<p>

//        <div class="flexDivIcon" style="justify-content:flex-end; gap:10px; margin-top:15px;">
//        <button class="cancelDeleteTeamBtn" style="background:var(--background);">Annuler</button>
//        <button class="deleteTeamBtn" style="background:hsl(4, 100%, 67%);"onclick='handleDeleteTeam()' >Dissoudre l'équipe</button>
//        </div>
//        `;
//         const deleteModal = createModal(content);

//         document.body.appendChild(deleteModal);
//         document.querySelector('.cancelDeleteTeamBtn').onclick = () => closeModal(deleteModal);

//         document.body.style = "overflow-y:hidden;";    
//         deleteModal.showModal();
//         requestAnimationFrame(() => animation(deleteModal, 0));
//     }

//     const newInvitCodeBtn = document.querySelector('.code');
//     newInvitCodeBtn.onclick = () => {
//         const newCodeDiv = document.querySelector('.invitCode p');
//         newCodeDiv.textContent = getRandomInvitCode();
//     }
    
//     const submitBtn = document.querySelector('.submitBtn');
//     const name = document.getElementById('name');
//     const nameValue = name.value;
//     const description = document.getElementById('description');
//     const descriptionValue = description.value;
//     name.oninput = () => {
//         if(nameValue === name.value && descriptionValue === description.value){
//             submitBtn.disabled = true;
//         }else{
//             submitBtn.disabled = false;
//         }
//     }
//     description.oninput = () => {
//         if(descriptionValue === description.value && nameValue === name.value){
//             submitBtn.disabled = true;
//         }else{
//             submitBtn.disabled = false;
//         }
//     }
    

//     const getRandomInvitCode = () => {
//         const letter = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
//         let code = [];
//         for (let i = 0; i < 13; i++) {
//             code[i] = letter[Math.floor(Math.random() * 36)] + ((i % 4 === 0 && i !== 0 && i !== 12) ? '-' : '');
//         }
//         return code.join('');
//     }
//     const form = document.querySelector('form');
//     form.onsubmit = modifyForm;
// };
//Note a Seathiel de Florent j'ai lu ton code juste pour comprendre comment fonctionnait le code j'ai du retirer la generation du code parce que le code est genere par le serveur

const handleSettingsAction = () => {
    const deleteTeam = document.querySelector('.dangerBtn');
    const newInvitCodeBtn = document.querySelector('.code');
    const submitBtn = document.querySelector('.submitBtn');
    const name = document.getElementById('name');
    const description = document.getElementById('description');
    const form = document.querySelector('form');

    const initialName = name.value;
    const initialDescription = description.value;

    deleteTeam.onclick = () => {
        const content = `
            <h2 style="margin-bottom:10px;">Êtes-vous absolument sûr ?</h2>
            <p style="color:var(--text-secondary); font-size:0.9em;">Cette action ne peut pas être annulée. Cela supprimera définitivement <br> l'équipe et retirera tous les membres de l'équipe.</p>
            <div class="flexDivIcon" style="justify-content:flex-end; gap:10px; margin-top:15px;">
                <button class="cancelDeleteTeamBtn" style="background:var(--background);">Annuler</button>
                <button class="deleteTeamBtn" style="background:hsl(4, 100%, 67%);" onclick='handleDeleteTeam()'>Dissoudre l'équipe</button>
            </div>
        `;
        const deleteModal = createModal(content);
        document.body.appendChild(deleteModal);
        document.querySelector('.cancelDeleteTeamBtn').onclick = () => closeModal();
        document.body.style = "overflow-y:hidden;";
        deleteModal.showModal();
        requestAnimationFrame(() => animation(deleteModal, 0));
    };

    newInvitCodeBtn.onclick = async () => {
        const newCodeDiv = document.querySelector('.invitCode p');
        try {
            const response = await manageOverviewData.updateInvitCode();
            if (response && response.success && response.data && response.data.invitation_code) {
                newCodeDiv.textContent = response.data.invitation_code;
                team.invitation_code = response.data.invitation_code; // Mettre à jour la variable globale
                showNotification('Modification effectuée !','Le code d\'invitation a été mis à jour avec succès.', 'info');
            } else {
                console.warn('Données manquantes dans la réponse API:', response);
                showNotification('Modification echouée !','Le code d\'invitation a été mis à jour, mais le nouveau code n\'est pas disponible.', 'warning');
            }
        } catch (error) {
            console.error('Erreur lors de la mise à jour du code:', error);
            showNotification('Modification echouée !', error.message, 'error');
        }
    };

    const updateSubmitButton = () => {
        submitBtn.disabled = (name.value === initialName && description.value === initialDescription);
    };

    name.oninput = updateSubmitButton;
    description.oninput = updateSubmitButton;

    form.onsubmit = modifyForm;
};

// ========================================
// FONCTIONS DE GESTION DES DONNÉES
// ========================================

const defineTeamNameOverviewData = async () => {
    await getTeam();
    await getJoinRequests();
    console.log(team);
    const isMember=team.members.find(member=>member.id===userConnected.id);

    const teamAvatar = document.getElementById('teamAvatar');
    teamAvatar.textContent=team.name[0].toUpperCase()+team.name[1].toUpperCase();
    tabContents = {
        details: `
            <div class="aboutFirstContainer">
                <p class="flexDivIcon" style="gap:20px;">
                    <i data-lucide="shield" class="icon" style="color:var(--blue);"></i>
                    <strong>A propos de nous</strong>
                </p>      
                ${
                    isMember ? `
                    <button id="editBtn" class="flexDivIcon" style="gap:10px; color:white;" onclick="handleAboutSection();">
                    <i data-lucide="edit"></i>
                    <span>${team.description ? 'Modifier' : 'Créer une description'}</span>
                </button>` : ''
                }
            </div>                
    
            <p id="aboutText">                
                ${team.description ? team.description : 'No description. Create one.'}
            </p>
        `,
        members: `
            <div class="members-container">
                <div class="members-header">
                 <div class="flexDivIcon" style="gap:25px;">
                         <i data-lucide='users' class="icon" style="color:var(--blue);"></i>
                    <h3>Membres de l'équipe (<span></span>)</h3>
                 </div>
                </div>
                <div class="members-list">
                    ${team.members.map(member => renderMember(member).outerHTML).join('')}
                </div>
            </div>
        `,
        settings: `
            <div class="settings-container">
            <div class="flexDivIcon" style="justify-content:flex-start;" >
                <i data-lucide='settings' class="icon" style="color:var(--blue);"></i>
                <h3>Paramètres de l'équipe</h3>
              </div>  
                <div class="settings-options">
                    ${renderSettings()}
                </div>
            </div>
        `,
        requests: `
            <div class="requests-container">
                <div class="flexDivIcon" style="justify-content:flex-start; margin-bottom:30px;">
                    <i data-lucide='clock' class="icon" style=" color:var(--yellow); background:var(--icon-yellow-bg);"></i>
                    <h3>Demandes d'adhésion</span></h3>
                 </div>
                <div class="requests-list">
                    ${renderJoinRequestsContent()}
                </div>
            </div>
        `
    };    
};

const getJoinRequests = async () => {
    joinRequests = await manageOverviewData.getAllTeamRequests();
 }

const getTeam = async () => {
    if(team.length === 0){
        const waitTeam = await manageOverviewData.getTeamMembers() || null;
        if (waitTeam) {
            team = waitTeam;
            if (teamName) teamName.textContent = waitTeam.name;
            if (teamCategory) teamCategory.textContent = waitTeam.type;
            if (memberNumber) memberNumber.textContent = team.members.length;
            const aboutText = document.getElementById('aboutText');
            if (aboutText) aboutText.textContent = waitTeam.description;
        }
    }
    
};

const handleDeleteTeam = async () => {
    await manageOverviewData.deleteTeam(teamId);
};

const modifyForm = async (event) => { 
    event.preventDefault();
    const data = {
        name: document.getElementById('name').value,
        description: document.getElementById('description').value
    }
    await manageOverviewData.updateTeam(teamId, data);
};

// ========================================
// FONCTIONS API ET COMMUNICATION
// ========================================

const apiReq = async (apiRoute, method = 'GET', data = null) => {
    const optionRequest = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'credentials': 'include',
        }
    }
    if (data) {
        optionRequest.headers['Content-Type'] = 'application/x-www-form-urlencoded';
        optionRequest.body = new URLSearchParams(data);
    }
    if (method !== 'GET') {
        optionRequest.headers['X-CSRF-Token'] = document.querySelector('input[name="csrf_token"]').value;
    }
    console.log(optionRequest);
    console.log(apiRoute);
    const reponse = 
    await fetch('/api/' + apiRoute, optionRequest)
            .then(rep => rep.json())    
            .catch(err => err);

    return reponse;
};

const manageOverviewData = {
    getTeamMembers: async () => {
        const team = await apiReq(`teams/${teamId}`);
        if (team.success) {
            return team.data;
        }
    },
    deleteTeam: async () => {
        const deleteT = await apiReq(`teams/${teamId}`, 'delete');
        console.log(deleteT);
        if (deleteT.success) {
            showNotification("Suppression effectuée !","L\'équipe a été supprimée avec succès.", 'info');
            window.location.href = '/user/teams';
        }
    },
    deleteMember: async (id) => {
        const deleteM = await apiReq(`teams/${teamId}/members/remove`, 'POST', { user_id: id });
        if (deleteM.success) {
            showNotification("Suppression effectuée !","Le membre a été retiré avec succès.", 'info');
            window.location.reload();
        }
    },
    updateTeam: async (id, data) => {
        
        const updateT = await apiReq(`teams/${id}`, 'POST', data);
        console.log('Réponse complète de l\'API updateTeam:', JSON.stringify(updateT, null, 2));
        if (updateT.success) {
            showNotification('Modification effectuée !','L\'équipe a été mise à jour avec succès.', 'info');
            window.location.reload();
        }else {
            const errorMessage = updateT.errot || 'Erreur inconnue lors de la mise à jour de l\'équipe';
            console.log('Erreur lors de la mise à jour de l\'équipe:', errorMessage);
            showNotification(`Echec de la modification !`,errorMessage, 'error');
        }
    },
    
    // updateInvitCode: async () => {
    //     const updateI = await apiReq(`teams/${teamId}/invit/update`, 'POST');
    //     if (updateI.success) {
    //         showNotification('Le code d\'invitation a été mis à jour avec succès.', 'info');
    //         window.location.reload();
    //     }
    // },
    
    updateInvitCode: async () =>  await apiReq(`teams/${teamId}/invit/update`, 'POST'),

    promoteLeader: async (id) => {
        const promoteLeader = await apiReq(`teams/${teamId}/leader/change`, 'POST', { new_leader_id: id });
        if(promoteLeader.success)
           return promoteLeader;
    },
    acceptRequest: async (id) => {
        const acceptR = await apiReq(`teams/${teamId}/leader/accept`,'POST',{user_id:id});
        if(acceptR.success){
            showNotification(`Demande acceptée !`, acceptR.message || null, 'info');
            window.location.reload();
        }else{
            showNotification(`Erreur lors de l'adhesion.`, acceptR.message || null, 'error');
        }
    },
    deleteRequest: async (id) => {
        const deleteR = await apiReq(`teams/${teamId}/leader/reject`,'POST',{user_id:id});
        if(deleteR.success){
            showNotification(`Demande refusée !`, deleteR.message || null, 'error');
            window.location.reload();
        }else{
            showNotification(`Erreur lors du refus.`, deleteR.message || null, 'error');
        }
    },
    getAllTeamRequests: async () => {
        const teamRequests= await apiReq(`teams/${teamId}/members/requests`);
        if (teamRequests.success) {
            return teamRequests.data;
        }
    }
};

// ========================================
// INITIALISATION
// ========================================

// Initialisation : afficher la section "Détails" par défaut
document.addEventListener('DOMContentLoaded', async () => {
    // S'assurer que le premier li a la classe 'focus'
    if (listItems.length > 0) {
        listItems[0].classList.add('focus');
    }
    showSkeletons(); // Afficher les skeletons avant le chargement
    await defineTeamNameOverviewData();
    await handleNavBar();
    handleTabClick();

    if(userConnected.id === team.leader_id){
        const sectionTeamInfo = document.querySelector('#teamInfo');
        sectionTeamInfo.innerHTML += `
        <button id="invit" class="flexDivIcon" onclick="invitUser()">
            <i data-lucide="user-plus"></i>
            <p>Inviter un utilisateur</p>
        </button>
        `
    }
    // S'assurer que la section about a le contenu par défaut avec animation
    if (aboutSection) {
        animateContentChange(tabContents.details, () => {
            lucide.createIcons();
        });
    }
});

// Ajout : fonctions pour afficher les skeletons
const showSkeletons = () => {
    // Nom équipe, avatar, catégorie, nombre de membres
    if (teamName) teamName.innerHTML = '<div style="display:flex; align-items:center; gap:10px;"> <span class="skeleton title" style="width:120px; margin-bottom:0;"></span>';
    if (teamCategory) teamCategory.innerHTML = '<span class="skeleton text" style="width:80px; margin-top:15px;"></span></div>';
    if (memberNumber) memberNumber.innerHTML = '<span class="skeleton text" style="width:40px;"></span>';
    if (aboutSection) aboutSection.innerHTML = `
        <div class="aboutFirstContainer">
            <span class="skeleton title" style="width:180px;"></span>
            <span class="skeleton btn"></span>
        </div>
        <span class="skeleton text" style="width:100%;height:2.5em; margin-bottom:10px;"></span>
        <span class="skeleton text" style="width:90%;height:2.5em; margin-bottom:10px;"></span>
    `;
    document.querySelector('.name').style.display = 'flex';
    document.querySelector('.name').style.gap = '10px';
    // Liste membres
    const membersList = document.querySelector('.members-list');
    if (membersList) {
        membersList.innerHTML = Array(3).fill(`<div class="member-card"><span class="skeleton avatar"></span><div style="flex:1;padding-left:10px;"><span class="skeleton text" style="width:60%; margin-bottom:8px;"></span><span class="skeleton text" style="width:40%; margin-bottom:8px;"></span></div></div>`).join('');
    }
};

