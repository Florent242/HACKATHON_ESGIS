<?php
/**
 * Récupère le token Bearer de l'en-tête Authorization
 * @return string|null Le token ou null si non trouvé
 */
function getBearerToken() {
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER['Authorization']);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        $requestHeaders = array_combine(
            array_map('ucwords', array_keys($requestHeaders)),
            array_values($requestHeaders)
        );
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    
    // Extraire le token du header
    if (!empty($headers) && preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
        return $matches[1];
    }
    
    // Vérifier dans les cookies
    if (isset($_COOKIE['long_term_token'])) {
        return $_COOKIE['long_term_token'];
    }
    
    if (isset($_COOKIE['jwt_token'])) {
        return $_COOKIE['jwt_token'];
    }
    
    return null;
}