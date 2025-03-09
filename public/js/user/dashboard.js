document.addEventListener('DOMContentLoaded', () => {
    // lucide initiating
    lucide.createIcons();

    // Add smooth scroll behavior. La partie a[href^="#"] est un sélecteur CSS qui cible tous les éléments <a> (liens) dont l'attribut href commence par le caractère #
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
});