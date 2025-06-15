<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact</title>
    <link rel="stylesheet" href="/css/styles/contact.css">
    <link rel="stylesheet" href="/css/styles/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.280.0/dist/umd/lucide.min.js"></script>

</head>

<body>
    <?php require_once '../includes/header.php'; ?>
    <div class="contact" style="margin-top: 100px; display: flex; justify-content: center; align-items: center; flex-direction: column; padding-left: 20px; padding-right  : 20px; margin-left: 25%; margin-right: 25%;">
        <div class="head" style="height: 100px; width: 100%;">
            <h1 style="text-align: center;">Contactez-nous</h1>
            <p style="text-align: center;">Nous sommes là pour vous aider</p>
        </div>

        <div class="form-container Les_form">
            <div class="message">
                <form action="">
                    <h3 style="text-align: center; margin-bottom: 30px;">Envoyez-nous un message</h3>
                    <div class="form-container">
                        <div class="input-group">
                            <label for="name">Nom</label>
                            <input type="text" id="name" placeholder="Votre nom...">
                        </div>
                        <div class="input-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" placeholder="esgishub@gmail.com..."><br>
                        </div>
                    </div>
                    <label for="subject">Sujet</label><br>
                    <input type="text" id="subject" placeholder="Sujet du message..."><br><br>
                    <label for="message">Message</label><br>
                    <textarea id="message" rows="4" placeholder="Votre message..."></textarea><br><br>
                    <button class="button" type="submit">Envoyer le message</button>
                </form>
            </div>

            <div class="message1" style="margin-left: 40px; margin-right: 40px;">
                <div class="titre">
                    <span class="icon" style="margin-right: 10px; margin-bottom: 30px;"><i data-lucide="map-pin"></i></span>
                    <div >
                        <h4 style="margin-top: 5px;">Notre adresse</h4>
                        <p>Jericho Cotonou, Benin</p>
                    </div>
                </div>
                <div class="titre">
                    <span class="icon" style="margin-right: 10px; margin-bottom: 30px;"><i data-lucide="mail"></i></span>
                    <div>
                        <h4 style="margin-top: 5px;">Email</h4>
                        <p>esgishub@gmail.com</p>
                    </div>
                </div>
                <div class="titre">
                    <span class="icon" style="margin-right: 10px; margin-bottom: 30px;"><i data-lucide="phone"></i></span>
                    <div>
                        <h4 style="margin-top: 5px;">Téléphone</h4>
                        <p>+229 61 XX XX XX</p>
                    </div>
                </div>
                <div class="support-container">
                    <div class="titre">
                        <span class="icon" style="margin-right: 10px; margin-bottom: 30px;"><i data-lucide="message-square"></i></span>
                        <div class="text support">
                            <h4 style="margin-bottom: 5px;">Support en direct</h4>
                            <p >Notre équipe est disponible 24/7 pour vous aider</p>
                        </div>
                        <div class="button-container" style="margin-left: 40px; margin-top: 35px;">
                            <button class="button-onclick" onclick="alert('Support en direct indisponible pour le moment')">Démarrer le chat</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
        window.addEventListener("load", function() {
            lucide.createIcons();
        });
    </script>
</body>

</html>