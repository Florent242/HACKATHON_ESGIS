<?php

class Controller {
    public function __construct() {
        // Constructeur vide pour le moment
    }

    protected function jsonResponse($data, $statusCode = 200) {
        if (headers_sent()) {
            // Si les en-têtes ont déjà été envoyés, on retourne juste le JSON
            echo json_encode($data, JSON_PRETTY_PRINT);
        } else {
            // Sinon, on envoie les en-têtes appropriés
            http_response_code($statusCode);
            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);
        }
        
        if (php_sapi_name() !== 'cli') {
            exit;
        }
    }
}
