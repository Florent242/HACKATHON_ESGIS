document.addEventListener("DOMContentLoaded", function() {
    const tabLogin = document.getElementById("tab-login");
    const tabRegister = document.getElementById("tab-register");
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");

    // Afficher le formulaire de connexion par défaut
    loginForm.style.display = "block";
    
    tabLogin.addEventListener("click", function() {
        tabLogin.classList.add("active");
        tabRegister.classList.remove("active");
        loginForm.style.display = "block";
        registerForm.style.display = "none";
    });

    tabRegister.addEventListener("click", function() {
        tabRegister.classList.add("active");
        tabLogin.classList.remove("active");
        registerForm.style.display = "block";
        loginForm.style.display = "none";
    });
});