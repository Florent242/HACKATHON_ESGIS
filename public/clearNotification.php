<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['notification'])) {
    $_SESSION['notification'] = null;
}
// Supprimer la notification de la session
unset($_SESSION['notification']);

// Répondre avec un statut HTTP 200 (succès)
http_response_code(200);
echo json_encode(['success' => true]);
exit();