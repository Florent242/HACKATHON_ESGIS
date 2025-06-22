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
}
const getUserConnected = async ()=>{
    const response = await apiReq('users/me');
    if(response.success){
        userConnected = response.data;
    }
}
const createHeader = ()=>{
    const header = document.querySelector('header');
    console.log(new Date(hackathon['start_date']))
    if(new Date(hackathon['start_date']) > new Date()){
    header.innerHTML = `
        <div class="flexDivIcon">
            <i data-lucide="zap" class="icon" stroke="#fff"></i>
            <strong>A venir</strong>
        </div>
    `;
}
}
window.addEventListener('DOMContentLoaded', async ()=>{
    await getUserConnected();
    await getHackathon(window.location.href.split('/').pop());
    console.log(hackathon);
    console.log(userConnected);
    createHeader();
});