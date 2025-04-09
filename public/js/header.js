// Start Challenge button click handler
if (document.querySelector('.start-challenge')) {
    document.querySelector('.start-challenge').addEventListener('click', () => {
        window.location.href = '/HACKATHON_ESGIS/public/auth';
    });
}

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

if (fadeElements) {
    fadeElements.forEach(element => {
        heroObserver.observe(element);
    });
}

// Add smooth scroll behavior. La partie a[href^="#"] est un sélecteur CSS qui cible tous les éléments <a> (liens) dont l'attribut href commence par le caractère #
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {

});



// showNotification('Successfully saved!', 'Anyone with a link can now view this file.','success');