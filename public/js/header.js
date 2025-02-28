// lucide initiating
lucide.createIcons();

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


/* Handle scroll annimations for elements */
// Select all element that have the .fade-in class for the animation when they are visible
const fadeElements = document.querySelectorAll('.fade-in, .fade-out, .fade-in-left, .fade-in-right');// Intersection Observer to trigger the animation when the element is visible...hehe that's cool tho
const heroObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
        else {
            entry.target.classList.remove('visible');
        }
    });
}, {
    root: null, // Utilise la fenêtre de visualisation
    threshold: 0.25 // Déclenche l'événement lorsque 10% de l'élément est visible
});

fadeElements.forEach(element => {
    heroObserver.observe(element);
});