document.addEventListener("DOMContentLoaded", function () {
    const signinForm = document.getElementById('signinForm');
    // Init icons on load
    if (window.lucide && typeof lucide.createIcons === 'function') {
        lucide.createIcons();
    }

    // Display server-side notification if any
    try {
        const notifEl = document.getElementById('notification-data');
        if (notifEl) {
            const raw = notifEl.getAttribute('data-notification');
            if (raw && raw !== 'null') {
                const notification = JSON.parse(raw);
                if (notification && typeof window.showNotification === 'function') {
                    showNotification(notification.message || 'Notification', notification.details || '', notification.type || 'info');
                }
            }
        }
    } catch (_) {}

    // Gestionnaire de formulaire de connexion
    signinForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i data-lucide="loader-circle" class="animate-spin"></i> Traitement...';
        lucide.createIcons();

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Erreur de connexion');
            }

            // Stockage des tokens
            if (data.success) {
                console.log(data);
                setFlashMessage('success', data.message, data.username);
                window.location.href = data.redirect;
            } else if (!data.success) {
                showNotification(data.message || "Erreur lors de la connexion", 'Veuillez corriger les erreurs', 'warning');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i data-lucide="send"></i> Se connecter';
                lucide.createIcons();
                return;
            }
        } catch (error) {
            showNotification(error.message, 'Veuillez corriger les erreurs', 'warning');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i data-lucide="send"></i> Se connecter';
            lucide.createIcons();
        }
    });

    // Fonctions d'affichage/masquage des erreurs
    function showError(inputElement, errorElement, message) {
        inputElement.parentElement.classList.add('input-error');
        errorElement.textContent = message;
        errorElement.classList.remove('hidden', 'fade-out');
    }

    function hideError(inputElement, errorElement) {
        inputElement.parentElement.classList.remove('input-error');
        if (errorElement.classList.contains('hidden')) return;
        errorElement.classList.remove('fade-in');
        errorElement.classList.add('fade-out');
        errorElement.addEventListener('animationend', function () {
            errorElement.classList.add('hidden');
            errorElement.classList.remove('fade-out');
        }, { once: true });
    }
});