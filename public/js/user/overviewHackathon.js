let hackathon=null
let userConnected= null;

const apiReq = async (apiRoute, method = 'GET', data = null) => {
    const token = localStorage.getItem('jwt_token') || ''; //ici fallait recuperer le token dans le localStorage pour verifier que c'est bien le leader
    const optionRequest = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Authorization': `Bearer ${token}`
        }
    }
    if (data) {
        optionRequest.headers['Content-Type'] = 'application/x-www-form-urlencoded';
        optionRequest.body = new URLSearchParams(data);
    }
    if (method !== 'GET') {
        optionRequest.headers['X-CSRF-Token'] = document.querySelector('input[name="csrf_token"]').value;
    }
    const reponse = 
    await fetch('/api/' + apiRoute, optionRequest)
            .then(rep => rep.json())
            .catch(err => err);

    return reponse;
};


const getHackathon = async (id)=>{
    const response = await apiReq(`hackathons/${id}`);
    if(response.success){
        hackathon = response.data;
    }

    const organizer= await apiReq(`users/${hackathon['created_by']}`);

    if(organizer && organizer.sucess){
        hackathon['organizer']=organizer.data['fullname'];
    }
}

const renderHackathonTechno=(hackathon)=>{
    return hackathon.map((techno)=> `<p class="techno">${techno}</p>`).join('');
}

const renderHackathonPhases=(phases=[])=>{
    if(phases.length)
    return phases.map((phase)=> `<div class="hackathon-phase">${phase}</div>`).join('');

    return '<p style="color:var(--text-secondary);">Planning détaillé bientot disponible.</p>';
}

const renderHackathonPrize=()=>{
    const prizes=JSON.parse(hackathon.prizes);
    const icon=['🥇', '🥈', '🥉']
    return prizes.map((prize,index)=> 
    `<div class="prize">
        <p>${icon[index] + prize.label} </p>

        <p style="margin-top:20px;">${prize.reward}</p>
    </div>`).join('');
}

const renderRules=()=>{
    const rules=JSON.parse(hackathon.rules);
    return rules.map((rule)=>
    `<div class="rule">
        <div class="flex" style="justify-content:space-between; margin-bottom:10px;">
            <p class="flex" style="width:max-content;">
                <i data-lucide="circle-alert"></i>
                <strong>${rule.title}</strong>
           </p>
            <i data-lucide="chevron-down" class="chevron-icon"></i>
        </div>
        <p class="rule-description">${rule.description}</p>
        <hr style="margin:15px 0; border:0.1px solid var(--text); opacity:0.1;">
    </div>`).join('');
}

const showRules=()=>{
    const rules=document.querySelectorAll('.rule');
    let currentlyOpen = null;
    
    rules.forEach(rule=>{
        const ruleDescription = rule.querySelector('.rule-description');
        const descriptionHeight = ruleDescription.offsetHeight;
        

        // Initialiser l'état fermé avec hauteur 0
        ruleDescription.style.opacity = '0';
        ruleDescription.style.height = '0px';
        ruleDescription.style.padding = '0';
        ruleDescription.style.overflow = 'hidden';
        ruleDescription.style.transition = 'height 0.3s ease-out';
        
        rule.addEventListener('click',()=>{
            const isCurrentlyOpen = rule === currentlyOpen;
            const chevronIcon = rule.querySelector('.chevron-icon');

            // Fermer la règle actuellement ouverte (si différente)
            if (currentlyOpen && currentlyOpen !== rule) {
                const openDescription = currentlyOpen.querySelector('.rule-description');
                const openChevron = currentlyOpen.querySelector('[data-lucide="chevron-down"]');
                
                // Animation de fermeture
                openDescription.style.height = '0px';
                openDescription.style.padding = '0';
                // Rotation du chevron
                openChevron.style.transition = 'transform 0.3s ease';
                openChevron.style.transform = 'rotate(0deg)';
                
                currentlyOpen.classList.remove('active');
                currentlyOpen = null;
            }
            
            // Gérer l'état de la règle cliquée
            if (!isCurrentlyOpen) {
                // Ouvrir la règle
                ruleDescription.style.height = descriptionHeight + 'px';
                ruleDescription.style.transition = 'all 0.3s ease-out';
                ruleDescription.style.opacity = '1';
                ruleDescription.style.padding = '10px';
                // Rotation du chevron
                chevronIcon.style.transition = 'transform 0.3s ease';
                chevronIcon.style.transform = 'rotate(180deg)';
                
                rule.classList.add('active');
                currentlyOpen = rule;
            } else {
                // Fermer la règle actuellement ouverte
                ruleDescription.style.height = '0px';
                ruleDescription.style.transition = 'all 0.3s ease-out';
                ruleDescription.style.opacity = '0';
                ruleDescription.style.padding = '0';
                // Rotation du chevron
                chevronIcon.style.transition = 'transform 0.3s ease';
                chevronIcon.style.transform = 'rotate(0deg)';
                
                rule.classList.remove('active');
                currentlyOpen = null;
            }
        });
    });
}

const getUserConnected = async ()=>{
    const response = await apiReq('users/me');
    if(response.success){
        userConnected = response.data;
    }
}
const createHeader = ()=>{
    console.log(hackathon);
    const header = document.querySelector('header');
    if(new Date(hackathon['start_date']) > new Date()){
    header.innerHTML = `
        <div class="flexDiv rounded">
            <i data-lucide="zap" stroke="#fff"></i>
            <strong>A venir</strong>
        </div>

        <section class="flexDiv">
            <div class="infos">
               <h1>${hackathon['name']}</h1>
               <div class="flexDiv" style="margin: 30px;">
                    <i data-lucide="calendar" stroke="#fff"></i>
                    <p>
                        <span>Du</span> <strong>${new Date(hackathon['start_date']).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' }) + ' à ' + new Date(hackathon['start_date']).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}</strong> <span>au</span> <strong>${new Date(hackathon['end_date']).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' }) + ' à ' + new Date(hackathon['end_date']).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}</strong>
                     </p>
               </div>

               <p class="flexDiv" style="margin: 30px;">
                    <i data-lucide="users" stroke="#fff"></i>
                    <span>${hackathon['teams_count']} équipes inscrites</span>
               </p>

               <p>Build innovation from EsgisHub to the world.</p>
            </div>
            <img src="${hackathon['image']}" alt="Image du hackathon">
        </section> 
    `;
    lucide.createIcons();
}
}

const createMain = ()=>{
    const main = document.querySelector('main');
    main.innerHTML = `
    <div style="width:70%;">
        <div class="description card">
            <div class="flexDiv">
                 <i data-lucide="sparkle" stroke="#fff"></i>
                <h2>Description & objectifs</h2>
            </div>
        <p>${hackathon['description']}</p>
        </div>

        <div class="flex" style="gap:30px;">
            <div class="card" style="text-align:left!important;">
                
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
                <p>Outils/Langages</p>
                <div class="technoDiv">
                    ${renderHackathonTechno(['Python', 'Gulp', 'Javascript'])}
                </div>
            </div>

        </div>
            <div class="card">
                <p class="flex" >
                    <i data-lucide="clock" stroke="var(--blue)" class="icon" style="background:var(--blue-opac);"></i>
                    <strong style="font-size:1.6em;">Phases du hackathon</strong>
                </p>

                <div style="margin:30px 0 20px;">${renderHackathonPhases()}</div>
            </div>

            <div class="card" style="margin:20px 0;">
                <p class="flex">
                    <i data-lucide="trophy" class="icon" stroke="yellow" style="background:var(--icon-yellow-bg);"></i>
                    <strong style="font-size:1.6em;">Récompenses</strong>
                </p>

                <div class="flex" style="margin-top:20px;">
                    ${renderHackathonPrize()}
                </div>
            </div>

            <div class="card">
                <p class="flex">
                    <i data-lucide="circle-check-big" stroke="var(--blue)" class="icon" style="background:var(--blue-opac);"></i>
                    <strong style="font-size:1.4em;">Règlements du hackathon</strong>
                </p>
                
                <div style="margin:20px auto; width:98%;">${renderRules()}</div>
                
            </div>
    </div>

    <div class="inscription">
        <div class="card">
       <div class="flexDiv" style="justify-content: left;">
            <i data-lucide="users" class="icon" stroke="#fff"></i>
            <h2>Inscription</h2>
       </div>

       <p style="font-size: 1.2rem;">Gérez votre participation à ce hackathon.</p>
         
       <hr style="margin: 30px 0 20px; border: 0.1px solid var(--text); opacity: 0.1;">

       <button class="btn flexDiv">
            <i data-lucide="users" stroke="#fff"></i>
            <strong style="color:var(--text);">Inscrire mon équipe</strong>
       </button>

       <p class="subtitle">
            En tant que leader, vous pouvez inscrire votre équipe à ce hackathon.
       </p>

       <hr style="margin: 30px 0 20px; border: 0.1px solid var(--text); opacity: 0.1;">

        <p class="subtitle" style="text-align: left;">
            Organisateur: <strong>${hackathon['organizer']}</strong>
        </p>

        <p class="subtitle" style="text-align: left;">
            Lieu: <strong>${hackathon['location']}</strong>
        </p>

        </div>

        <div class="card" style="color:var(--text-secondary); font-size:0.9em; margin-top:20px;">
            <p class="flexDiv" style="transform:translateX(-10px);">
                <i data-lucide="clock" stroke="white" class="icon" style="background:#c23c3c;"></i>
                <strong style="color:#c23c3c;">Date limite d'inscription:</strong> 
            </p>
            <strong style="margin-left:10px; text-align:center;">${hackathon['registration_deadline'].replace(' ', ' à ')}</strong>
        </div>
    </div>
    `;
}

window.addEventListener('DOMContentLoaded', async ()=>{
    await getUserConnected();
    await getHackathon(window.location.href.split('/').pop());
    console.log(userConnected);
    createHeader();
    createMain();
    showRules();
    // if(document.querySelector('img'))
    // document.querySelector('img').onerror=(e)=> e.target.style.display = 'none';
    lucide.createIcons();
});