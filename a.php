<?php

function listerArborescence($repertoire, $niveau = 0) {
    // Ouvre le répertoire
    if ($handle = opendir($repertoire)) {
        // Liste les fichiers et dossiers
        while (false !== ($element = readdir($handle))) {
            // Ignorer les . et ..
            if ($element == "." || $element == "..") {
                continue;
            }
            
            // Affiche l'élément avec un décalage pour représenter le niveau
            echo str_repeat("  ", $niveau) . $element . "\n";
            
            // Si c'est un répertoire, appeler récursivement la fonction
            if (is_dir($repertoire . "/" . $element)) {
                listerArborescence($repertoire . "/" . $element, $niveau + 1);
            }
        }
        closedir($handle);
    }
}

// Remplacer par le répertoire que vous souhaitez lister
$repertoire = __DIR__; // Par exemple, le répertoire courant
listerArborescence($repertoire);

?>
