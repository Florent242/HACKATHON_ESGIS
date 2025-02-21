// Start Challenge button click handler
if (document.querySelector('.start-challenge')) {
    document.querySelector('.start-challenge').addEventListener('click', () => {
        window.location.href = '/HACKATHON_ESGIS/public/signup';
    });
}

// Notification button click handler
const notificationBtn = document.querySelector('.notification-btn');
notificationBtn.addEventListener('click', () => {
    alert('Notifications coming soon!');
});
