<?php
session_start();

// Supprimer la notification de la session
unset($_SESSION['notification']);

// Répondre avec un statut HTTP 200 (succès)
http_response_code(200);
exit();