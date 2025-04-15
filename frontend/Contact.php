<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact</title>
    <link rel="stylesheet" href="../public/css/styles/contact.css">
    <link rel="stylesheet" href="../public/css/styles/header.css">
    <link rel="stylesheet" href="../public/css/dist/output.css">
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.280.0/dist/umd/lucide.min.js"></script>

</head>

<body>
    <?php require_once '../includes/header.php'; ?>
    <div class="contact">
        <div class="head">
            <h1>Contactez-nous</h1>
            <p>Nous sommes là pour vous aider</p>
        </div>

        <div class="Les_form">
            <div class="message">
                <form>
                    <h3>Envoyez-nous un message</h3>
                    <div class="form-container">
                        <div class="input-group">
                            <label for="name">Nom</label>
                            <input type="text" id="name" placeholder="Votre nom...">
                        </div>
                        <div class="input-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" placeholder="votre@gmail.com..."><br>
                        </div>
                    </div>
                    <label for="subject">Sujet</label><br>
                    <input type="text" id="subject" placeholder="Sujet du message..."><br><br>
                    <label for="message">Message</label><br>
                    <textarea id="message" rows="4" placeholder="Votre message..."></textarea><br><br>
                    <button class="button" type="submit">Envoyer le message</button>
                </form>
            </div>

            <div class="message1">
                <div class="titre">
                    <span class="icon"><i data-lucide="map-pin"></i></span>
                    <div>
                        <h4>Notre adresse</h4>
                        <p>123 Avenue de l'innovation</p>
                        <p>75000 Paris, France</p>
                    </div>
                </div>
                <div class="titre">
                    <span class="icon"><i data-lucide="mail"></i></span>
                    <div>
                        <h4>Email</h4>
                        <p>contact@esgishub.com</p>
                    </div>
                </div>
                <div class="titre">
                    <span class="icon"><i data-lucide="phone"></i></span>
                    <div>
                        <h4>Téléphone</h4>
                        <p>+33 1 23 45 67 89</p>
                    </div>
                </div>
                <div class="support-container">
                    <div class="titre">
                        <span class="icon"><i data-lucide="message-square"></i></span>
                        <div class="text">
                            <h4>Support en direct</h4>
                            <p>Notre équipe est disponible 24/7 pour vous aider</p>
                        </div>
                        <div class="button-container">
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