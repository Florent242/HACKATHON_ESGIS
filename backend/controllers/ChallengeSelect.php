<?php
require_once '../models/Database.php'; // chemin correct vers ton fichier

use Auth\Model\Database;
use PDO;

class Challenge {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Récupère un challenge spécifique
     * @param int $id ID du challenge
     * @return array|false Challenge ou false si non trouvé
     */
    public function getChallenge($id) {
        $sql = "SELECT * FROM challenges WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les challenges
     * @return array Liste des challenges
     */
    public function getAllChallenges() {
        $sql = "SELECT * FROM challenges";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les challenges en JSON
     * @return string JSON des challenges
     */
    public function getChallengesJSON() {
        $challenges = $this->getAllChallenges();
        return json_encode($challenges, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}

// Récupération de l'instance PDO via la classe Database
$pdo = Database::getInstance()->getConnection();

// Instanciation et affichage du JSON
$challenges = new Challenge($pdo);

header('Content-Type: application/json; charset=utf-8');
echo $challenges->getChallengesJSON();  
