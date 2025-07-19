<?php
use Auth\Model\Database;
use Auth\Controller\ScoreController;

require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../controllers/ScoreController.php';
if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/config.php';
}

// Inclure les fichiers contenant des fonctions
if (!defined('FUNCTIONS_INCLUDED')) {
    require_once __DIR__ . '/functions.php';
}
$db = Database::getInstance()->getConnection();

// Recalcule pour les hackathons actifs
recalculateAllHackathonScores($db);
