<?php
$password = 'qwertyui'; // Mot de passe saisi par l'utilisateur
$hashedPassword = '$2y$10$o5KpdvJ0F48w8opnWslB3uO4fOls54NL4aS7pAb2uef4ckFxwYqVu'; // Hachage stocké

if (password_verify($password, $hashedPassword)) {
    echo 'Mot de passe valide !';
} else {
    echo 'Mot de passe invalide.';
}
?>