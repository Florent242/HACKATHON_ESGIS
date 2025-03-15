document.addEventListener('DOMContentLoaded', () => {
    // lucide initiating
    lucide.createIcons();

    // Add smooth scroll behavior
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    // Initialiser le dashboard
    initializeDashboard();
});

// Fonction pour initialiser le dashboard
function initializeDashboard() {
    // Récupérer les informations de l'utilisateur
    getUserInfo();
    // Récupérer les statistiques
    // getStatistics();
    // Récupérer les notifications
    // getNotifications();
}

// Fonction pour récupérer les informations de l'utilisateur
function getUserInfo() {
    fetch('/HACKATHON_ESGIS/public/api/users/2', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
            // ,
            // 'Authorization': 'Bearer ' + localStorage.getItem('token') 
        }
    })
    .then(response => {
        console.log('Status:', response.status);
        console.log('Headers:', response.headers);
        return response.json();
    })
    .then(data => {
        if (data.length === 0) {
            console.log(data);
            console.error('Aucune donnée utilisateur trouvée');
            return;
        }
        console.log(data);
        // Mettre à jour le nom d'utilisateur
        document.querySelectorAll('.Username').forEach(element => element.textContent = `${data.username}`);
        // Mettre à jour les informations de l'utilisateur
        document.querySelectorAll('.Email').forEach(element => element.textContent = `${data.email}`);
    })
    .catch(error => console.error('Error:', error));
}

// Fonction pour récupérer les statistiques
function getStatistics() {
    fetch('/HACKATHON_ESGIS/public/api/user/statistics', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
            // ,
            // 'Authorization': 'Bearer ' + localStorage.getItem('token') 
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log(data);
        // Mettre à jour les statistiques
        document.querySelector('#number-dev-challenges').textContent = data.numberDevChallenges;
        document.querySelector('#number-hacking-challenges').textContent = data.numberHackingChallenges;
        document.querySelector('#number-dev-challenges-on').textContent = data.numberDevChallengesOn;
        document.querySelector('#number-hacking-challenges-validate').textContent = data.numberHackingChallengesValidate;
        document.querySelector('#number-submitted-projects').textContent = data.numberSubmittedProjects;
        document.querySelector('#total-points').textContent = data.totalPoints;
        document.querySelector('#dev-stat').textContent = data.devStat;
        document.querySelector('#hacking-stat').textContent = data.hackingStat;
        document.querySelector('#total-points-stat').textContent = data.totalPointsStat;
    })
    .catch(error => console.error('Error:', error));
}

// Fonction pour récupérer les notifications
function getNotifications() {
    fetch('/api/user/notifications', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        }
    })
    .then(response => response.json())
    .then(data => {
        // Mettre à jour les notifications
        const notificationsContainer = document.querySelector('.notifications-list');
        notificationsContainer.innerHTML = data.map(notification => `
            <div class="notification-item" data-id="${notification.id}">
                <p>${notification.message}</p>
                <span class="timestamp">${notification.timestamp}</span>
            </div>
        `).join('');
    })
    .catch(error => console.error('Error:', error));
}

// Fonction pour marquer une notification comme lue
function markNotificationAsRead(notificationId) {
    fetch(`/api/user/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        }
    })
    .then(response => response.json())
    .then(data => {
        // Mettre à jour l'interface
        const notification = document.querySelector(`[data-id="${notificationId}"]`);
        if (notification) {
            notification.classList.add('read');
        }
    })
    .catch(error => console.error('Error:', error));
}