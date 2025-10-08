<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact</title>
    <link rel="stylesheet" href="/css/styles/contact.css">
    <?php require_once "../includes/head.php"; ?>
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
            <div class="message message-box">
                <form>

                    <h3 style="text-align: center; margin-bottom: 5px;">Envoyez-nous un message</h3>

                    <div class="form-container">
                        <div class="input-group">
                            <label for="name">Nom</label>
                            <input type="text" id="name" placeholder="Votre nom...">
                        </div>
                            <div class="input-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" placeholder="Hack&Stack@gmail.com..."><br>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="subject">Sujet</label>
                        <input type="text" id="subject" placeholder="Sujet du message..."><br>
                    </div>

                    <div class="input-group">
                        <label for="message">Message</label>
                        <textarea id="message" rows="4" placeholder="Votre message..."></textarea>
                    </div>

                    <button class="button" type="submit">Envoyer le message</button>
                </form>

            </div>

            <div class="message1  message-box">
                <div class="titre">
                    <span class="icon"><i data-lucide="map-pin"></i></span>
                    <div>
                        <h4>Notre adresse</h4>
                        <p>Jericho Cotonou, Benin</p>
                    </div>
                </div>

                <div class="titre">
                    <span class="icon"><i data-lucide="mail"></i></span>
                    <div>
                        <h4>Email</h4>
                        <p>esgis.benin@esgis.org</p>
                    </div>
                </div>

                <div class="titre">
                    <span class="icon"><i data-lucide="phone"></i></span>
                    <div>
                        <h4>Téléphone</h4>
                        <p>+229 01 61 27 13 13</p>
                    </div>
                </div>
                
                <div class="support-container">
                    <div class="titre">
                        <span class="icon"><i data-lucide="message-square"></i></span>
                        <div>
                            <h4>Support en direct</h4>
                            <p>Notre équipe est disponible 24/7 pour vous aider</p>
                        </div>
                    </div>
                </div>

                <div class="button-container">
                    <button class="button" onclick="window.location.href = 'https://discord.com/invite/FbztK5Uagd'">Contactez-nous sur Discord</button>
                </div>  
            </div>
        </div>
    </div>

</body>

</html>